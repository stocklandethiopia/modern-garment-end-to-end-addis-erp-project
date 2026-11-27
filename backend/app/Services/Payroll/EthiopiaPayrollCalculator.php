<?php

namespace App\Services\Payroll;

use InvalidArgumentException;

/**
 * Server-side Ethiopian employment-pay calculation boundary.
 *
 * This service ships no default statutory values. Candidate calculations are
 * available for regression analysis only. Payroll-draft calculation requires
 * effective-dated, source-backed and independently approved rule records.
 */
final class EthiopiaPayrollCalculator
{
    public const TAX_BASE_GROSS = 'TAXABLE_GROSS';

    public const TAX_BASE_GROSS_LESS_EMPLOYEE_PENSION = 'TAXABLE_GROSS_LESS_EMPLOYEE_PENSION';

    /**
     * Calculate a candidate vector for engineering regression tests only.
     *
     * The method does not attest that the supplied legal interpretation is
     * approved or suitable for a live payroll run.
     *
     * @param array<string, mixed> $input
     * @param array<string, mixed> $incomeTaxRule
     * @param array<string, mixed> $pensionRule
     * @return array<string, mixed>
     */
    public function calculateCandidate(array $input, array $incomeTaxRule, array $pensionRule): array
    {
        return $this->calculate($input, $incomeTaxRule, $pensionRule, false);
    }

