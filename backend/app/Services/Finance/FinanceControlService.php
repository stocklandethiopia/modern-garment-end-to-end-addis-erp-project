<?php

namespace App\Services\Finance;

use App\Services\Support\DecimalQuantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FinanceControlService
{
    public function createJournalDraft(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $period = DB::table('finance_periods')->where('tenant_id', $tenantId)
                ->where('id', $data['period_id'])->where('legal_entity_id', $data['legal_entity_id'])->lockForUpdate()->first();
            if ($period === null || $period->status !== 'OPEN') {
                throw ValidationException::withMessages(['period_id' => 'A journal requires an open accounting period for the selected legal entity.']);
            }

            if ($data['entry_date'] < $period->starts_on || $data['entry_date'] > $period->ends_on) {
                throw ValidationException::withMessages(['entry_date' => 'The journal entry date must be inside the selected accounting period.']);
            }

            $id = (string) Str::uuid();
            DB::table('finance_journals')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'legal_entity_id' => $data['legal_entity_id'],
                'period_id' => $period->id,
                'journal_number' => $data['journal_number'],
                'journal_type' => $data['journal_type'],
                'entry_date' => $data['entry_date'],
                'currency' => strtoupper($data['currency']),
                'description' => $data['description'],
                'source_document_type' => $data['source_document_type'] ?? null,
                'source_document_id' => $data['source_document_id'] ?? null,
                'status' => 'DRAFT',
                'created_by' => $actorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($data['lines'] as $index => $line) {
                $account = DB::table('finance_accounts')->where('tenant_id', $tenantId)
                    ->where('legal_entity_id', $data['legal_entity_id'])->where('id', $line['account_id'])
                    ->where('status', 'ACTIVE')->first();
                if ($account === null) {
                    throw ValidationException::withMessages(["lines.{$index}.account_id" => 'The account is not active for the selected legal entity.']);
                }

                $debit = (string) ($line['debit_amount'] ?? '0');
                $credit = (string) ($line['credit_amount'] ?? '0');
                $baseDebit = (string) ($line['base_debit_amount'] ?? '0');
                $baseCredit = (string) ($line['base_credit_amount'] ?? '0');
                if (DecimalQuantity::units($debit) > 0 === DecimalQuantity::units($credit) > 0) {
                    throw ValidationException::withMessages(["lines.{$index}" => 'Each journal line must contain either a debit or credit, not both.']);
                }
                if (DecimalQuantity::units($baseDebit) > 0 === DecimalQuantity::units($baseCredit) > 0) {
                    throw ValidationException::withMessages(["lines.{$index}" => 'Each journal line must contain either a base debit or base credit, not both.']);
                }

                DB::table('finance_journal_lines')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'journal_id' => $id,
                    'line_no' => $index + 1,
                    'account_id' => $account->id,
                    'description' => $line['description'] ?? null,
                    'currency' => strtoupper($line['currency'] ?? $data['currency']),
                    'debit_amount' => $debit,
                    'credit_amount' => $credit,
                    'base_debit_amount' => $baseDebit,
                    'base_credit_amount' => $baseCredit,
                    'exchange_rate_reference' => $line['exchange_rate_reference'] ?? null,
                    'cost_centre_code' => $line['cost_centre_code'] ?? null,
                    'source_reference' => json_encode($line['source_reference'] ?? [], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                ]);
            }

            return DB::table('finance_journals')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function submitJournal(string $tenantId, string $actorId, string $journalId): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $journalId): object {
            $journal = DB::table('finance_journals')->where('tenant_id', $tenantId)->where('id', $journalId)->lockForUpdate()->first();
            if ($journal === null || $journal->status !== 'DRAFT' || $journal->created_by !== $actorId) {
                throw ValidationException::withMessages(['journal' => 'Only the creator can submit a draft journal for review.']);
            }

            DB::table('finance_journals')->where('tenant_id', $tenantId)->where('id', $journalId)->update([
                'status' => 'UNDER_REVIEW',
                'submitted_by' => $actorId,
                'submitted_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('finance_journals')->where('tenant_id', $tenantId)->where('id', $journalId)->first();
        }, 3);
    }

    public function decideJournal(string $tenantId, string $actorId, string $journalId, bool $approve, string $reason): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $journalId, $approve, $reason): object {
            $journal = DB::table('finance_journals')->where('tenant_id', $tenantId)->where('id', $journalId)->lockForUpdate()->first();
            if ($journal === null || $journal->status !== 'UNDER_REVIEW') {
                throw ValidationException::withMessages(['journal' => 'Only a journal under review can be approved or rejected.']);
            }
            if ($journal->created_by === $actorId || $journal->submitted_by === $actorId) {
                throw ValidationException::withMessages(['approver' => 'The journal creator or submitter cannot approve the journal.']);
            }
            if (trim($reason) === '') {
                throw ValidationException::withMessages(['reason' => 'An approval or rejection reason is required.']);
            }

            $period = DB::table('finance_periods')->where('tenant_id', $tenantId)->where('id', $journal->period_id)->first();
            if ($approve && ($period === null || $period->status !== 'OPEN')) {
                throw ValidationException::withMessages(['period' => 'The accounting period must remain open for journal approval.']);
            }

            DB::table('finance_journals')->where('tenant_id', $tenantId)->where('id', $journalId)->update([
                'status' => $approve ? 'APPROVED' : 'REJECTED',
                'approved_by' => $actorId,
                'approved_at' => now(),
                'approval_reason' => $reason,
                'updated_at' => now(),
            ]);

            return DB::table('finance_journals')->where('tenant_id', $tenantId)->where('id', $journalId)->first();
        }, 3);
    }

    public function postApprovedJournal(string $tenantId, string $actorId, string $journalId, string $postingNumber, ?string $postingReference): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $journalId, $postingNumber, $postingReference): object {
            $journal = DB::table('finance_journals')->where('tenant_id', $tenantId)->where('id', $journalId)->lockForUpdate()->first();
            if ($journal === null || $journal->status !== 'APPROVED' || $journal->approved_by === $actorId) {
                throw ValidationException::withMessages(['journal' => 'Posting requires an approved journal and a separate posting actor.']);
            }
            $period = DB::table('finance_periods')->where('tenant_id', $tenantId)->where('id', $journal->period_id)->lockForUpdate()->first();
            if ($period === null || $period->status !== 'OPEN') {
                throw ValidationException::withMessages(['period' => 'The accounting period must be open at posting time.']);
            }
            $existing = DB::table('finance_journal_postings')->where('tenant_id', $tenantId)->where('journal_id', $journalId)->first();
            if ($existing !== null) {
                return $existing;
            }

            $totals = DB::table('finance_journal_lines')->where('tenant_id', $tenantId)->where('journal_id', $journalId)
                ->selectRaw('COUNT(*) AS line_count, SUM(base_debit_amount) AS debit_total, SUM(base_credit_amount) AS credit_total')->first();
            if ((int) $totals->line_count < 2 ||
                DecimalQuantity::units((string) $totals->debit_total) <= 0 ||
                ! DecimalQuantity::equal((string) $totals->debit_total, (string) $totals->credit_total)) {
                throw ValidationException::withMessages(['balance' => 'Journal lines must balance before posting.']);
            }

            $id = (string) Str::uuid();
            DB::table('finance_journal_postings')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'journal_id' => $journalId,
                'posting_number' => $postingNumber,
                'posted_by' => $actorId,
                'posted_at' => now(),
                'posting_reference' => $postingReference,
                'created_at' => now(),
            ]);

            return DB::table('finance_journal_postings')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function createPayrollDraft(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $entity = DB::table('legal_entities')->where('tenant_id', $tenantId)->where('id', $data['legal_entity_id'])->exists();
            if (! $entity) {
                throw ValidationException::withMessages(['legal_entity_id' => 'The selected legal entity is not in this tenant.']);
            }

            $verifiedRules = DB::table('statutory_rule_versions')->where('tenant_id', $tenantId)
                ->where('jurisdiction_code', $data['jurisdiction_code'])
                ->where('verification_status', 'PROFESSIONAL_APPROVED')
                ->whereDate('effective_from', '<=', $data['period_end'])
                ->where(function ($q) use ($data): void {
                    $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $data['period_end']);
                })->whereIn('id', $data['statutory_rule_ids'])->get();

            if ($verifiedRules->count() !== count(array_unique($data['statutory_rule_ids']))) {
                throw ValidationException::withMessages(['statutory_rule_ids' => 'Every payroll statutory rule must be professionally approved and effective for the payroll period.']);
            }

            $id = (string) Str::uuid();
            DB::table('payroll_run_drafts')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'legal_entity_id' => $data['legal_entity_id'],
                'payroll_run_code' => $data['payroll_run_code'],
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'currency' => strtoupper($data['currency']),
                'calculation_snapshot' => json_encode($data['calculation_snapshot'] ?? [], JSON_THROW_ON_ERROR),
                'statutory_rule_set' => json_encode($verifiedRules->map(fn ($r) => ['id' => $r->id, 'sha256' => $r->source_sha256, 'rule_code' => $r->rule_code])->values()->all(), JSON_THROW_ON_ERROR),
                'legal_review_status' => 'REQUIRED',
                'status' => 'DRAFT',
                'control_totals' => json_encode([], JSON_THROW_ON_ERROR),
                'hold_reasons' => json_encode([], JSON_THROW_ON_ERROR),
                'prepared_by' => $actorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('payroll_run_drafts')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function addPayrollDraftLine(string $tenantId, string $actorId, string $runId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $runId, $data): object {
            $run = DB::table('payroll_run_drafts')->where('tenant_id', $tenantId)->where('id', $runId)->lockForUpdate()->first();
            if ($run === null || $run->status !== 'DRAFT' || $run->prepared_by !== $actorId) {
                throw ValidationException::withMessages(['payroll_run' => 'Only the payroll preparer can add lines to a draft payroll run.']);
            }
            $gross = (string) $data['gross_amount'];
            $deductions = (string) $data['total_deductions'];
            $grossUnits = DecimalQuantity::units($gross);
            $deductionUnits = DecimalQuantity::units($deductions);
            if ($grossUnits < $deductionUnits) {
                throw ValidationException::withMessages(['net_amount' => 'Total deductions cannot exceed gross earnings.']);
            }
            $net = DecimalQuantity::format($grossUnits - $deductionUnits);

            $id = (string) Str::uuid();
            DB::table('payroll_run_lines')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'payroll_run_draft_id' => $runId,
                'worker_reference' => $data['worker_reference'],
                'earning_components' => json_encode($data['earning_components'] ?? [], JSON_THROW_ON_ERROR),
                'deduction_components' => json_encode($data['deduction_components'] ?? [], JSON_THROW_ON_ERROR),
                'gross_amount' => $gross,
                'total_deductions' => $deductions,
                'net_amount' => $net,
                'currency' => $run->currency,
                'calculation_evidence' => json_encode($data['calculation_evidence'], JSON_THROW_ON_ERROR),
                'calculation_status' => 'DRAFT',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('payroll_run_lines')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function submitPayrollForProfessionalReview(string $tenantId, string $actorId, string $runId): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $runId): object {
            $run = DB::table('payroll_run_drafts')->where('tenant_id', $tenantId)->where('id', $runId)->lockForUpdate()->first();
            if ($run === null || $run->prepared_by !== $actorId || $run->status !== 'DRAFT') {
                throw ValidationException::withMessages(['payroll_run' => 'Only the preparer can submit a draft run.']);
            }
            if (! DB::table('payroll_run_lines')->where('tenant_id', $tenantId)->where('payroll_run_draft_id', $runId)->exists()) {
                throw ValidationException::withMessages(['payroll_run_lines' => 'A payroll run must contain at least one worker line.']);
            }
            DB::table('payroll_run_drafts')->where('tenant_id', $tenantId)->where('id', $runId)->update([
                'status' => 'UNDER_REVIEW',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'updated_at' => now(),
            ]);
            return DB::table('payroll_run_drafts')->where('tenant_id', $tenantId)->where('id', $runId)->first();
        }, 3);
    }

    public function applyProfessionalPayrollDecision(string $tenantId, string $professionalId, string $runId, bool $approve, string $reference, string $reason): object
    {
        return DB::transaction(function () use ($tenantId, $professionalId, $runId, $approve, $reference, $reason): object {
            $run = DB::table('payroll_run_drafts')->where('tenant_id', $tenantId)->where('id', $runId)->lockForUpdate()->first();
            if ($run === null || $run->status !== 'UNDER_REVIEW' || $run->prepared_by === $professionalId) {
                throw ValidationException::withMessages(['payroll_run' => 'Payroll professional review must be separate from preparation.']);
            }
            if (trim($reference) === '' || trim($reason) === '') {
                throw ValidationException::withMessages(['professional_approval' => 'Professional approval reference and decision reason are required.']);
            }

            DB::table('payroll_run_drafts')->where('tenant_id', $tenantId)->where('id', $runId)->update([
                'legal_review_status' => $approve ? 'APPROVED' : 'HOLD',
                'status' => $approve ? 'APPROVED_FOR_PAYROLL' : 'LEGAL_HOLD',
                'legal_approved_by' => $professionalId,
                'legal_approved_at' => now(),
                'legal_approval_reference' => $reference,
                'reviewed_by' => $professionalId,
                'reviewed_at' => now(),
                'hold_reasons' => json_encode($approve ? [] : [['reason' => $reason]], JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);

            return DB::table('payroll_run_drafts')->where('tenant_id', $tenantId)->where('id', $runId)->first();
        }, 3);
    }
}
