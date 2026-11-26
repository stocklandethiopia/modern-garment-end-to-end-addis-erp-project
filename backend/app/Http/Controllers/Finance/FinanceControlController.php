<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceControlService;
use App\Services\Tenant\TenantContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FinanceControlController extends Controller
{
    public function __construct(
        private readonly FinanceControlService $finance,
        private readonly TenantContextResolver $tenant
    ) {
    }

    public function storeJournal(Request $request): JsonResponse
    {
        Gate::authorize('finance.create-journal');
        $data = $request->validate([
            'legal_entity_id' => ['required','uuid'],
            'period_id' => ['required','uuid'],
            'journal_number' => ['required','string','max:100'],
            'journal_type' => ['required','in:GENERAL,AP,AR,INVENTORY,COSTING,PAYROLL_DRAFT,TAX_DRAFT,ADJUSTMENT,REVERSAL'],
            'entry_date' => ['required','date'],
            'currency' => ['required','string','size:3'],
            'description' => ['required','string','max:2000'],
            'source_document_type' => ['nullable','string','max:80'],
            'source_document_id' => ['nullable','uuid'],
            'lines' => ['required','array','min:2'],
            'lines.*.account_id' => ['required','uuid'],
            'lines.*.description' => ['nullable','string','max:500'],
            'lines.*.currency' => ['nullable','string','size:3'],
            'lines.*.debit_amount' => ['nullable','numeric','gte:0'],
            'lines.*.credit_amount' => ['nullable','numeric','gte:0'],
            'lines.*.base_debit_amount' => ['required','numeric','gte:0'],
            'lines.*.base_credit_amount' => ['required','numeric','gte:0'],
            'lines.*.exchange_rate_reference' => ['nullable','string','max:120'],
            'lines.*.cost_centre_code' => ['nullable','string','max:80'],
            'lines.*.source_reference' => ['nullable','array'],
        ]);

        return response()->json(['data' => $this->finance->createJournalDraft($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function submitJournal(string $journalId): JsonResponse
    {
        Gate::authorize('finance.submit-journal');
        return response()->json(['data' => $this->finance->submitJournal($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $journalId)]);
    }

    public function decideJournal(Request $request, string $journalId): JsonResponse
    {
        Gate::authorize('finance.approve-journal');
        $data = $request->validate([
            'approve' => ['required','boolean'],
            'reason' => ['required','string','max:2000'],
        ]);

        return response()->json(['data' => $this->finance->decideJournal($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $journalId, $data['approve'], $data['reason'])]);
    }

    public function postJournal(Request $request, string $journalId): JsonResponse
    {
        Gate::authorize('finance.post-journal');
        $data = $request->validate([
            'posting_number' => ['required','string','max:100'],
            'posting_reference' => ['nullable','string','max:180'],
        ]);

        return response()->json(['data' => $this->finance->postApprovedJournal(
            $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $journalId,
            $data['posting_number'], $data['posting_reference'] ?? null
        )], 201);
    }

    public function storePayrollDraft(Request $request): JsonResponse
    {
        Gate::authorize('payroll.prepare-draft');
        $data = $request->validate([
            'legal_entity_id' => ['required','uuid'],
            'payroll_run_code' => ['required','string','max:100'],
            'period_start' => ['required','date'],
            'period_end' => ['required','date','after_or_equal:period_start'],
            'currency' => ['required','string','size:3'],
            'jurisdiction_code' => ['required','string','max:24'],
            'statutory_rule_ids' => ['required','array','min:1'],
            'statutory_rule_ids.*' => ['required','uuid','distinct'],
            'calculation_snapshot' => ['nullable','array'],
        ]);

        return response()->json(['data' => $this->finance->createPayrollDraft($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function addPayrollDraftLine(Request $request, string $runId): JsonResponse
    {
        Gate::authorize('payroll.prepare-draft');
        $data = $request->validate([
            'worker_reference' => ['required','string','max:120'],
            'earning_components' => ['nullable','array'],
            'deduction_components' => ['nullable','array'],
            'gross_amount' => ['required','numeric','gte:0'],
            'total_deductions' => ['required','numeric','gte:0'],
            'calculation_evidence' => ['required','array'],
        ]);

        return response()->json(['data' => $this->finance->addPayrollDraftLine($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $runId, $data)], 201);
    }

    public function submitPayroll(string $runId): JsonResponse
    {
        Gate::authorize('payroll.submit-for-review');
        return response()->json(['data' => $this->finance->submitPayrollForProfessionalReview($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $runId)]);
    }

    public function decidePayroll(Request $request, string $runId): JsonResponse
    {
        Gate::authorize('payroll.professional-approval');
        $data = $request->validate([
            'approve' => ['required','boolean'],
            'professional_approval_reference' => ['required','string','max:180'],
            'reason' => ['required','string','max:2000'],
        ]);

        return response()->json(['data' => $this->finance->applyProfessionalPayrollDecision(
            $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $runId,
            $data['approve'], $data['professional_approval_reference'], $data['reason']
        )]);
    }
}
