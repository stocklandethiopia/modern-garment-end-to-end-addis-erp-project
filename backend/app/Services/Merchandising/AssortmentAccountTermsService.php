<?php

namespace App\Services\Merchandising;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssortmentAccountTermsService
{
    private const CHANNELS = ['WHOLESALE', 'RESELLER', 'CUSTOMER_PROGRAMME', 'EXPORT'];

    public function index(string $tenantId, string $assortmentPlanId): mixed
    {
        $plan = $this->plan($tenantId, $assortmentPlanId);
        abort_if($plan === null, 404);

        return DB::table('assortment_account_terms as t')
            ->join('price_lists as p', function ($join): void {
                $join->on('p.tenant_id', '=', 't.tenant_id')
                    ->on('p.id', '=', 't.price_list_id');
            })
            ->where('t.tenant_id', $tenantId)
            ->where('t.assortment_plan_id', $assortmentPlanId)
            ->select(['t.*', 'p.code as price_list_code', 'p.currency as price_list_currency'])
            ->orderByDesc('t.version_no')
            ->paginate(50);
    }

    public function create(string $tenantId, string $assortmentPlanId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $assortmentPlanId, $actorId, $data): object {
            $plan = $this->plan($tenantId, $assortmentPlanId, true);
            abort_if($plan === null, 404);
            abort_unless($plan->status === 'DRAFT', 422, 'Account terms can only be added to a draft assortment plan.');
            abort_unless(in_array($plan->channel_code, self::CHANNELS, true), 422, 'This channel does not use wholesale/reseller account terms.');
            abort_unless(
                is_string($plan->account_reference) && trim($plan->account_reference) !== '',
                422,
                'The assortment plan must identify its commercial account before account terms can be added.',
            );

            $rangePlan = DB::table('range_plans')
                ->where('tenant_id', $tenantId)
                ->where('id', $plan->range_plan_id)
                ->first();

            abort_if($rangePlan === null, 404);
            $collection = DB::table('collections')
                ->where('tenant_id', $tenantId)
                ->where('id', $rangePlan->collection_id)
                ->first();

            abort_if($collection === null, 404);

            $priceList = DB::table('price_lists')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['price_list_id'])
                ->lockForUpdate()
                ->first();

            abort_if($priceList === null, 404, 'The selected price list does not belong to this tenant.');
            abort_unless($priceList->status === 'PUBLISHED', 422, 'Account terms require a published price list.');
            abort_unless(
                strtoupper(trim($priceList->channel_code)) === strtoupper(trim($plan->channel_code)),
                422,
                'The price-list channel does not match the assortment channel.',
            );
            abort_unless(
                strtoupper(trim($priceList->market_scope)) === strtoupper(trim($plan->market_scope)),
                422,
                'The price-list market does not match the assortment market.',
            );
            abort_unless(
                trim((string) $priceList->account_reference) === trim((string) $plan->account_reference),
                422,
                'The published price list must be scoped to the same account as the assortment.',
            );
            abort_unless(
                $priceList->brand_id === null || $priceList->brand_id === $collection->brand_id,
                422,
                'The published price list belongs to a different brand.',
            );
            $priceListStart = \Carbon\CarbonImmutable::parse($priceList->effective_from);
            $termsStart = \Carbon\CarbonImmutable::parse($data['effective_from'])->startOfDay();
            $priceListEnd = $priceList->effective_to === null
                ? null
                : \Carbon\CarbonImmutable::parse($priceList->effective_to);
            $termsEnd = isset($data['effective_to'])
                ? \Carbon\CarbonImmutable::parse($data['effective_to'])->endOfDay()
                : null;

            abort_unless(
                $priceListStart->lessThanOrEqualTo($termsStart)
                    && ($priceListEnd === null || ($termsEnd !== null && $priceListEnd->greaterThanOrEqualTo($termsEnd))),
                422,
                'The published price list does not cover the complete account-terms effective period.',
            );

            DB::selectOne(
                'SELECT pg_advisory_xact_lock(hashtextextended(?, 0))',
                [$tenantId.':assortment-account-terms:'.$assortmentPlanId],
            );

            $version = (int) DB::table('assortment_account_terms')
                ->where('tenant_id', $tenantId)
                ->where('assortment_plan_id', $assortmentPlanId)
                ->max('version_no') + 1;

