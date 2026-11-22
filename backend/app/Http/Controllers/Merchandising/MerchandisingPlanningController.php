<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MerchandisingPlanningController extends Controller
{
    public function brands(TenantContext $tenantContext): JsonResponse
    {
        return response()->json([
            'data' => DB::table('brands as b')
                ->leftJoin('brand_strategy_versions as v', function ($join): void {
                    $join->on('v.tenant_id', '=', 'b.tenant_id')
                        ->on('v.brand_id', '=', 'b.id')
                        ->whereRaw('v.version_no = (SELECT MAX(v2.version_no) FROM brand_strategy_versions AS v2 WHERE v2.tenant_id = b.tenant_id AND v2.brand_id = b.id)');
                })
                ->leftJoin('merchandising_decisions as d', function ($join): void {
                    $join->on('d.tenant_id', '=', 'v.tenant_id')
                        ->on('d.object_id', '=', 'v.id')
                        ->where('d.object_type', '=', 'BRAND_STRATEGY')
                        ->where('d.decision', '=', 'APPROVE');
                })
                ->where('b.tenant_id', $tenantContext->tenantId())
                ->orderBy('b.name')
                ->select([
                    'b.*',
                    'v.id as latest_strategy_version_id',
                    'v.version_no as latest_strategy_version_no',
                    'v.created_by as latest_strategy_created_by',
                    DB::raw("CASE WHEN b.current_strategy_version > 0 THEN 'APPROVED' ELSE COALESCE(v.status, 'NOT_CREATED') END AS current_strategy_status"),
                    DB::raw("CASE WHEN d.id IS NOT NULL THEN 'APPROVED' ELSE COALESCE(v.status, 'NOT_CREATED') END AS latest_strategy_status"),
                ])
                ->paginate(50),
        ]);
    }

    public function createBrand(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/',
                Rule::unique('brands', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'name' => ['required', 'string', 'max:180'],
            'owner_entity_id' => [
                'nullable',
                'uuid',
                Rule::exists('legal_entities', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
        ]);

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();
        $now = now();

        DB::transaction(function () use ($tenantId, $data, $id, $actor, $now): void {
            DB::table('brands')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'owner_entity_id' => $data['owner_entity_id'] ?? null,
                'code' => strtoupper($data['code']),
                'name' => trim($data['name']),
                'status' => 'DRAFT',
                'current_strategy_version' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->audit($tenantId, $actor, 'BRAND_CREATED', 'BRAND', $id, 'Brand master created in draft state.');
        });

        return response()->json([
            'data' => DB::table('brands')->where('tenant_id', $tenantId)->where('id', $id)->first(),
        ], 201);
    }

    public function createBrandStrategy(string $brandId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $brand = DB::table('brands')->where('tenant_id', $tenantId)->where('id', $brandId)->first();
        abort_if($brand === null, 404);

        $data = $request->validate([
            'positioning' => ['required', 'string', 'min:10', 'max:500'],
            'target_consumer' => ['required', 'string', 'min:2', 'max:200'],
            'value_proposition' => ['required', 'string', 'min:10', 'max:10000'],
            'quality_tier' => ['nullable', 'string', 'max:100'],
            'approved_categories' => ['required', 'array', 'min:1', 'max:50'],
            'approved_categories.*' => ['string', 'max:80'],
            'price_architecture' => ['required', 'array'],
            'approved_channels' => ['required', 'array', 'min:1', 'max:30'],
            'approved_channels.*' => ['string', 'max:60'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'change_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $created = DB::transaction(function () use ($tenantId, $brandId, $data, $id, $actor): array {
            $brand = DB::table('brands')
                ->where('tenant_id', $tenantId)
                ->where('id', $brandId)
                ->lockForUpdate()
                ->first();

            abort_if($brand === null, 404);

            $version = (int) DB::table('brand_strategy_versions')
                ->where('tenant_id', $tenantId)
                ->where('brand_id', $brandId)
                ->max('version_no') + 1;

            DB::table('brand_strategy_versions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'brand_id' => $brandId,
                'version_no' => $version,
                'positioning' => trim($data['positioning']),
                'target_consumer' => trim($data['target_consumer']),
                'value_proposition' => trim($data['value_proposition']),
                'quality_tier' => $data['quality_tier'] ?? null,
                'approved_categories' => json_encode(array_values($data['approved_categories']), JSON_THROW_ON_ERROR),
                'price_architecture' => json_encode($data['price_architecture'], JSON_THROW_ON_ERROR),
                'approved_channels' => json_encode(array_values($data['approved_channels']), JSON_THROW_ON_ERROR),
                'effective_from' => $data['effective_from'] ?? null,
                'effective_to' => $data['effective_to'] ?? null,
                'status' => 'PENDING_APPROVAL',
                'change_reason' => trim($data['change_reason']),
                'created_by' => $actor,
                'created_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'BRAND_STRATEGY_VERSION_CREATED', 'BRAND_STRATEGY', $id, $data['change_reason']);

            return ['id' => $id, 'brand_id' => $brandId, 'version_no' => $version, 'status' => 'PENDING_APPROVAL'];
        });

        return response()->json(['data' => $created], 201);
    }

    public function approveBrandStrategy(string $brandId, string $versionId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $approver = (string) $request->user()->getAuthIdentifier();
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator approval is required.');

        $result = DB::transaction(function () use ($tenantId, $brandId, $versionId, $approver): object {
            $brand = DB::table('brands')
                ->where('tenant_id', $tenantId)
                ->where('id', $brandId)
                ->lockForUpdate()
                ->first();

            abort_if($brand === null, 404);

            $version = DB::table('brand_strategy_versions')
                ->where('tenant_id', $tenantId)
                ->where('brand_id', $brandId)
                ->where('id', $versionId)
                ->first();

            abort_if($version === null, 404);
            abort_unless($version->status === 'PENDING_APPROVAL', 422, 'Only a pending strategy version can be approved.');
            abort_unless($version->created_by !== $approver, 403, 'The author cannot approve their own brand strategy.');

            $alreadyApproved = DB::table('merchandising_decisions')
                ->where('tenant_id', $tenantId)
                ->where('object_type', 'BRAND_STRATEGY')
                ->where('object_id', $versionId)
                ->where('decision', 'APPROVE')
                ->exists();

            abort_unless(! $alreadyApproved, 422, 'This brand strategy version has already been approved.');

            DB::table('merchandising_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'object_type' => 'BRAND_STRATEGY',
                'object_id' => $versionId,
                'object_version' => $version->version_no,
                'decision' => 'APPROVE',
                'reason' => 'Brand strategy independently approved.',
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $approver,
                'decided_at' => now(),
            ]);

            // The strategy version remains immutable. Approval is represented by
            // the append-only merchandising_decisions record above.
            DB::table('brands')->where('tenant_id', $tenantId)->where('id', $brandId)->update([
                'current_strategy_version' => $version->version_no,
                'status' => 'ACTIVE',
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $approver, 'BRAND_STRATEGY_APPROVED', 'BRAND_STRATEGY', $versionId, 'Independent approval.');

            return DB::table('brands')->where('tenant_id', $tenantId)->where('id', $brandId)->first();
        });

        return response()->json(['data' => $result]);
    }

    public function seasons(TenantContext $tenantContext): JsonResponse
    {
        return response()->json([
            'data' => DB::table('seasons')->where('tenant_id', $tenantContext->tenantId())
                ->orderByDesc('design_start')->paginate(50),
        ]);
    }

    public function createSeason(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/',
                Rule::unique('seasons', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantContext->tenantId())),
            ],
            'name' => ['required', 'string', 'max:180'],
            'calendar_type' => ['required', Rule::in(['FASHION', 'SCHOOL', 'UNIFORM', 'CLIMATE', 'HOLIDAY', 'WHOLESALE', 'CUSTOM'])],
            'market_scope' => ['nullable', 'string', 'max:160'],
            'timezone' => ['required', 'timezone'],
            'design_start' => ['nullable', 'date'],
            'buy_start' => ['nullable', 'date'],
            'production_start' => ['nullable', 'date'],
            'production_end' => ['nullable', 'date', 'after_or_equal:production_start'],
            'delivery_start' => ['nullable', 'date'],
            'delivery_end' => ['nullable', 'date', 'after_or_equal:delivery_start'],
            'selling_start' => ['nullable', 'date'],
            'selling_end' => ['nullable', 'date', 'after_or_equal:selling_start'],
        ]);
        $tenantId = $tenantContext->tenantId();
        $code = strtoupper($data['code']);
        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $season = DB::transaction(function () use ($tenantId, $data, $code, $id, $actor): object {
            DB::table('seasons')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                ...$data,
                'code' => $code,
                'status' => 'ACTIVE',
                'created_by' => $actor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'SEASON_CREATED', 'SEASON', $id, 'Season calendar created. Overlapping season windows are permitted.');

            return DB::table('seasons')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $season], 201);
    }

    public function collections(TenantContext $tenantContext): JsonResponse
    {
        return response()->json([
            'data' => DB::table('collections as c')
                ->join('brands as b', fn ($join) => $join->on('b.id', '=', 'c.brand_id')->on('b.tenant_id', '=', 'c.tenant_id'))
                ->join('seasons as s', fn ($join) => $join->on('s.id', '=', 'c.season_id')->on('s.tenant_id', '=', 'c.tenant_id'))
                ->leftJoin('collection_versions as cv', function ($join): void {
                    $join->on('cv.tenant_id', '=', 'c.tenant_id')
                        ->on('cv.collection_id', '=', 'c.id')
                        ->whereRaw('cv.version_no = (SELECT MAX(cv2.version_no) FROM collection_versions AS cv2 WHERE cv2.tenant_id = c.tenant_id AND cv2.collection_id = c.id)');
                })
                ->leftJoin('merchandising_decisions as md', function ($join): void {
                    $join->on('md.tenant_id', '=', 'cv.tenant_id')
                        ->on('md.object_id', '=', 'cv.id')
                        ->where('md.object_type', '=', 'COLLECTION_VERSION')
                        ->where('md.decision', '=', 'APPROVE');
                })
                ->where('c.tenant_id', $tenantContext->tenantId())
                ->orderByDesc('c.created_at')
                ->paginate(50, [
                    'c.*',
                    'b.name as brand_name',
                    's.name as season_name',
                    'cv.id as latest_version_id',
                    'cv.version_no as latest_version_no',
                    'cv.created_by as latest_version_created_by',
                    DB::raw("CASE WHEN md.id IS NOT NULL THEN 'APPROVED' ELSE COALESCE(cv.status, 'NOT_CREATED') END AS latest_version_status"),
                ]),
        ]);
    }

    public function createCollection(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'brand_id' => ['required', 'uuid', Rule::exists('brands', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)->where('status', 'ACTIVE'))],
            'season_id' => ['required', 'uuid', Rule::exists('seasons', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)->where('status', 'ACTIVE'))],
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/',
                Rule::unique('collections', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'name' => ['required', 'string', 'max:180'],
            'concept' => ['nullable', 'string', 'max:500'],
            'target_market' => ['required', 'string', 'max:160'],
        ]);
        $code = strtoupper($data['code']);
        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $collection = DB::transaction(function () use ($tenantId, $data, $code, $id, $actor): object {
            DB::table('collections')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                ...$data,
                'code' => $code,
                'current_version' => 0,
                'status' => 'DRAFT',
                'owner_user_id' => $actor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'COLLECTION_CREATED', 'COLLECTION', $id, 'Collection created in draft state.');

            return DB::table('collections')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $collection], 201);
    }

    public function createCollectionVersion(string $collectionId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'story' => ['required', 'string', 'min:10', 'max:10000'],
            'target_consumer' => ['required', 'string', 'max:180'],
            'category_architecture' => ['required', 'array', 'min:1'],
            'price_margin_envelope' => ['required', 'array'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'budget_currency' => ['nullable', 'string', 'size:3', 'alpha:upper'],
            'delivery_start' => ['nullable', 'date'],
            'delivery_end' => ['nullable', 'date', 'after_or_equal:delivery_start'],
            'change_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);
        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $created = DB::transaction(function () use ($tenantId, $collectionId, $data, $id, $actor): array {
            $collection = DB::table('collections')
                ->where('tenant_id', $tenantId)
                ->where('id', $collectionId)
                ->lockForUpdate()
                ->first();

            abort_if($collection === null, 404);

            $version = (int) DB::table('collection_versions')
                ->where('tenant_id', $tenantId)
                ->where('collection_id', $collectionId)
                ->max('version_no') + 1;

            DB::table('collection_versions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'collection_id' => $collectionId,
                'version_no' => $version,
                ...$data,
                'category_architecture' => json_encode($data['category_architecture'], JSON_THROW_ON_ERROR),
                'price_margin_envelope' => json_encode($data['price_margin_envelope'], JSON_THROW_ON_ERROR),
                'status' => 'PENDING_APPROVAL',
                'created_by' => $actor,
                'created_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'COLLECTION_VERSION_CREATED', 'COLLECTION_VERSION', $id, $data['change_reason']);

            return ['id' => $id, 'collection_id' => $collectionId, 'version_no' => $version, 'status' => 'PENDING_APPROVAL'];
        });

        return response()->json(['data' => $created], 201);
    }

    public function approveCollectionVersion(string $collectionId, string $versionId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $approver = (string) $request->user()->getAuthIdentifier();
        abort_unless($tenantContext->canAdministerTenant(), 403);
        $collection = DB::table('collections')->where('tenant_id', $tenantId)->where('id', $collectionId)->first();
        abort_if($collection === null, 404);

        return DB::transaction(function () use ($tenantId, $collectionId, $versionId, $approver) {
            $collection = DB::table('collections')
                ->where('tenant_id', $tenantId)
                ->where('id', $collectionId)
                ->lockForUpdate()
                ->first();

            abort_if($collection === null, 404);

            $version = DB::table('collection_versions')
                ->where('tenant_id', $tenantId)
                ->where('collection_id', $collectionId)
                ->where('id', $versionId)
                ->first();

            abort_if($version === null, 404);
            abort_unless($version->status === 'PENDING_APPROVAL', 422, 'Collection version is not awaiting approval.');
            abort_unless($version->created_by !== $approver, 403, 'The author cannot approve their own collection version.');

            $alreadyApproved = DB::table('merchandising_decisions')
                ->where('tenant_id', $tenantId)
                ->where('object_type', 'COLLECTION_VERSION')
                ->where('object_id', $versionId)
                ->where('decision', 'APPROVE')
                ->exists();

            abort_unless(! $alreadyApproved, 422, 'This collection version has already been approved.');

            DB::table('merchandising_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'object_type' => 'COLLECTION_VERSION',
                'object_id' => $versionId,
                'object_version' => $version->version_no,
                'decision' => 'APPROVE',
                'reason' => 'Collection version independently approved.',
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $approver,
                'decided_at' => now(),
            ]);
            // The collection version remains immutable. Approval is stored as a
            // separate append-only decision and reflected on the collection master.
            DB::table('collections')->where('tenant_id', $tenantId)->where('id', $collectionId)->update([
                'current_version' => $version->version_no,
                'status' => 'APPROVED',
                'updated_at' => now(),
            ]);
            $this->audit($tenantId, $approver, 'COLLECTION_VERSION_APPROVED', 'COLLECTION_VERSION', $versionId, 'Independent collection version approval.');

            return response()->json(['data' => DB::table('collections')->where('tenant_id', $tenantId)->where('id', $collectionId)->first()]);
        });
    }

    public function rangePlans(TenantContext $tenantContext): JsonResponse
    {
        return response()->json([
            'data' => DB::table('range_plans')
                ->where('tenant_id', $tenantContext->tenantId())
                ->orderByDesc('created_at')
                ->paginate(50),
        ]);
    }

    public function rangeLines(string $rangePlanId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $plan = DB::table('range_plans')
            ->where('tenant_id', $tenantId)
            ->where('id', $rangePlanId)
            ->first();

        abort_if($plan === null, 404);

        return response()->json([
            'data' => DB::table('range_lines')
                ->where('tenant_id', $tenantId)
                ->where('range_plan_id', $rangePlanId)
                ->orderBy('line_code')
                ->paginate(100),
        ]);
    }

    public function assortmentPlans(TenantContext $tenantContext): JsonResponse
    {
        return response()->json([
            'data' => DB::table('assortment_plans as ap')
                ->join('range_plans as rp', function ($join): void {
                    $join->on('rp.tenant_id', '=', 'ap.tenant_id')
                        ->on('rp.id', '=', 'ap.range_plan_id');
                })
                ->where('ap.tenant_id', $tenantContext->tenantId())
                ->orderByDesc('ap.created_at')
                ->paginate(50, [
                    'ap.*',
                    'rp.code as range_plan_code',
                    'rp.status as range_plan_status',
                ]),
        ]);
    }

    public function assortmentLines(string $assortmentPlanId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $plan = DB::table('assortment_plans')
            ->where('tenant_id', $tenantId)
            ->where('id', $assortmentPlanId)
            ->first();

        abort_if($plan === null, 404);

        return response()->json([
            'data' => DB::table('assortment_lines as al')
                ->join('range_lines as rl', function ($join): void {
                    $join->on('rl.tenant_id', '=', 'al.tenant_id')
                        ->on('rl.id', '=', 'al.range_line_id');
                })
                ->where('al.tenant_id', $tenantId)
                ->where('al.assortment_plan_id', $assortmentPlanId)
                ->orderBy('rl.line_code')
                ->paginate(100, [
                    'al.*',
                    'rl.line_code',
                    'rl.category_code',
                    'rl.lifecycle_type',
                ]),
        ]);
    }

    public function createRangePlan(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'collection_id' => ['required', 'uuid', Rule::exists('collections', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)->where('status', 'APPROVED'))],
            'collection_version' => ['required', 'integer', 'min:1'],
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/',
                Rule::unique('range_plans', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'plan_start' => ['required', 'date'],
            'plan_end' => ['required', 'date', 'after_or_equal:plan_start'],
        ]);
        $code = strtoupper($data['code']);
        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $rangePlan = DB::transaction(function () use ($tenantId, $data, $code, $id, $actor): object {
            $collection = DB::table('collections')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['collection_id'])
                ->lockForUpdate()
                ->first();

            abort_if($collection === null, 404);
            abort_unless($collection->status === 'APPROVED', 422, 'The collection must be approved before range planning.');

            $version = DB::table('collection_versions')
                ->where('tenant_id', $tenantId)
                ->where('collection_id', $data['collection_id'])
                ->where('version_no', $data['collection_version'])
                ->first();

            abort_if($version === null, 422, 'The selected collection version does not exist.');

            $versionApproved = DB::table('merchandising_decisions')
                ->where('tenant_id', $tenantId)
                ->where('object_type', 'COLLECTION_VERSION')
                ->where('object_id', $version->id)
                ->where('decision', 'APPROVE')
                ->exists();

            abort_unless($versionApproved, 422, 'The selected collection version must be approved.');

            DB::table('range_plans')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                ...$data,
                'code' => $code,
                'status' => 'DRAFT',
                'owner_user_id' => $actor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'RANGE_PLAN_CREATED', 'RANGE_PLAN', $id, 'Range plan created against an approved collection version.');

            return DB::table('range_plans')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $rangePlan], 201);
    }

    public function createRangeLine(string $rangePlanId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'line_code' => [
                'required',
                'string',
                'max:80',
                Rule::unique('range_lines', 'line_code')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('range_plan_id', $rangePlanId)),
            ],
            'category_code' => ['required', 'string', 'max:80'],
            'target_tier' => ['nullable', 'string', 'max:80'],
            'product_hypothesis' => ['required', 'string', 'min:5', 'max:500'],
            'planned_units' => ['required', 'integer', 'min:0'],
            'target_retail_price' => ['nullable', 'numeric', 'min:0'],
            'target_wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'price_currency' => ['nullable', 'string', 'size:3', 'alpha:upper'],
            'target_margin_percent' => ['nullable', 'numeric', 'between:-100,100'],
            'channel_scope' => ['required', 'array', 'min:1'],
            'size_colour_intent' => ['required', 'array'],
            'lifecycle_type' => ['required', Rule::in(['NEW', 'CARRY_OVER', 'REPLENISHMENT'])],
            'forecast_basis' => ['required', 'string', 'min:5', 'max:500'],
            'launch_date' => ['nullable', 'date'],
        ]);

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $rangeLine = DB::transaction(function () use ($tenantId, $rangePlanId, $data, $id, $actor): object {
            $plan = DB::table('range_plans')
                ->where('tenant_id', $tenantId)
                ->where('id', $rangePlanId)
                ->lockForUpdate()
                ->first();

            abort_if($plan === null, 404);
            abort_unless($plan->status === 'DRAFT', 422, 'Range lines can only be added while a plan is in draft.');

            DB::table('range_lines')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'range_plan_id' => $rangePlanId,
                ...$data,
                'channel_scope' => json_encode($data['channel_scope'], JSON_THROW_ON_ERROR),
                'size_colour_intent' => json_encode($data['size_colour_intent'], JSON_THROW_ON_ERROR),
                'status' => 'DRAFT',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'RANGE_LINE_CREATED', 'RANGE_LINE', $id, 'Draft range line added.');

            return DB::table('range_lines')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $rangeLine], 201);
    }

    public function freezeRangePlan(string $rangePlanId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actor = (string) $request->user()->getAuthIdentifier();
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator freeze approval is required.');

        return DB::transaction(function () use ($tenantId, $rangePlanId, $actor) {
            $plan = DB::table('range_plans')->where('tenant_id', $tenantId)->where('id', $rangePlanId)->lockForUpdate()->first();
            abort_if($plan === null, 404);
            abort_unless($plan->status === 'DRAFT', 422, 'Only a draft range plan can be frozen.');
            abort_unless($plan->owner_user_id !== $actor, 403, 'The range plan owner cannot approve their own freeze.');
            $lines = DB::table('range_lines')->where('tenant_id', $tenantId)->where('range_plan_id', $rangePlanId)->count();
            abort_unless($lines > 0, 422, 'A range plan needs at least one completed product hypothesis before freeze.');
            DB::table('merchandising_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'object_type' => 'RANGE_PLAN',
                'object_id' => $rangePlanId,
                'object_version' => 1,
                'decision' => 'FREEZE',
                'reason' => 'Range plan independently frozen for downstream product development.',
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $actor,
                'decided_at' => now(),
            ]);
            DB::table('range_plans')->where('tenant_id', $tenantId)->where('id', $rangePlanId)->update([
                'status' => 'FROZEN',
                'frozen_at' => now(),
                'frozen_by' => $actor,
                'updated_at' => now(),
            ]);
            $this->audit($tenantId, $actor, 'RANGE_PLAN_FROZEN', 'RANGE_PLAN', $rangePlanId, 'Independent freeze approval.');

            return response()->json(['data' => DB::table('range_plans')->where('tenant_id', $tenantId)->where('id', $rangePlanId)->first()]);
        });
    }

    public function createAssortmentPlan(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'range_plan_id' => ['required', 'uuid', Rule::exists('range_plans', 'id')->where(fn ($query) => $query
                ->where('tenant_id', $tenantId)
                ->where('status', 'FROZEN'))],
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/',
                Rule::unique('assortment_plans', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'channel_code' => ['required', 'string', 'max:60'],
            'market_scope' => ['required', 'string', 'max:160'],
            'account_reference' => ['nullable', 'string', 'max:120'],
            'location_cluster_reference' => ['nullable', 'string', 'max:120'],
        ]);

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $assortmentPlan = DB::transaction(function () use ($tenantId, $data, $id, $actor): object {
            $rangePlan = DB::table('range_plans')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['range_plan_id'])
                ->lockForUpdate()
                ->first();

            abort_if($rangePlan === null, 404);
            abort_unless($rangePlan->status === 'FROZEN', 422, 'The range plan must be frozen before assortment planning.');

            DB::table('assortment_plans')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                ...$data,
                'version_no' => 1,
                'status' => 'DRAFT',
                'owner_user_id' => $actor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'ASSORTMENT_PLAN_CREATED', 'ASSORTMENT_PLAN', $id, 'Channel assortment plan created against a frozen range.');

            return DB::table('assortment_plans')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $assortmentPlan], 201);
    }

    public function createAssortmentLine(string $assortmentPlanId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'range_line_id' => [
                'required',
                'uuid',
                Rule::exists('range_lines', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)),
                Rule::unique('assortment_lines', 'range_line_id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('assortment_plan_id', $assortmentPlanId)),
            ],
            'variant_size_curve' => ['required', 'array'],
            'planned_units' => ['required', 'integer', 'min:0'],
            'launch_date' => ['nullable', 'date'],
            'minimum_depth' => ['nullable', 'integer', 'min:0'],
            'maximum_depth' => ['nullable', 'integer', 'min:0', 'gte:minimum_depth'],
        ]);

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $assortmentLine = DB::transaction(function () use ($tenantId, $assortmentPlanId, $data, $id, $actor): object {
            $plan = DB::table('assortment_plans')
                ->where('tenant_id', $tenantId)
                ->where('id', $assortmentPlanId)
                ->lockForUpdate()
                ->first();

            abort_if($plan === null, 404);
            abort_unless($plan->status === 'DRAFT', 422, 'Only draft assortment plans can be edited.');

            $rangeLine = DB::table('range_lines')
                ->where('tenant_id', $tenantId)
                ->where('range_plan_id', $plan->range_plan_id)
                ->where('id', $data['range_line_id'])
                ->first();

            abort_if($rangeLine === null, 422, 'The selected range line does not belong to the frozen range plan.');

            DB::table('assortment_lines')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'assortment_plan_id' => $assortmentPlanId,
                ...$data,
                'variant_size_curve' => json_encode($data['variant_size_curve'], JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'ASSORTMENT_LINE_CREATED', 'ASSORTMENT_LINE', $id, 'Channel assortment depth added.');

            return DB::table('assortment_lines')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $assortmentLine], 201);
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
            'scope' => json_encode(['module' => 'MERCHANDISING_PLANNING'], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
