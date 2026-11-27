<?php

namespace Tests\Unit;

use App\Services\Payroll\EthiopiaPayrollCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EthiopiaPayrollCalculatorTest extends TestCase
{
    private EthiopiaPayrollCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new EthiopiaPayrollCalculator();
    }

    public function test_etb_30000_pension_first_candidate_regression_is_exact(): void
    {
        $result = $this->calculator->calculateCandidate(
            $this->standardEmployeeInput(),
            $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS_LESS_EMPLOYEE_PENSION),
            $this->pensionRule()
        );

        self::assertSame('27900.00', $result['taxable_income']);
        self::assertSame('7715.00', $result['paye']);
        self::assertSame('2100.00', $result['employee_pension']);
        self::assertSame('3300.00', $result['employer_pension']);
        self::assertSame('20185.00', $result['net_pay']);
        self::assertSame('33300.00', $result['employer_cost_total']);
        self::assertSame('CANDIDATE_NOT_FOR_PAYROLL', $result['calculation_status']);
    }

    public function test_etb_30000_gross_base_candidate_comparison_is_exact(): void
    {
        $input = $this->standardEmployeeInput();
        $grossBase = $this->calculator->calculateCandidate(
            $input,
            $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS),
            $this->pensionRule()
        );
        $pensionFirst = $this->calculator->calculateCandidate(
            $input,
            $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS_LESS_EMPLOYEE_PENSION),
            $this->pensionRule()
        );

        self::assertSame('30000.00', $grossBase['taxable_income']);
        self::assertSame('8450.00', $grossBase['paye']);
        self::assertSame('19450.00', $grossBase['net_pay']);
        self::assertSame(73500, $this->minor($grossBase['paye']) - $this->minor($pensionFirst['paye']));
    }

    public function test_employer_pension_is_not_deducted_from_employee_net_pay(): void
    {
        $result = $this->calculator->calculateCandidate(
            $this->standardEmployeeInput(),
            $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS_LESS_EMPLOYEE_PENSION),
            $this->pensionRule()
        );

        self::assertSame('20185.00', $result['net_pay']);
        self::assertSame('3300.00', $result['employer_pension']);
        self::assertSame('2100.00', $result['employee_pension']);
    }

    public function test_non_covered_worker_has_no_private_scheme_contribution(): void
    {
        $input = $this->standardEmployeeInput();
        $input['pension_coverage']['status'] = 'NOT_COVERED';

        $result = $this->calculator->calculateCandidate(
            $input,
            $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS),
            $this->pensionRule()
        );

        self::assertSame('0.00', $result['employee_pension']);
        self::assertSame('0.00', $result['employer_pension']);
        self::assertSame('30000.00', $result['taxable_income']);
        self::assertSame('21550.00', $result['net_pay']);
    }

    public function test_retainer_service_is_excluded_from_employee_payroll(): void
    {
        $input = $this->standardEmployeeInput();
        $input['service_type'] = 'RETAINER_SERVICE';

        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculateCandidate(
            $input,
            $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS),
            $this->pensionRule()
        );
    }

    public function test_exempt_component_without_legal_evidence_is_rejected(): void
    {
        $input = $this->standardEmployeeInput();
        $input['earning_components'] = [[
            'code' => 'HOUSING_ALLOWANCE',
            'amount' => '500.00',
            'tax_treatment' => 'EXEMPT',
            'pension_treatment' => 'NON_PENSIONABLE',
        ]];

        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculateCandidate(
            $input,
            $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS),
            $this->pensionRule()
        );
    }

    public function test_non_standard_pensionable_salary_without_rationale_is_rejected(): void
    {
        $input = $this->standardEmployeeInput();
        $input['pensionable_salary'] = '25000.00';

        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculateCandidate(
            $input,
            $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS),
            $this->pensionRule()
        );
    }

    public function test_paye_bands_cannot_be_missing_or_gap_filled_by_defaults(): void
    {
        $rule = $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS);
        $rule['monthly_tax_bands'] = [];

        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculateCandidate($this->standardEmployeeInput(), $rule, $this->pensionRule());
    }

    public function test_missing_rounding_method_is_not_silently_defaulted(): void
    {
        $tax = $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS);
        unset($tax['rounding_method']);

        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculateCandidate($this->standardEmployeeInput(), $tax, $this->pensionRule());
    }

    public function test_production_calculation_is_blocked_without_independent_tax_base_opinion(): void
    {
        $tax = $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS_LESS_EMPLOYEE_PENSION);
        $tax['tax_base_decision']['status'] = 'REVIEW_REQUIRED';

        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculateApproved(
            $this->approvedEmployeeInput(),
            $this->approvedRuleRecords($tax, $this->pensionRule()),
            '2026-11-01',
            '2026-11-30',
            'payroll-preparer'
        );
    }

    public function test_production_calculation_requires_official_source_and_review_evidence(): void
    {
        $tax = $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS);
        $records = $this->approvedRuleRecords($tax, $this->pensionRule());
        $records[0]['source_reference'] = 'https://example.com/not-an-ethiopian-authority.pdf';

        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculateApproved(
            $this->approvedEmployeeInput(),
            $records,
            '2026-11-01',
            '2026-11-30',
            'payroll-preparer'
        );
    }

    public function test_production_calculation_requires_rules_to_cover_entire_period(): void
    {
        $records = $this->approvedRuleRecords(
            $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS),
            $this->pensionRule()
        );
        $records[1]['effective_to'] = '2026-11-15';

        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculateApproved(
            $this->approvedEmployeeInput(),
            $records,
            '2026-11-01',
            '2026-11-30',
            'payroll-preparer'
        );
    }

    public function test_production_calculation_uses_the_frozen_approved_rule_snapshot(): void
    {
        $incomeTax = $this->incomeTaxRule(EthiopiaPayrollCalculator::TAX_BASE_GROSS_LESS_EMPLOYEE_PENSION);
        $records = $this->approvedRuleRecords($incomeTax, $this->pensionRule());

        $result = $this->calculator->calculateApproved(
            $this->approvedEmployeeInput(),
            $records,
            '2026-11-01',
            '2026-11-30',
            'payroll-preparer'
        );

        self::assertSame('27900.00', $result['taxable_income']);
        self::assertSame('7715.00', $result['paye']);
        self::assertSame('20185.00', $result['net_pay']);
        self::assertSame('CALCULATED_DRAFT_NOT_POSTED', $result['calculation_status']);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $result['source_snapshot_sha256']);
    }

    private function standardEmployeeInput(): array
    {
        return [
            'payee_type' => 'EMPLOYEE',
            'currency' => 'ETB',
            'base_salary' => '30000.00',
            'earning_components' => [],
            'pensionable_salary' => '30000.00',
            'pension_coverage' => ['status' => 'COVERED'],
            'other_deductions' => [],
        ];
    }

    private function approvedEmployeeInput(): array
    {
        $input = $this->standardEmployeeInput();
        $input['pension_coverage'] = [
            'status' => 'COVERED',
            'review_status' => 'PROFESSIONALLY_APPROVED',
            'approved_by' => 'pension-reviewer',
            'approved_at' => '2026-10-31T12:00:00+03:00',
            'approval_reference' => 'PENSION-COVERAGE-OPINION-01',
            'reviewer_qualification' => 'Synthetic pension compliance reviewer fixture',
            'reviewer_registration_reference' => 'SYNTHETIC-PENSION-REGISTRATION',
            'source_document_reference' => '10000000-0000-4000-8000-000000000001',
            'source_url' => 'https://justice.gov.et/law/private-organization-employees-pension-proclamation-1268-2022/',
            'source_sha256' => hash('sha256', 'synthetic-coverage-source'),
            'article_reference' => 'Proclamation 1268/2022 Articles 2, 3 and 10',
        ];

        return $input;
    }

    private function incomeTaxRule(string $method): array
    {
        return [
            'tax_base_method' => $method,
            'rounding_method' => 'HALF_UP',
            'monthly_tax_bands' => [
                ['lower' => '0.00', 'upper' => '2000.00', 'rate_basis_points' => 0],
                ['lower' => '2000.00', 'upper' => '4000.00', 'rate_basis_points' => 1500],
                ['lower' => '4000.00', 'upper' => '7000.00', 'rate_basis_points' => 2000],
                ['lower' => '7000.00', 'upper' => '10000.00', 'rate_basis_points' => 2500],
                ['lower' => '10000.00', 'upper' => '14000.00', 'rate_basis_points' => 3000],
                ['lower' => '14000.00', 'upper' => null, 'rate_basis_points' => 3500],
            ],
            'tax_base_decision' => [
                'status' => 'APPROVED',
                'approved_method' => $method,
                'opinion_reference' => 'SYNTHETIC-LEGAL-OPINION-TEST',
                'reviewer_qualification' => 'Synthetic qualified tax reviewer fixture',
                'reviewer_user_id' => 'tax-reviewer',
                'reviewer_registration_reference' => 'SYNTHETIC-TAX-QUALIFICATION',
                'opinion_document_reference' => 'synthetic://tax-opinion',
            'opinion_document_storage_reference' => '10000000-0000-4000-8000-000000000002',
                'opinion_sha256' => hash('sha256', 'synthetic-tax-opinion'),
                'independence_confirmed' => true,
                'reviewed_at' => '2026-10-31T12:00:00+03:00',
                'article_references' => ['1395/2025 Article 11', '979/2016 Article 10'],
            ],
            'supporting_sources' => [$this->supportingSource('1395/2025', '1395-2025')],
        ];
    }

    private function pensionRule(): array
    {
        return [
            'salary_basis' => 'REGULAR_HOURS_MONTHLY_SALARY',
            'rounding_method' => 'HALF_UP',
            'employee_rate_basis_points' => 700,
            'employer_rate_basis_points' => 1100,
            'supporting_sources' => [
                $this->supportingSource('1268/2022', '1268-2022'),
                $this->supportingSource('1085/2025', '1085-2025'),
                $this->supportingSource('1086/2025', '1086-2025'),
                $this->supportingSource('1087/2025', '1087-2025'),
            ],
        ];
    }

    private function approvedRuleRecords(array $taxRule, array $pensionRule): array
    {
        return [
            $this->ruleRecord('INCOME_TAX', '1395-2025-TEST', $taxRule),
            $this->ruleRecord('PENSION', '1268-2022-TEST', $pensionRule),
        ];
    }

    private function ruleRecord(string $domain, string $code, array $payroll): array
    {
        return [
            'id' => $domain === 'PENSION' ? 'synthetic-rule-pension' : 'synthetic-rule-tax',
            'jurisdiction_code' => 'ETH',
            'rule_domain' => $domain,
            'rule_code' => $code,
            'source_authority' => 'Synthetic Ethiopian authority fixture',
            'source_title' => 'Synthetic legal source test fixture only',
            'source_reference' => 'https://justice.gov.et/synthetic-test-source.pdf',
            'source_document_reference' => ($domain === 'PENSION' ? '10000000-0000-4000-8000-000000000003' : '10000000-0000-4000-8000-000000000004'),
            'source_sha256' => str_repeat($domain === 'PENSION' ? 'b' : 'a', 64),
            'publication_date' => '2025-09-01',
            'effective_from' => '2025-07-08',
            'effective_to' => null,
            'verification_status' => 'PROFESSIONAL_APPROVED',
            'verified_by' => 'independent-rule-reviewer',
            'verified_at' => '2026-10-31T12:00:00+03:00',
            'professional_approval_reference' => 'SYNTHETIC-APPROVAL-FIXTURE',
            'rule_payload' => ['payroll' => $payroll],
        ];
    }

    private function minor(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '00');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function supportingSource(string $instrument, string $fileTag): array
    {
        return [
            'instrument_number' => $instrument,
            'source_authority' => 'Synthetic Ethiopian government source fixture',
            'source_title' => 'Synthetic test fixture; not a legal source',
            'article_references' => 'Illustrative test clause only',
            'source_url' => 'https://justice.gov.et/synthetic/'.$fileTag.'.pdf',
            'document_storage_reference' => '10000000-0000-4000-8000-000000000005',
            'source_sha256' => hash('sha256', 'synthetic-source-'.$fileTag),
            'publication_date' => '2025-09-01',
            'effective_from' => '2025-07-08',
            'review_status' => 'PROFESSIONALLY_APPROVED',
            'reviewer_user_id' => 'independent-source-reviewer',
            'reviewer_qualification' => 'Synthetic source reviewer credential fixture',
            'reviewer_registration_reference' => 'SYNTHETIC-REVIEWER-REGISTRATION',
            'reviewed_at' => '2026-10-31T12:00:00+03:00',
            'review_reference' => 'SYNTHETIC-SOURCE-REVIEW',
        ];
    }

}