            $id = (string) Str::uuid();
            $now = now();
            DB::table('assortment_account_terms')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'assortment_plan_id' => $assortmentPlanId,
                'price_list_id' => $priceList->id,
                'version_no' => $version,
                'channel_code' => strtoupper(trim($plan->channel_code)),
                'market_scope' => strtoupper(trim($plan->market_scope)),
                'account_reference' => trim($plan->account_reference),
                'account_tier' => isset($data['account_tier']) ? strtoupper(trim($data['account_tier'])) : null,
                'commercial_terms_reference' => trim($data['commercial_terms_reference']),
                'payment_terms_reference' => trim($data['payment_terms_reference']),
                'delivery_terms_reference' => trim($data['delivery_terms_reference']),
                'returns_policy_reference' => isset($data['returns_policy_reference'])
                    ? trim($data['returns_policy_reference'])
                    : null,
                'currency' => strtoupper($priceList->currency),
                'minimum_order_units' => $data['minimum_order_units'],
                'order_multiple_units' => $data['order_multiple_units'],
                'effective_from' => $data['effective_from'],
                'effective_to' => $data['effective_to'] ?? null,
                'change_reason' => trim($data['change_reason']),
                'status' => 'DRAFT',
                'created_by' => $actorId,
                'submitted_by' => null,
                'submitted_at' => null,
                'decided_by' => null,
                'decided_at' => null,
                'decision_reason' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->audit(
                $tenantId,
                $actorId,
                'ASSORTMENT_ACCOUNT_TERMS_DRAFT_CREATED',
                $id,
                'Versioned external account-term references and published price-list scope captured; no order or payment was created.',
            );

            return DB::table('assortment_account_terms')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();
        });
    }

    public function submit(
        string $tenantId,
        string $assortmentPlanId,
        string $termsId,
        string $actorId,
        bool $canAdminister,
    ): object {
        return DB::transaction(function () use (
            $tenantId,
            $assortmentPlanId,
            $termsId,
            $actorId,
            $canAdminister,
        ): object {
            $terms = DB::table('assortment_account_terms')
                ->where('tenant_id', $tenantId)
                ->where('assortment_plan_id', $assortmentPlanId)
                ->where('id', $termsId)
                ->lockForUpdate()
                ->first();

            abort_if($terms === null, 404);
            abort_unless(
                $terms->created_by === $actorId || $canAdminister,
                403,
                'Only the account-terms author or a tenant administrator may submit this version.',
            );
            abort_unless($terms->status === 'DRAFT', 422, 'Only a draft account-terms version can be submitted.');

            DB::table('assortment_account_terms')
                ->where('tenant_id', $tenantId)
                ->where('id', $termsId)
                ->update([
                    'status' => 'PENDING_APPROVAL',
                    'submitted_by' => $actorId,
                    'submitted_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->audit(
                $tenantId,
                $actorId,
                'ASSORTMENT_ACCOUNT_TERMS_SUBMITTED',
                $termsId,
                'Account-specific assortment terms submitted for independent approval.',
            );

            return DB::table('assortment_account_terms')
                ->where('tenant_id', $tenantId)
                ->where('id', $termsId)
                ->first();
        });
    }

    public function decide(
        string $tenantId,
        string $assortmentPlanId,
        string $termsId,
        string $actorId,
        string $decision,
        string $reason,
    ): object {
        return DB::transaction(function () use (
            $tenantId,
            $assortmentPlanId,
            $termsId,
            $actorId,
            $decision,
            $reason,
        ): object {
            $terms = DB::table('assortment_account_terms')
                ->where('tenant_id', $tenantId)
                ->where('assortment_plan_id', $assortmentPlanId)
                ->where('id', $termsId)
                ->lockForUpdate()
                ->first();

            abort_if($terms === null, 404);
            abort_unless($terms->status === 'PENDING_APPROVAL', 422, 'Account terms are not awaiting a decision.');
            abort_unless($terms->created_by !== $actorId, 403, 'The account-terms author cannot approve or reject their own version.');

            $status = $decision === 'APPROVE' ? 'APPROVED' : 'REJECTED';
            DB::table('merchandising_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'object_type' => 'ASSORTMENT_ACCOUNT_TERMS',
                'object_id' => $termsId,
                'object_version' => $terms->version_no,
                'decision' => $decision === 'APPROVE' ? 'APPROVE_ACCOUNT_TERMS' : 'REJECT_ACCOUNT_TERMS',
                'reason' => $reason,
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $actorId,
                'decided_at' => now(),
            ]);

            DB::table('assortment_account_terms')
                ->where('tenant_id', $tenantId)
                ->where('id', $termsId)
                ->update([
                    'status' => $status,
                    'decided_by' => $actorId,
                    'decided_at' => now(),
                    'decision_reason' => $reason,
                    'updated_at' => now(),
                ]);

            $this->audit(
                $tenantId,
                $actorId,
                'ASSORTMENT_ACCOUNT_TERMS_'.$status,
                $termsId,
                $reason,
            );

            return DB::table('assortment_account_terms')
                ->where('tenant_id', $tenantId)
                ->where('id', $termsId)
                ->first();
        });
    }

    private function plan(string $tenantId, string $assortmentPlanId, bool $lock = false): ?object
    {
        $query = DB::table('assortment_plans')
            ->where('tenant_id', $tenantId)
            ->where('id', $assortmentPlanId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function audit(
        string $tenantId,
        string $actorId,
        string $action,
        string $objectId,
        string $reason,
    ): void {
        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => 'ASSORTMENT_ACCOUNT_TERMS',
            'object_id' => $objectId,
            'scope' => json_encode(['module' => 'MERCHANDISING'], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