    /**
     * Calculate a payroll draft line using the rule records frozen on the run.
     *
     * @param array<string, mixed> $input
     * @param array<int, array<string, mixed>> $ruleRecords
     * @return array<string, mixed>
     */
    public function calculateApproved(
        array $input,
        array $ruleRecords,
        string $periodStart,
        string $periodEnd,
        string $preparedBy
    ): array {
        $taxRecord = $this->findUniqueRuleRecord($ruleRecords, 'INCOME_TAX');
        $pensionRecord = $this->findUniqueRuleRecord($ruleRecords, 'PENSION');

        $this->assertApprovedRuleRecord($taxRecord, $periodStart, $periodEnd, $preparedBy);
        $this->assertApprovedRuleRecord($pensionRecord, $periodStart, $periodEnd, $preparedBy);

        if (strtoupper((string) ($taxRecord['jurisdiction_code'] ?? '')) !== 'ETH'
            || strtoupper((string) ($pensionRecord['jurisdiction_code'] ?? '')) !== 'ETH'
        ) {
            throw new InvalidArgumentException('Ethiopia payroll requires both the income-tax and pension rules to be explicitly registered for ETH.');
        }

        $taxPayload = $this->decodeRulePayload($taxRecord);
        $pensionPayload = $this->decodeRulePayload($pensionRecord);
        $taxRule = $taxPayload['payroll'] ?? null;
        $pensionRule = $pensionPayload['payroll'] ?? null;

        if (! is_array($taxRule) || ! is_array($pensionRule)) {
            throw new InvalidArgumentException('Approved Ethiopia payroll rule records must contain a payroll configuration object.');
        }

        $decision = $taxRule['tax_base_decision'] ?? null;
        if (! is_array($decision)
            || strtoupper((string) ($decision['status'] ?? '')) !== 'APPROVED'
            || (string) ($decision['approved_method'] ?? '') !== (string) ($taxRule['tax_base_method'] ?? '')
            || trim((string) ($decision['opinion_reference'] ?? '')) === ''
            || trim((string) ($decision['reviewer_qualification'] ?? '')) === ''
            || trim((string) ($decision['reviewer_user_id'] ?? '')) === ''
            || (string) $decision['reviewer_user_id'] === $preparedBy
            || trim((string) ($decision['reviewer_registration_reference'] ?? '')) === ''
            || trim((string) ($decision['opinion_document_reference'] ?? '')) === ''
            || trim((string) ($decision['opinion_document_storage_reference'] ?? '')) === ''
            || ! preg_match('/^[a-f0-9]{64}$/Di', (string) ($decision['opinion_sha256'] ?? ''))
            || empty($decision['independence_confirmed'])
            || ! $this->isTimestamp((string) ($decision['reviewed_at'] ?? ''))
            || empty($decision['article_references'])
        ) {
            throw new InvalidArgumentException('A separate, signed, clause-specific Ethiopian tax opinion with reviewer credentials and a document hash must explicitly approve the PAYE base method before payroll calculation.');
        }

        if (($pensionRule['salary_basis'] ?? null) !== 'REGULAR_HOURS_MONTHLY_SALARY') {
            throw new InvalidArgumentException('The pension rule must explicitly identify the legally supported regular-hours monthly salary basis.');
        }

        $this->assertSupportingSources($taxRule['supporting_sources'] ?? null, $preparedBy, 'income-tax');
        $this->assertSupportingSources($pensionRule['supporting_sources'] ?? null, $preparedBy, 'pension');

        $result = $this->calculate($input, $taxRule, $pensionRule, true, $preparedBy);
        $result['calculation_status'] = 'CALCULATED_DRAFT_NOT_POSTED';
        $result['source_snapshot'] = [
            'income_tax' => $this->sourceRecordSnapshot($taxRecord),
            'pension' => $this->sourceRecordSnapshot($pensionRecord),
            'tax_base_opinion_reference' => (string) $decision['opinion_reference'],
            'tax_base_opinion_articles' => array_values($decision['article_references']),
        ];
        $result['source_snapshot_sha256'] = hash('sha256', json_encode(
            $this->canonicalize($result['source_snapshot']),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ));

        return $result;
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $incomeTaxRule
     * @param array<string, mixed> $pensionRule
     * @return array<string, mixed>
     */
    private function calculate(
        array $input,
        array $incomeTaxRule,
        array $pensionRule,
        bool $requireApprovedEvidence,
        ?string $preparedBy = null
    ): array {
        $payeeType = strtoupper(trim((string) ($input['payee_type'] ?? '')));
        $serviceType = strtoupper(trim((string) ($input['service_type'] ?? '')));

        if ($payeeType === 'RETAINER_SERVICE' || $serviceType === 'RETAINER_SERVICE') {
            throw new InvalidArgumentException('RETAINER_SERVICE is not employee payroll; route it to supplier/AP and individual-payee tax review.');
        }

        if ($payeeType !== 'EMPLOYEE') {
            throw new InvalidArgumentException('Only a legally classified employee can enter the employee PAYE/pension calculator.');
        }

        if (strtoupper((string) ($input['currency'] ?? '')) !== 'ETB') {
            throw new InvalidArgumentException('This Ethiopia statutory calculator accepts ETB payroll only.');
        }

        $baseSalary = self::toMinor($input['base_salary'] ?? null, 'base_salary');
        $components = $input['earning_components'] ?? [];
        if (! is_array($components)) {
            throw new InvalidArgumentException('earning_components must be a list.');
        }

        $gross = $baseSalary;
        $taxableGross = $baseSalary;
        $expectedPensionable = $baseSalary;
        $exemptTotal = 0;
        $taxableComponentTotal = 0;
        $pensionableComponentTotal = 0;
        $componentSnapshot = [];

        foreach ($components as $index => $component) {
            if (! is_array($component)) {
                throw new InvalidArgumentException('Each earning component must be an object.');
            }

            $code = trim((string) ($component['code'] ?? ''));
            if ($code === '') {
                throw new InvalidArgumentException("earning_components.{$index}.code is required.");
            }

            $amount = self::toMinor($component['amount'] ?? null, "earning_components.{$index}.amount");
            $taxTreatment = strtoupper(trim((string) ($component['tax_treatment'] ?? '')));
            $pensionTreatment = strtoupper(trim((string) ($component['pension_treatment'] ?? '')));

            if (! in_array($taxTreatment, ['TAXABLE', 'EXEMPT'], true)) {
                throw new InvalidArgumentException("earning_components.{$index}.tax_treatment must be TAXABLE or EXEMPT.");
            }

            if (! in_array($pensionTreatment, ['PENSIONABLE', 'NON_PENSIONABLE'], true)) {
                throw new InvalidArgumentException("earning_components.{$index}.pension_treatment must be explicit.");
            }

            if ($taxTreatment === 'EXEMPT') {
                $this->assertComponentEvidence(
                    $component['exemption_evidence'] ?? null,
                    $requireApprovedEvidence,
                    $preparedBy,
                    "earning_components.{$index}.exemption_evidence"
                );
                $exemptTotal += $amount;
            } else {
                $taxableGross += $amount;
                $taxableComponentTotal += $amount;
            }

            if ($pensionTreatment === 'PENSIONABLE') {
                $expectedPensionable += $amount;
                $pensionableComponentTotal += $amount;
            }

            $gross += $amount;
            $componentSnapshot[] = [
                'code' => $code,
                'amount' => self::fromMinor($amount),
                'tax_treatment' => $taxTreatment,
                'pension_treatment' => $pensionTreatment,
                'exemption_evidence' => $taxTreatment === 'EXEMPT'
                    ? $component['exemption_evidence']
                    : null,
            ];
        }

        $pensionableSalary = self::toMinor($input['pensionable_salary'] ?? null, 'pensionable_salary');
        if ($pensionableSalary > $gross) {
            throw new InvalidArgumentException('pensionable_salary cannot exceed gross earnings in this calculation model.');
        }

        if ($pensionableSalary !== $expectedPensionable) {
            $this->assertPensionableSalaryRationale(
                $input['pensionable_salary_rationale'] ?? null,
                $requireApprovedEvidence,
                $preparedBy
            );
        }

        $coverage = $input['pension_coverage'] ?? null;
        if (! is_array($coverage)
            || ! in_array(strtoupper((string) ($coverage['status'] ?? '')), ['COVERED', 'NOT_COVERED'], true)
        ) {
            throw new InvalidArgumentException('An explicit, reviewed pension coverage decision is required; coverage cannot be inferred from job title or contract label.');
        }

        if ($requireApprovedEvidence) {
            $this->assertIndependentDecision($coverage, $preparedBy, 'pension_coverage');
            if (! preg_match('/^[a-f0-9]{64}$/Di', (string) ($coverage['source_sha256'] ?? ''))
                || ! $this->isOfficialEthiopianHost(strtolower((string) parse_url((string) ($coverage['source_url'] ?? ''), PHP_URL_HOST)))
                || trim((string) ($coverage['source_document_reference'] ?? '')) === ''
                || trim((string) ($coverage['article_reference'] ?? '')) === ''
            ) {
                throw new InvalidArgumentException('The pension coverage decision requires the official source URL, article and SHA-256.');
            }
        }

        $taxBaseMethod = (string) ($incomeTaxRule['tax_base_method'] ?? '');
        if (! in_array($taxBaseMethod, [
            self::TAX_BASE_GROSS,
            self::TAX_BASE_GROSS_LESS_EMPLOYEE_PENSION,
        ], true)) {
            throw new InvalidArgumentException('The approved tax rule must explicitly select a supported PAYE base method.');
        }

        $this->assertTaxBands($incomeTaxRule['monthly_tax_bands'] ?? null);

        $taxRounding = $this->roundingMethod($incomeTaxRule['rounding_method'] ?? null, 'PAYE');
        $pensionRounding = $this->roundingMethod($pensionRule['rounding_method'] ?? null, 'pension');
        $employeeRate = self::basisPoints($pensionRule['employee_rate_basis_points'] ?? null, 'employee_rate_basis_points');
        $employerRate = self::basisPoints($pensionRule['employer_rate_basis_points'] ?? null, 'employer_rate_basis_points');

        if (strtoupper((string) $coverage['status']) === 'COVERED') {
            $employeePension = self::percentage($pensionableSalary, $employeeRate, $pensionRounding);
            $employerPension = self::percentage($pensionableSalary, $employerRate, $pensionRounding);
        } else {
            $employeePension = 0;
            $employerPension = 0;
        }

        // Both candidate methods are computed server-side. Only calculateApproved()
        // can produce a payroll-draft result, and it requires an opinion approving
        // the exact method. Employer pension is never deducted from employee pay.
        $taxableIncome = $taxBaseMethod === self::TAX_BASE_GROSS_LESS_EMPLOYEE_PENSION
            ? max(0, $taxableGross - $employeePension)
            : $taxableGross;

        $paye = $this->progressiveTax($taxableIncome, $incomeTaxRule['monthly_tax_bands'], $taxRounding);
        $otherDeductions = $this->authorizedDeductions(
            $input['other_deductions'] ?? [],
            $requireApprovedEvidence,
            $preparedBy
        );

        $deductionTotal = $paye + $employeePension + $otherDeductions;
        if ($deductionTotal > $gross) {
            throw new InvalidArgumentException('PAYE, employee pension and authorized deductions exceed gross earnings.');
        }

        $netPay = $gross - $deductionTotal;

        return [
            'currency' => 'ETB',
            'base_salary' => self::fromMinor($baseSalary),
            'taxable_component_total' => self::fromMinor($taxableComponentTotal),
            'exempt_component_total' => self::fromMinor($exemptTotal),
            'pensionable_component_total' => self::fromMinor($pensionableComponentTotal),
            'gross_pay' => self::fromMinor($gross),
            'taxable_gross' => self::fromMinor($taxableGross),
            'tax_base_basis' => $taxBaseMethod,
            'taxable_income' => self::fromMinor($taxableIncome),
            'paye' => self::fromMinor($paye),
            'pension_coverage_status' => strtoupper((string) $coverage['status']),
            'pensionable_salary' => self::fromMinor($pensionableSalary),
            'employee_pension_rate_basis_points' => $employeeRate,
            'employer_pension_rate_basis_points' => $employerRate,
            'employee_pension' => self::fromMinor($employeePension),
            'employer_pension' => self::fromMinor($employerPension),
            'other_deductions' => self::fromMinor($otherDeductions),
            'deduction_total' => self::fromMinor($deductionTotal),
            'net_pay' => self::fromMinor($netPay),
            'employer_cost_total' => self::fromMinor($gross + $employerPension),
            'earning_component_snapshot' => $componentSnapshot,
            'calculation_status' => 'CANDIDATE_NOT_FOR_PAYROLL',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $ruleRecords
     * @return array<string, mixed>
     */
    private function findUniqueRuleRecord(array $ruleRecords, string $domain): array
    {
        $matches = array_values(array_filter(
            $ruleRecords,
            static fn (array $record): bool => strtoupper((string) ($record['rule_domain'] ?? '')) === $domain
        ));

        if (count($matches) !== 1) {
            throw new InvalidArgumentException("Exactly one frozen {$domain} payroll rule record is required for this calculation.");
        }

        return $matches[0];
    }

    /**
     * @param array<string, mixed> $record
     */
    private function assertApprovedRuleRecord(
        array $record,
        string $periodStart,
        string $periodEnd,
        string $preparedBy
    ): void {
        if (strtoupper((string) ($record['verification_status'] ?? '')) !== 'PROFESSIONAL_APPROVED') {
            throw new InvalidArgumentException('A statutory rule record is not professionally approved.');
        }

        if (trim((string) ($record['professional_approval_reference'] ?? '')) === '') {
            throw new InvalidArgumentException('A statutory rule record is missing its professional approval reference.');
        }

        if (trim((string) ($record['verified_by'] ?? '')) === ''
            || (string) $record['verified_by'] === $preparedBy
            || ! $this->isTimestamp((string) ($record['verified_at'] ?? ''))
        ) {
            throw new InvalidArgumentException('A statutory rule record requires a separate reviewer and verification date.');
        }

        $sha = strtolower((string) ($record['source_sha256'] ?? ''));
        if (! preg_match('/^[a-f0-9]{64}$/D', $sha)) {
            throw new InvalidArgumentException('A statutory rule record must store the SHA-256 of the source document.');
        }

        $url = trim((string) ($record['source_reference'] ?? ''));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (! str_starts_with($url, 'https://') || ! $this->isOfficialEthiopianHost($host)) {
            throw new InvalidArgumentException('A statutory calculation rule must point to an official Ethiopian government/authority source.');
        }

        if (trim((string) ($record['source_title'] ?? '')) === ''
            || trim((string) ($record['source_authority'] ?? '')) === ''
            || trim((string) ($record['source_document_reference'] ?? '')) === ''
            || ! $this->isDate((string) ($record['publication_date'] ?? ''))
        ) {
            throw new InvalidArgumentException('The statutory source record is missing its title, authority or publication date.');
        }

        $effectiveFrom = (string) ($record['effective_from'] ?? '');
        $effectiveTo = $record['effective_to'] ?? null;
        if (! $this->isDate($effectiveFrom)
            || $effectiveFrom > $periodStart
            || ($effectiveTo !== null && (! $this->isDate((string) $effectiveTo) || (string) $effectiveTo < $periodEnd))
        ) {
            throw new InvalidArgumentException('The statutory rule does not cover the complete payroll period.');
        }
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function decodeRulePayload(array $record): array
    {
        $payload = $record['rule_payload'] ?? null;
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        if (! is_array($payload)) {
            throw new InvalidArgumentException('The frozen statutory rule payload is missing or invalid.');
        }

        return $payload;
    }

    /**
     * @param mixed $evidence
     */
    private function assertComponentEvidence(
        mixed $evidence,
        bool $requireApproved,
        ?string $preparedBy,
        string $field
    ): void {
        if (! is_array($evidence)
            || trim((string) ($evidence['legal_basis'] ?? '')) === ''
            || trim((string) ($evidence['article_reference'] ?? '')) === ''
            || trim((string) ($evidence['evidence_reference'] ?? '')) === ''
            || trim((string) ($evidence['document_storage_reference'] ?? '')) === ''
        ) {
            throw new InvalidArgumentException("{$field} must identify the legal basis, article and evidence.");
        }

        if ($requireApproved) {
            $this->assertIndependentDecision($evidence, $preparedBy, $field);
            if (! preg_match('/^[a-f0-9]{64}$/Di', (string) ($evidence['source_sha256'] ?? ''))
                || ! $this->isOfficialEthiopianHost(strtolower((string) parse_url((string) ($evidence['source_url'] ?? ''), PHP_URL_HOST)))
            ) {
                throw new InvalidArgumentException("{$field} requires the official source URL and SHA-256.");
            }
        }
    }

    /**
     * @param mixed $rationale
     */
    private function assertPensionableSalaryRationale(
        mixed $rationale,
        bool $requireApproved,
        ?string $preparedBy
    ): void {
        if (! is_array($rationale)
            || trim((string) ($rationale['reason'] ?? '')) === ''
            || trim((string) ($rationale['contract_reference'] ?? '')) === ''
            || trim((string) ($rationale['legal_basis'] ?? '')) === ''
            || trim((string) ($rationale['evidence_reference'] ?? '')) === ''
            || trim((string) ($rationale['document_storage_reference'] ?? '')) === ''
        ) {
            throw new InvalidArgumentException('A non-standard pensionable salary requires a reason, contract reference, legal basis and supporting evidence.');
        }

        if ($requireApproved) {
            $this->assertIndependentDecision($rationale, $preparedBy, 'pensionable_salary_rationale');
            if (! preg_match('/^[a-f0-9]{64}$/Di', (string) ($rationale['source_sha256'] ?? ''))
                || ! $this->isOfficialEthiopianHost(strtolower((string) parse_url((string) ($rationale['source_url'] ?? ''), PHP_URL_HOST)))
            ) {
                throw new InvalidArgumentException('A non-standard pensionable salary requires approved official-source evidence.');
            }
        }
    }

    /**
     * @param array<string, mixed> $decision
     */
    private function assertIndependentDecision(array $decision, ?string $preparedBy, string $field): void
    {
        $approver = (string) ($decision['approved_by'] ?? $decision['reviewer_user_id'] ?? '');
        if (strtoupper((string) ($decision['approval_status'] ?? $decision['review_status'] ?? '')) !== 'PROFESSIONALLY_APPROVED'
            && strtoupper((string) ($decision['approval_status'] ?? $decision['review_status'] ?? '')) !== 'APPROVED'
        ) {
            throw new InvalidArgumentException("{$field} is not professionally approved.");
        }

        if ($approver === '' || $preparedBy === null || $approver === $preparedBy
            || trim((string) ($decision['reviewer_qualification'] ?? '')) === ''
            || trim((string) ($decision['reviewer_registration_reference'] ?? '')) === ''
            || ! $this->isTimestamp((string) ($decision['approved_at'] ?? $decision['reviewed_at'] ?? ''))
            || trim((string) ($decision['approval_reference'] ?? $decision['evidence_reference'] ?? '')) === ''
        ) {
            throw new InvalidArgumentException("{$field} requires a separate approver, approval date and approval/evidence reference.");
        }
    }

    /**
     * @param mixed $items
     */
    private function authorizedDeductions(mixed $items, bool $requireApproved, ?string $preparedBy): int
    {
        if (! is_array($items)) {
            throw new InvalidArgumentException('other_deductions must be a list.');
        }

        $total = 0;
        foreach ($items as $index => $item) {
            if (! is_array($item) || trim((string) ($item['code'] ?? '')) === '') {
                throw new InvalidArgumentException("other_deductions.{$index} must have a code.");
            }
            $amount = self::toMinor($item['amount'] ?? null, "other_deductions.{$index}.amount");
            if ($amount > 0) {
                $evidence = $item['authorization_evidence'] ?? null;
                if (! is_array($evidence)
                    || trim((string) ($evidence['legal_basis'] ?? '')) === ''
                    || trim((string) ($evidence['evidence_reference'] ?? '')) === ''
                ) {
                    throw new InvalidArgumentException("other_deductions.{$index} requires lawful authorization evidence.");
                }
                if ($requireApproved) {
                    $this->assertIndependentDecision($evidence, $preparedBy, "other_deductions.{$index}");
                }
            }
            $total += $amount;
        }

        return $total;
    }

    private function assertTaxBands(mixed $bands): void
    {
        if (! is_array($bands) || $bands === []) {
            throw new InvalidArgumentException('No default PAYE bands are supplied; an approved monthly tax band table is required.');
        }

        $expectedLower = 0;
        $last = count($bands) - 1;
        foreach (array_values($bands) as $index => $band) {
            if (! is_array($band)) {
                throw new InvalidArgumentException('Each PAYE band must be an object.');
            }
            $lower = self::toMinor($band['lower'] ?? null, "monthly_tax_bands.{$index}.lower");
            $upper = isset($band['upper']) && $band['upper'] !== null
                ? self::toMinor($band['upper'], "monthly_tax_bands.{$index}.upper")
                : null;
            $rate = self::basisPoints($band['rate_basis_points'] ?? null, "monthly_tax_bands.{$index}.rate_basis_points");

            if ($lower !== $expectedLower || ($upper !== null && $upper <= $lower)) {
                throw new InvalidArgumentException('PAYE bands must be contiguous, ordered and non-overlapping.');
            }
            if ($index === $last && $upper !== null) {
                throw new InvalidArgumentException('The final PAYE band must be open-ended.');
            }
            if ($index !== $last && $upper === null) {
                throw new InvalidArgumentException('Only the final PAYE band may be open-ended.');
            }
            if ($upper !== null) {
                $expectedLower = $upper;
            }
        }
    }

    /**
     * Accumulate all marginal-band fractions and round once at the final
     * currency precision. No rounding convention is silently defaulted.
     *
     * @param array<int, array<string, mixed>> $bands
     */
    private function progressiveTax(int $income, array $bands, string $roundingMethod): int
    {
        $taxNumerator = 0;
        foreach (array_values($bands) as $band) {
            $lower = self::toMinor($band['lower'], 'monthly_tax_bands.lower');
            $upper = isset($band['upper']) && $band['upper'] !== null
                ? self::toMinor($band['upper'], 'monthly_tax_bands.upper')
                : null;
            $rate = self::basisPoints($band['rate_basis_points'], 'monthly_tax_bands.rate_basis_points');
            $taxableInBand = max(0, ($upper === null ? $income : min($income, $upper)) - $lower);
            $taxNumerator += $taxableInBand * $rate;
        }

        return self::roundBasisPointNumerator($taxNumerator, $roundingMethod);
    }

    /**
     * Each supporting Ethiopian instrument is stored as reviewed source evidence.
     *
     * @param mixed $sources
     */
    private function assertSupportingSources(mixed $sources, string $preparedBy, string $domain): void
    {
        if (! is_array($sources) || $sources === []) {
            throw new InvalidArgumentException("The {$domain} rule must include its supporting official instruments and directives.");
        }

        foreach ($sources as $index => $source) {
            if (! is_array($source)
                || trim((string) ($source['instrument_number'] ?? '')) === ''
                || trim((string) ($source['source_authority'] ?? '')) === ''
                || trim((string) ($source['source_title'] ?? '')) === ''
                || trim((string) ($source['article_references'] ?? '')) === ''
                || trim((string) ($source['document_storage_reference'] ?? '')) === ''
                || trim((string) ($source['reviewer_qualification'] ?? '')) === ''
                || trim((string) ($source['reviewer_registration_reference'] ?? '')) === ''
                || ! $this->isDate((string) ($source['publication_date'] ?? ''))
                || ! $this->isDate((string) ($source['effective_from'] ?? ''))
                || ! preg_match('/^[a-f0-9]{64}$/Di', (string) ($source['source_sha256'] ?? ''))
            ) {
                throw new InvalidArgumentException("{$domain} supporting source {$index} is incomplete or has no verified source hash.");
            }

            $url = trim((string) ($source['source_url'] ?? ''));
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            if (! str_starts_with($url, 'https://') || ! $this->isOfficialEthiopianHost($host)) {
                throw new InvalidArgumentException("{$domain} supporting source {$index} must be an official Ethiopian authority URL.");
            }

            if (strtoupper((string) ($source['review_status'] ?? '')) !== 'PROFESSIONALLY_APPROVED'
                || trim((string) ($source['reviewer_user_id'] ?? '')) === ''
                || (string) $source['reviewer_user_id'] === $preparedBy
                || ! $this->isTimestamp((string) ($source['reviewed_at'] ?? ''))
                || trim((string) ($source['review_reference'] ?? '')) === ''
            ) {
                throw new InvalidArgumentException("{$domain} supporting source {$index} has not passed independent professional review.");
            }
        }
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function sourceRecordSnapshot(array $record): array
    {
        return [
            'record_id' => (string) ($record['id'] ?? ''),
            'rule_domain' => (string) ($record['rule_domain'] ?? ''),
            'source_authority' => (string) ($record['source_authority'] ?? ''),
            'source_title' => (string) ($record['source_title'] ?? ''),
            'source_reference' => (string) ($record['source_reference'] ?? ''),
            'source_document_reference' => (string) ($record['source_document_reference'] ?? ''),
            'source_sha256' => strtolower((string) ($record['source_sha256'] ?? '')),
            'publication_date' => (string) ($record['publication_date'] ?? ''),
            'effective_from' => (string) ($record['effective_from'] ?? ''),
            'effective_to' => $record['effective_to'] ?? null,
            'verification_status' => (string) ($record['verification_status'] ?? ''),
            'professional_approval_reference' => (string) ($record['professional_approval_reference'] ?? ''),
            'verified_by' => (string) ($record['verified_by'] ?? ''),
            'verified_at' => (string) ($record['verified_at'] ?? ''),
            'rule_payload' => $this->decodeRulePayload($record),
        ];
    }

    private function isOfficialEthiopianHost(string $host): bool
    {
        return $host === 'gov.et' || str_ends_with($host, '.gov.et');
    }

    private function isDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function isTimestamp(string $value): bool
    {
        if (trim($value) === '') {
            return false;
        }

        try {
            new \DateTimeImmutable($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Parse a non-negative decimal amount without floating-point conversion.
     */
    private static function toMinor(mixed $value, string $field): int
    {
        $raw = is_string($value) || is_int($value) ? trim((string) $value) : '';
        if (! preg_match('/^(?:0|[1-9][0-9]*)(?:\.([0-9]{1,2}))?$/D', $raw, $matches)) {
            throw new InvalidArgumentException("{$field} must be a non-negative decimal amount with at most two fractional digits.");
        }

        $parts = explode('.', $raw, 2);
        $whole = (int) $parts[0];
        $fraction = isset($parts[1]) ? (int) str_pad($parts[1], 2, '0') : 0;
        if ($whole > intdiv(PHP_INT_MAX - $fraction, 100)) {
            throw new InvalidArgumentException("{$field} exceeds the supported monetary range.");
        }

        return ($whole * 100) + $fraction;
    }

    private static function fromMinor(int $minor): string
    {
        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    private static function basisPoints(mixed $value, string $field): int
    {
        if (! is_int($value) && ! (is_string($value) && preg_match('/^[0-9]{1,5}$/D', $value))) {
            throw new InvalidArgumentException("{$field} must be integer basis points.");
        }
        $rate = (int) $value;
        if ($rate < 0 || $rate > 10000) {
            throw new InvalidArgumentException("{$field} must be between 0 and 10000 basis points.");
        }

        return $rate;
    }

    private static function percentage(int $amountMinor, int $rateBasisPoints, string $roundingMethod): int
    {
        return self::roundBasisPointNumerator($amountMinor * $rateBasisPoints, $roundingMethod);
    }

    private function roundingMethod(mixed $value, string $context): string
    {
        $method = strtoupper(trim((string) $value));
        if (! in_array($method, ['HALF_UP', 'HALF_EVEN'], true)) {
            throw new InvalidArgumentException("An explicit supported {$context} rounding method is required.");
        }

        return $method;
    }

    private static function roundBasisPointNumerator(int $numerator, string $method): int
    {
        $whole = intdiv($numerator, 10000);
        $remainder = $numerator % 10000;

        if ($method === 'HALF_UP') {
            return $remainder >= 5000 ? $whole + 1 : $whole;
        }

        if ($remainder > 5000 || ($remainder === 5000 && ($whole % 2) === 1)) {
            return $whole + 1;
        }

        return $whole;
    }

    /**
     * @param array<mixed> $value
     * @return array<mixed>
     */
    private function canonicalize(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->canonicalize($item);
            }
        }
        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }
}
