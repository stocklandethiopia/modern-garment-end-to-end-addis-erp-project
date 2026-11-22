<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CommercialPricingController extends Controller
{
    public function priceLists(TenantContext $tenantContext): JsonResponse
    {
        return response()->json([
            'data' => DB::table('price_lists as p')
                ->leftJoin('brands as b', function ($join): void {
                    $join->on('b.tenant_id', '=', 'p.tenant_id')
                        ->on('b.id', '=', 'p.brand_id');
                })
                ->where('p.tenant_id', $tenantContext->tenantId())
                ->orderByDesc('p.created_at')
                ->select(['p.*', 'b.name as brand_name'])
                ->paginate(50),
        ]);
    }

    public function createPriceList(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $data = $request->validate([
            'legal_entity_id' => [
                'required',
                'uuid',
                Rule::exists('legal_entities', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'brand_id' => [
                'nullable',
                'uuid',
                Rule::exists('brands', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('status', 'ACTIVE')),
            ],
            'code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/'],
            'channel_code' => ['required', 'string', 'max:60'],
            'market_scope' => ['required', 'string', 'max:160'],
            'account_reference' => ['nullable', 'string', 'max:120'],
            'currency' => ['required', 'string', 'size:3', 'alpha:upper'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
        ]);

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();
        $code = strtoupper($data['code']);

        $priceList = DB::transaction(function () use ($tenantId, $data, $id, $actor, $code): object {
            DB::select('SELECT pg_advisory_xact_lock(hashtext(?))', ['price-list:'.$tenantId.':'.$code]);

            $version = (int) DB::table('price_lists')
                ->where('tenant_id', $tenantId)
                ->where('code', $code)
                ->max('version_no') + 1;

            DB::table('price_lists')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'legal_entity_id' => $data['legal_entity_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'code' => $code,
                'channel_code' => strtoupper(trim($data['channel_code'])),
                'market_scope' => trim($data['market_scope']),
                'account_reference' => $data['account_reference'] ?? null,
                'currency' => strtoupper($data['currency']),
                'version_no' => $version,
                'effective_from' => $data['effective_from'],
                'effective_to' => $data['effective_to'] ?? null,
                'status' => 'DRAFT',
                'created_by' => $actor,
                'published_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'PRICE_LIST_CREATED', 'PRICE_LIST', $id, 'Draft price list version created.');

            return DB::table('price_lists')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $priceList], 201);
    }

    public function priceListLines(string $priceListId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $priceList = DB::table('price_lists')
            ->where('tenant_id', $tenantId)
            ->where('id', $priceListId)
            ->first();

        abort_if($priceList === null, 404);

        return response()->json([
            'data' => DB::table('price_list_lines')
                ->where('tenant_id', $tenantId)
                ->where('price_list_id', $priceListId)
                ->orderBy('product_reference')
                ->paginate(100),
        ]);
    }

    public function createPriceListLine(string $priceListId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'product_reference' => ['required', 'string', 'max:120'],
            'list_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'price_floor' => ['nullable', 'numeric', 'min:0'],
            'cost_basis' => ['nullable', 'numeric', 'min:0'],
            'tax_profile_reference' => ['nullable', 'string', 'max:120'],
        ]);

        $listPrice = $this->decimal($data['list_price']);
        $priceFloor = isset($data['price_floor']) ? $this->decimal($data['price_floor']) : null;

        abort_unless($priceFloor === null || $this->compareDecimals($priceFloor, $listPrice) <= 0, 422, 'The price floor cannot exceed the list price.');
        if ($priceFloor !== null && isset($data['wholesale_price'])) {
            abort_unless($this->compareDecimals($this->decimal($data['wholesale_price']), $priceFloor) >= 0, 422, 'The wholesale price cannot be below the approved price floor.');
        }

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $line = DB::transaction(function () use ($tenantId, $priceListId, $data, $id, $actor, $listPrice, $priceFloor): object {
            $priceList = DB::table('price_lists')
                ->where('tenant_id', $tenantId)
                ->where('id', $priceListId)
                ->lockForUpdate()
                ->first();

            abort_if($priceList === null, 404);
            abort_unless($priceList->status === 'DRAFT', 422, 'Price-list lines can only be changed while the price list is in draft.');
            abort_if(
                DB::table('price_list_lines')->where('tenant_id', $tenantId)
                    ->where('price_list_id', $priceListId)
                    ->where('product_reference', trim($data['product_reference']))
                    ->exists(),
                422,
                'This product reference already exists in the price list.',
            );

            DB::table('price_list_lines')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'price_list_id' => $priceListId,
                'product_reference' => trim($data['product_reference']),
                'list_price' => $listPrice,
                'wholesale_price' => isset($data['wholesale_price']) ? $this->decimal($data['wholesale_price']) : null,
                'price_floor' => $priceFloor,
                'cost_basis' => isset($data['cost_basis']) ? $this->decimal($data['cost_basis']) : null,
                'tax_profile_reference' => $data['tax_profile_reference'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'PRICE_LIST_LINE_CREATED', 'PRICE_LIST_LINE', $id, 'Draft price line added; not yet commercially published.');

            return DB::table('price_list_lines')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $line], 201);
    }

    public function publishPriceList(string $priceListId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $approver = (string) $request->user()->getAuthIdentifier();
        abort_unless($tenantContext->canAdministerTenant(), 403, 'A tenant administrator must publish a price list.');

        $priceList = DB::transaction(function () use ($tenantId, $priceListId, $approver): object {
            $scopeSeed = DB::table('price_lists')
                ->where('tenant_id', $tenantId)
                ->where('id', $priceListId)
                ->first();

            abort_if($scopeSeed === null, 404);

            $scopeLock = implode('|', [
                $tenantId,
                $scopeSeed->legal_entity_id,
                $scopeSeed->channel_code,
                $scopeSeed->market_scope,
                $scopeSeed->currency,
                $scopeSeed->brand_id ?? '*',
                $scopeSeed->account_reference ?? '*',
            ]);
            DB::select('SELECT pg_advisory_xact_lock(hashtext(?))', ['price-list-scope:'.$scopeLock]);

            $priceList = DB::table('price_lists')
                ->where('tenant_id', $tenantId)
                ->where('id', $priceListId)
                ->lockForUpdate()
                ->first();

            abort_if($priceList === null, 404);
            abort_unless($priceList->status === 'DRAFT', 422, 'Only a draft price list can be published.');
            abort_unless($priceList->created_by !== $approver, 403, 'The price-list author cannot publish their own version.');

            $lineCount = DB::table('price_list_lines')
                ->where('tenant_id', $tenantId)
                ->where('price_list_id', $priceListId)
                ->count();

            abort_unless($lineCount > 0, 422, 'A price list must contain at least one approved product price before publication.');

            $overlap = DB::table('price_lists')
                ->where('tenant_id', $tenantId)
                ->where('legal_entity_id', $priceList->legal_entity_id)
                ->where('channel_code', $priceList->channel_code)
                ->where('market_scope', $priceList->market_scope)
                ->where('currency', $priceList->currency)
                ->where('status', 'PUBLISHED')
                ->where('id', '<>', $priceListId)
                ->when(
                    $priceList->brand_id !== null,
                    fn ($query) => $query->where('brand_id', $priceList->brand_id),
                    fn ($query) => $query->whereNull('brand_id'),
                )
                ->when(
                    $priceList->account_reference !== null,
                    fn ($query) => $query->where('account_reference', $priceList->account_reference),
                    fn ($query) => $query->whereNull('account_reference'),
                )
                ->where(function ($query) use ($priceList): void {
                    $query->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $priceList->effective_from);
                })
                ->when(
                    $priceList->effective_to !== null,
                    fn ($query) => $query->where('effective_from', '<=', $priceList->effective_to),
                )
                ->exists();

            abort_unless(! $overlap, 422, 'A published price list already overlaps this commercial scope and effective period.');

            DB::table('merchandising_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'object_type' => 'PRICE_LIST',
                'object_id' => $priceListId,
                'object_version' => $priceList->version_no,
                'decision' => 'PUBLISH',
                'reason' => 'Price list independently published for its effective commercial scope.',
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $approver,
                'decided_at' => now(),
            ]);

            DB::table('price_lists')->where('tenant_id', $tenantId)->where('id', $priceListId)->update([
                'status' => 'PUBLISHED',
                'published_by' => $approver,
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $approver, 'PRICE_LIST_PUBLISHED', 'PRICE_LIST', $priceListId, 'Independent approval; effective dates govern channel price resolution.');

            return DB::table('price_lists')->where('tenant_id', $tenantId)->where('id', $priceListId)->first();
        });

        return response()->json(['data' => $priceList]);
    }

    public function promotions(TenantContext $tenantContext): JsonResponse
    {
        return response()->json([
            'data' => DB::table('promotions as p')
                ->join('price_lists as l', function ($join): void {
                    $join->on('l.tenant_id', '=', 'p.tenant_id')
                        ->on('l.id', '=', 'p.price_list_id');
                })
                ->where('p.tenant_id', $tenantContext->tenantId())
                ->orderByDesc('p.created_at')
                ->select(['p.*', 'l.code as price_list_code', 'l.currency', 'l.channel_code'])
                ->paginate(50),
        ]);
    }

    public function createPromotion(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $data = $request->validate([
            'price_list_id' => [
                'required',
                'uuid',
                Rule::exists('price_lists', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('status', 'PUBLISHED')),
            ],
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/',
                Rule::unique('promotions', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'promotion_type' => ['required', Rule::in(['PERCENTAGE', 'FIXED_AMOUNT'])],
            'discount_value' => ['required', 'numeric', 'gt:0'],
            'stack_policy' => ['required', Rule::in(['NO_STACK', 'STACK_WITH_COUPON', 'STACK_WITH_MARKDOWN'])],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'funded_by' => ['nullable', 'string', 'max:80'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'margin_floor' => ['nullable', 'numeric', 'between:-100,100'],
        ]);

        $discount = $this->decimal($data['discount_value']);
        if ($data['promotion_type'] === 'PERCENTAGE') {
            abort_unless($this->compareDecimals($discount, '100.0000') <= 0, 422, 'A percentage promotion cannot exceed 100%.');
        }

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $promotion = DB::transaction(function () use ($tenantId, $data, $discount, $id, $actor): object {
            $priceList = DB::table('price_lists')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['price_list_id'])
                ->first();

            abort_if($priceList === null, 404);
            abort_unless($priceList->status === 'PUBLISHED', 422, 'Promotion must reference a published price-list version.');
            $promotionStart = Carbon::parse($data['starts_at']);
            $promotionEnd = Carbon::parse($data['ends_at']);
            $priceListStart = Carbon::parse($priceList->effective_from);
            $priceListEnd = $priceList->effective_to !== null ? Carbon::parse($priceList->effective_to) : null;

            abort_unless(
                $promotionStart->greaterThanOrEqualTo($priceListStart)
                    && ($priceListEnd === null || $promotionEnd->lessThanOrEqualTo($priceListEnd)),
                422,
                'Promotion effective dates must be inside the published price-list effective period.',
            );

            DB::table('promotions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'price_list_id' => $data['price_list_id'],
                'code' => strtoupper($data['code']),
                'promotion_type' => $data['promotion_type'],
                'discount_value' => $discount,
                'stack_policy' => $data['stack_policy'],
                'budget_amount' => isset($data['budget_amount']) ? $this->decimal($data['budget_amount'], 16) : null,
                'funded_by' => $data['funded_by'] ?? null,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'margin_floor' => isset($data['margin_floor']) ? $this->decimal($data['margin_floor']) : null,
                'status' => 'DRAFT',
                'created_by' => $actor,
                'approved_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'PROMOTION_CREATED', 'PROMOTION', $id, 'Promotion saved as draft; it cannot be applied to an order.');

            return DB::table('promotions')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $promotion], 201);
    }

    public function addPromotionEligibility(string $promotionId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'scope_type' => ['required', Rule::in(['CHANNEL', 'PRODUCT', 'CATEGORY', 'ACCOUNT', 'LOCATION', 'BRAND'])],
            'scope_reference' => ['required', 'string', 'min:1', 'max:160'],
            'is_exclusion' => ['required', 'boolean'],
        ]);

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $eligibility = DB::transaction(function () use ($tenantId, $promotionId, $data, $id, $actor): object {
            $promotion = DB::table('promotions')
                ->where('tenant_id', $tenantId)
                ->where('id', $promotionId)
                ->lockForUpdate()
                ->first();

            abort_if($promotion === null, 404);
            abort_unless($promotion->status === 'DRAFT', 422, 'Promotion eligibility can only change while draft.');

            DB::table('promotion_eligibility')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'promotion_id' => $promotionId,
                'scope_type' => $data['scope_type'],
                'scope_reference' => trim($data['scope_reference']),
                'is_exclusion' => $data['is_exclusion'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'PROMOTION_ELIGIBILITY_ADDED', 'PROMOTION_ELIGIBILITY', $id, 'Promotion eligibility scope added.');

            return DB::table('promotion_eligibility')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $eligibility], 201);
    }

    public function approvePromotion(string $promotionId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $approver = (string) $request->user()->getAuthIdentifier();
        abort_unless($tenantContext->canAdministerTenant(), 403, 'A tenant administrator must approve a promotion.');

        $promotion = DB::transaction(function () use ($tenantId, $promotionId, $approver): object {
            $promotionSeed = DB::table('promotions')
                ->where('tenant_id', $tenantId)
                ->where('id', $promotionId)
                ->first();

            abort_if($promotionSeed === null, 404);

            DB::select(
                'SELECT pg_advisory_xact_lock(hashtext(?))',
                ['promotion-price-list:'.$tenantId.':'.$promotionSeed->price_list_id],
            );

            $promotion = DB::table('promotions')
                ->where('tenant_id', $tenantId)
                ->where('id', $promotionId)
                ->lockForUpdate()
                ->first();

            abort_if($promotion === null, 404);
            abort_unless($promotion->status === 'DRAFT', 422, 'Only a draft promotion can be approved.');
            abort_unless($promotion->created_by !== $approver, 403, 'The promotion author cannot approve their own promotion.');

            $priceList = DB::table('price_lists')
                ->where('tenant_id', $tenantId)
                ->where('id', $promotion->price_list_id)
                ->first();

            abort_unless($priceList !== null && $priceList->status === 'PUBLISHED', 422, 'The linked price list is no longer published.');

            $eligible = DB::table('promotion_eligibility')
                ->where('tenant_id', $tenantId)
                ->where('promotion_id', $promotionId)
                ->where('is_exclusion', false)
                ->exists();

            abort_unless($eligible, 422, 'Promotion approval requires at least one explicit inclusion scope.');

            $overlap = DB::table('promotions as p')
                ->join('promotion_eligibility as existing_scope', function ($join): void {
                    $join->on('existing_scope.tenant_id', '=', 'p.tenant_id')
                        ->on('existing_scope.promotion_id', '=', 'p.id');
                })
                ->join('promotion_eligibility as candidate_scope', function ($join) use ($tenantId, $promotionId): void {
                    $join->on('candidate_scope.tenant_id', '=', 'existing_scope.tenant_id')
                        ->on('candidate_scope.scope_type', '=', 'existing_scope.scope_type')
                        ->on('candidate_scope.scope_reference', '=', 'existing_scope.scope_reference')
                        ->where('candidate_scope.tenant_id', '=', $tenantId)
                        ->where('candidate_scope.promotion_id', '=', $promotionId)
                        ->where('candidate_scope.is_exclusion', '=', false);
                })
                ->where('p.tenant_id', $tenantId)
                ->where('p.price_list_id', $promotion->price_list_id)
                ->where('p.status', 'APPROVED')
                ->where('p.id', '<>', $promotionId)
                ->where('existing_scope.is_exclusion', false)
                ->where('p.starts_at', '<=', $promotion->ends_at)
                ->where('p.ends_at', '>=', $promotion->starts_at)
                ->select('p.stack_policy')
                ->first();

            // Overlap is permitted only when both the existing and proposed
            // promotion policies explicitly permit stacking.
            if (
                $overlap !== null
                && ($promotion->stack_policy === 'NO_STACK' || $overlap->stack_policy === 'NO_STACK')
            ) {
                abort(422, 'An approved promotion overlaps an existing no-stack promotion for part of this eligibility scope.');
            }

            DB::table('merchandising_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'object_type' => 'PROMOTION',
                'object_id' => $promotionId,
                'object_version' => 1,
                'decision' => 'APPROVE_PROMOTION',
                'reason' => 'Promotion independently approved for its effective period and eligibility scope.',
                'actions' => json_encode(['overlap_detected' => $overlap !== null], JSON_THROW_ON_ERROR),
                'decided_by' => $approver,
                'decided_at' => now(),
            ]);

            DB::table('promotions')->where('tenant_id', $tenantId)->where('id', $promotionId)->update([
                'status' => 'APPROVED',
                'approved_by' => $approver,
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $approver, 'PROMOTION_APPROVED', 'PROMOTION', $promotionId, 'Independent approval; price calculation must re-check effective time and channel scope.');

            return DB::table('promotions')->where('tenant_id', $tenantId)->where('id', $promotionId)->first();
        });

        return response()->json(['data' => $promotion]);
    }

    private function decimal(int|float|string $value, int $maximumWholeDigits = 14): string
    {
        $raw = trim((string) $value);

        if (! preg_match('/^-?\\d+(?:\\.\\d{1,4})?$/', $raw)) {
            abort(422, 'A numeric value with at most four decimal places is required.');
        }

        $negative = str_starts_with($raw, '-');
        $unsigned = ltrim($raw, '-');
        [$whole, $fraction] = array_pad(explode('.', $unsigned, 2), 2, '');
        $whole = ltrim($whole, '0');
        $whole = $whole === '' ? '0' : $whole;
        abort_unless(strlen($whole) <= $maximumWholeDigits, 422, 'The amount exceeds the supported decimal precision.');
        $normalized = $whole.'.'.str_pad($fraction, 4, '0');

        return $negative && $normalized !== '0.0000' ? '-'.$normalized : $normalized;
    }

    private function compareDecimals(string $left, string $right): int
    {
        $left = $this->decimal($left);
        $right = $this->decimal($right);
        $leftNegative = str_starts_with($left, '-');
        $rightNegative = str_starts_with($right, '-');

        if ($leftNegative !== $rightNegative) {
            return $leftNegative ? -1 : 1;
        }

        $leftDigits = ltrim(str_replace(['-', '.'], '', $left), '0');
        $rightDigits = ltrim(str_replace(['-', '.'], '', $right), '0');
        $leftDigits = $leftDigits === '' ? '0' : $leftDigits;
        $rightDigits = $rightDigits === '' ? '0' : $rightDigits;

        $comparison = strlen($leftDigits) <=> strlen($rightDigits);
        if ($comparison === 0) {
            $comparison = strcmp($leftDigits, $rightDigits) <=> 0;
        }

        return $leftNegative ? -$comparison : $comparison;
    }

    private function audit(string $tenantId, string $actorId, string $action, string $objectType, string $objectId, string $reason): void
    {
        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'scope' => json_encode(['module' => 'COMMERCIAL_PRICING'], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
