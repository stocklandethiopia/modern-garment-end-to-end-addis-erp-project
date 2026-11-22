<?php

namespace App\Http\Controllers\ProductDevelopment;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductDevelopmentController extends Controller
{
    public function productBriefs(TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();

        return response()->json([
            'data' => DB::table('product_briefs as pb')
                ->join('range_lines as rl', function ($join): void {
                    $join->on('rl.tenant_id', '=', 'pb.tenant_id')
                        ->on('rl.id', '=', 'pb.range_line_id');
                })
                ->join('range_plans as rp', function ($join): void {
                    $join->on('rp.tenant_id', '=', 'pb.tenant_id')
                        ->on('rp.id', '=', 'pb.range_plan_id');
                })
                ->join('collections as c', function ($join): void {
                    $join->on('c.tenant_id', '=', 'pb.tenant_id')
                        ->on('c.id', '=', 'pb.collection_id');
                })
                ->where('pb.tenant_id', $tenantId)
                ->orderByDesc('pb.updated_at')
                ->paginate(50, [
                    'pb.*',
                    'rl.line_code',
                    'rl.category_code',
                    'rp.code as range_plan_code',
                    'c.code as collection_code',
                    'c.name as collection_name',
                ]),
        ]);
    }

    public function showProductBrief(string $productBriefId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $brief = DB::table('product_briefs')
            ->where('tenant_id', $tenantId)
            ->where('id', $productBriefId)
            ->first();

        abort_if($brief === null, 404);

        $versions = DB::table('product_brief_versions')
            ->where('tenant_id', $tenantId)
            ->where('product_brief_id', $productBriefId)
            ->orderByDesc('version_no')
            ->get()
            ->map(function (object $version): object {
                $version->snapshot = is_string($version->snapshot)
                    ? json_decode($version->snapshot, true, 512, JSON_THROW_ON_ERROR)
                    : $version->snapshot;

                return $version;
            });

        $decisions = DB::table('product_brief_decisions')
            ->where('tenant_id', $tenantId)
            ->where('product_brief_id', $productBriefId)
            ->orderByDesc('decided_at')
            ->get();

        $styles = DB::table('styles')
            ->where('tenant_id', $tenantId)
            ->where('product_brief_id', $productBriefId)
            ->orderBy('style_code')
            ->get();

        return response()->json([
            'data' => $brief,
            'versions' => $versions,
            'decisions' => $decisions,
            'styles' => $styles,
        ]);
    }

    public function createProductBrief(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'range_line_id' => [
                'required',
                'uuid',
                Rule::exists('range_lines', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'initiative_id' => [
                'nullable',
                'uuid',
                Rule::exists('initiatives', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'target_consumer' => ['required', 'string', 'max:200'],
            'product_need' => ['required', 'string', 'min:10', 'max:10000'],
            'intended_use' => ['required', 'string', 'max:500'],
            'target_price' => ['nullable', 'numeric', 'min:0', 'decimal:0,4'],
            'target_cost' => ['nullable', 'numeric', 'min:0', 'decimal:0,4'],
            'target_currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'quality_tier' => ['nullable', 'string', 'max:80'],
            'delivery_need_by' => ['nullable', 'date'],
            'sustainability_intent' => ['nullable', 'array', 'max:30'],
            'sustainability_intent.*' => ['string', 'max:300'],
            'change_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $source = DB::table('range_lines as rl')
            ->join('range_plans as rp', function ($join): void {
                $join->on('rp.tenant_id', '=', 'rl.tenant_id')
                    ->on('rp.id', '=', 'rl.range_plan_id');
            })
            ->join('collections as c', function ($join): void {
                $join->on('c.tenant_id', '=', 'rp.tenant_id')
                    ->on('c.id', '=', 'rp.collection_id');
            })
            ->join('brands as b', function ($join): void {
                $join->on('b.tenant_id', '=', 'c.tenant_id')
                    ->on('b.id', '=', 'c.brand_id');
            })
            ->where('rl.tenant_id', $tenantId)
            ->where('rl.id', $data['range_line_id'])
            ->first([
                'rl.id as range_line_id',
                'rl.range_plan_id',
                'rp.status as range_plan_status',
                'rp.collection_version',
                'c.id as collection_id',
                'c.current_version as collection_current_version',
                'c.status as collection_status',
                'b.status as brand_status',
                'b.current_strategy_version',
            ]);

        abort_if($source === null, 404);
        abort_unless(
            $source->range_plan_status === 'FROZEN'
            && $source->collection_status === 'APPROVED'
            && (int) $source->collection_current_version === (int) $source->collection_version
            && $source->brand_status === 'ACTIVE'
            && (int) $source->current_strategy_version > 0,
            422,
            'A product brief requires a frozen range line from the current approved collection and an active brand strategy.',
        );

        if (! empty($data['initiative_id'])) {
            $initiative = DB::table('initiatives')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['initiative_id'])
                ->first(['status']);

            abort_unless($initiative !== null && $initiative->status === 'APPROVED', 422, 'An attached initiative must be approved.');
        }

        abort_if(
            DB::table('product_briefs')
                ->where('tenant_id', $tenantId)
                ->where('range_line_id', $data['range_line_id'])
                ->exists(),
            422,
            'This range line already has a product brief. Create a new version on the existing brief.',
        );

        $briefId = (string) Str::uuid();
        $versionId = (string) Str::uuid();
        $now = now();
        $snapshot = [
            'initiative_id' => $data['initiative_id'] ?? null,
            'collection_id' => $source->collection_id,
            'range_plan_id' => $source->range_plan_id,
            'range_line_id' => $source->range_line_id,
            'target_consumer' => trim($data['target_consumer']),
            'product_need' => trim($data['product_need']),
            'intended_use' => trim($data['intended_use']),
            'target_price' => $data['target_price'] ?? null,
            'target_cost' => $data['target_cost'] ?? null,
            'target_currency' => strtoupper($data['target_currency']),
            'quality_tier' => $data['quality_tier'] ?? null,
            'delivery_need_by' => $data['delivery_need_by'] ?? null,
            'sustainability_intent' => $data['sustainability_intent'] ?? [],
        ];

        DB::transaction(function () use ($tenantId, $actorId, $briefId, $versionId, $source, $snapshot, $data, $now): void {
            DB::table('product_briefs')->insert([
                'id' => $briefId,
                'tenant_id' => $tenantId,
                'initiative_id' => $snapshot['initiative_id'],
                'collection_id' => $source->collection_id,
                'range_plan_id' => $source->range_plan_id,
                'range_line_id' => $source->range_line_id,
                'target_consumer' => $snapshot['target_consumer'],
                'product_need' => $snapshot['product_need'],
                'intended_use' => $snapshot['intended_use'],
                'target_price' => $snapshot['target_price'],
                'target_cost' => $snapshot['target_cost'],
                'target_currency' => $snapshot['target_currency'],
                'quality_tier' => $snapshot['quality_tier'],
                'delivery_need_by' => $snapshot['delivery_need_by'],
                'sustainability_intent' => json_encode($snapshot['sustainability_intent'], JSON_THROW_ON_ERROR),
                'status' => 'DRAFT',
                'current_version' => 1,
                'accepted_version' => null,
                'owner_user_id' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('product_brief_versions')->insert([
                'id' => $versionId,
                'tenant_id' => $tenantId,
                'product_brief_id' => $briefId,
                'version_no' => 1,
                'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'change_reason' => trim($data['change_reason']),
                'created_by' => $actorId,
                'created_at' => $now,
            ]);

            $this->writeAudit($tenantId, $actorId, 'PRODUCT_BRIEF_CREATED', 'PRODUCT_BRIEF', $briefId, 'Initial product brief version created.');
        });

        return response()->json([
            'data' => DB::table('product_briefs')->where('tenant_id', $tenantId)->where('id', $briefId)->first(),
            'version' => 1,
        ], 201);
    }

    public function createProductBriefVersion(string $productBriefId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'target_consumer' => ['required', 'string', 'max:200'],
            'product_need' => ['required', 'string', 'min:10', 'max:10000'],
            'intended_use' => ['required', 'string', 'max:500'],
            'target_price' => ['nullable', 'numeric', 'min:0', 'decimal:0,4'],
            'target_cost' => ['nullable', 'numeric', 'min:0', 'decimal:0,4'],
            'target_currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'quality_tier' => ['nullable', 'string', 'max:80'],
            'delivery_need_by' => ['nullable', 'date'],
            'sustainability_intent' => ['nullable', 'array', 'max:30'],
            'sustainability_intent.*' => ['string', 'max:300'],
            'change_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $result = DB::transaction(function () use ($productBriefId, $tenantId, $actorId, $data, $tenantContext): object {
            $brief = DB::table('product_briefs')
                ->where('tenant_id', $tenantId)
                ->where('id', $productBriefId)
                ->lockForUpdate()
                ->first();

            abort_if($brief === null, 404);
            abort_unless($brief->owner_user_id === $actorId || $tenantContext->canAdministerTenant(), 403);
            abort_unless($brief->status !== 'SUBMITTED_FOR_REVIEW', 422, 'A submitted product brief cannot be revised until its review is complete.');

            $nextVersion = (int) $brief->current_version + 1;
            $snapshot = [
                'initiative_id' => $brief->initiative_id,
                'collection_id' => $brief->collection_id,
                'range_plan_id' => $brief->range_plan_id,
                'range_line_id' => $brief->range_line_id,
                'target_consumer' => trim($data['target_consumer']),
                'product_need' => trim($data['product_need']),
                'intended_use' => trim($data['intended_use']),
                'target_price' => $data['target_price'] ?? null,
                'target_cost' => $data['target_cost'] ?? null,
                'target_currency' => strtoupper($data['target_currency']),
                'quality_tier' => $data['quality_tier'] ?? null,
                'delivery_need_by' => $data['delivery_need_by'] ?? null,
                'sustainability_intent' => $data['sustainability_intent'] ?? [],
            ];

            DB::table('product_brief_versions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'product_brief_id' => $productBriefId,
                'version_no' => $nextVersion,
                'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'change_reason' => trim($data['change_reason']),
                'created_by' => $actorId,
                'created_at' => now(),
            ]);

            DB::table('product_briefs')->where('tenant_id', $tenantId)->where('id', $productBriefId)->update([
                'target_consumer' => $snapshot['target_consumer'],
                'product_need' => $snapshot['product_need'],
                'intended_use' => $snapshot['intended_use'],
                'target_price' => $snapshot['target_price'],
                'target_cost' => $snapshot['target_cost'],
                'target_currency' => $snapshot['target_currency'],
                'quality_tier' => $snapshot['quality_tier'],
                'delivery_need_by' => $snapshot['delivery_need_by'],
                'sustainability_intent' => json_encode($snapshot['sustainability_intent'], JSON_THROW_ON_ERROR),
                'current_version' => $nextVersion,
                'status' => 'DRAFT',
                'updated_at' => now(),
            ]);

            $this->writeAudit($tenantId, $actorId, 'PRODUCT_BRIEF_VERSION_CREATED', 'PRODUCT_BRIEF', $productBriefId, trim($data['change_reason']));

            return DB::table('product_briefs')->where('tenant_id', $tenantId)->where('id', $productBriefId)->first();
        });

        return response()->json(['data' => $result]);
    }

    public function submitProductBrief(string $productBriefId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();

        $brief = DB::transaction(function () use ($productBriefId, $tenantId, $actorId, $tenantContext): object {
            $brief = DB::table('product_briefs')
                ->where('tenant_id', $tenantId)
                ->where('id', $productBriefId)
                ->lockForUpdate()
                ->first();

            abort_if($brief === null, 404);
            abort_unless($brief->owner_user_id === $actorId || $tenantContext->canAdministerTenant(), 403);
            abort_unless($brief->status === 'DRAFT', 422, 'Only a draft product brief can be submitted for review.');

            DB::table('product_briefs')->where('tenant_id', $tenantId)->where('id', $productBriefId)->update([
                'status' => 'SUBMITTED_FOR_REVIEW',
                'updated_at' => now(),
            ]);

            $this->writeAudit($tenantId, $actorId, 'PRODUCT_BRIEF_SUBMITTED', 'PRODUCT_BRIEF', $productBriefId, 'Product brief submitted for independent acceptance review.');

            return DB::table('product_briefs')->where('tenant_id', $tenantId)->where('id', $productBriefId)->first();
        });

        return response()->json(['data' => $brief]);
    }

    public function decideProductBrief(string $productBriefId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or admin approval is required.');

        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'decision' => ['required', Rule::in(['ACCEPT', 'REJECT'])],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'actions' => ['nullable', 'array', 'max:20'],
            'actions.*' => ['string', 'max:500'],
        ]);

        $brief = DB::transaction(function () use ($productBriefId, $tenantId, $actorId, $data): object {
            $brief = DB::table('product_briefs')
                ->where('tenant_id', $tenantId)
                ->where('id', $productBriefId)
                ->lockForUpdate()
                ->first();

            abort_if($brief === null, 404);
            abort_unless($actorId !== $brief->owner_user_id, 403, 'The product brief creator cannot accept their own brief.');
            abort_unless($brief->status === 'SUBMITTED_FOR_REVIEW', 422, 'Only a submitted product brief can be accepted or rejected.');

            DB::table('product_brief_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'product_brief_id' => $productBriefId,
                'version_no' => $brief->current_version,
                'decision' => $data['decision'],
                'reason' => trim($data['reason']),
                'actions' => json_encode($data['actions'] ?? [], JSON_THROW_ON_ERROR),
                'decided_by' => $actorId,
                'decided_at' => now(),
            ]);

            $nextStatus = $data['decision'] === 'ACCEPT' ? 'ACCEPTED' : 'REJECTED';
            DB::table('product_briefs')->where('tenant_id', $tenantId)->where('id', $productBriefId)->update([
                'status' => $nextStatus,
                'accepted_version' => $nextStatus === 'ACCEPTED' ? $brief->current_version : $brief->accepted_version,
                'updated_at' => now(),
            ]);

            $this->writeAudit($tenantId, $actorId, 'PRODUCT_BRIEF_DECISION_RECORDED', 'PRODUCT_BRIEF', $productBriefId, trim($data['reason']));

            return DB::table('product_briefs')->where('tenant_id', $tenantId)->where('id', $productBriefId)->first();
        });

        return response()->json(['data' => $brief]);
    }

    public function styles(TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();

        return response()->json([
            'data' => DB::table('styles as s')
                ->join('product_briefs as pb', function ($join): void {
                    $join->on('pb.tenant_id', '=', 's.tenant_id')
                        ->on('pb.id', '=', 's.product_brief_id');
                })
                ->leftJoin('style_revisions as sr', function ($join): void {
                    $join->on('sr.tenant_id', '=', 's.tenant_id')
                        ->on('sr.style_id', '=', 's.id')
                        ->whereRaw('sr.revision_no = (SELECT MAX(sr2.revision_no) FROM style_revisions AS sr2 WHERE sr2.tenant_id = s.tenant_id AND sr2.style_id = s.id)');
                })
                ->where('s.tenant_id', $tenantId)
                ->orderByDesc('s.updated_at')
                ->paginate(50, [
                    's.*',
                    'pb.target_consumer',
                    'pb.status as product_brief_status',
                    'sr.id as latest_revision_id',
                    'sr.revision_no as latest_revision_no',
                    'sr.status as latest_revision_status',
                    'sr.change_summary as latest_change_summary',
                ]),
        ]);
    }

    public function showStyle(string $styleId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $style = DB::table('styles as s')
            ->join('product_briefs as pb', function ($join): void {
                $join->on('pb.tenant_id', '=', 's.tenant_id')
                    ->on('pb.id', '=', 's.product_brief_id');
            })
            ->where('s.tenant_id', $tenantId)
            ->where('s.id', $styleId)
            ->first(['s.*', 'pb.status as product_brief_status']);

        abort_if($style === null, 404);

        $revisions = DB::table('style_revisions')
            ->where('tenant_id', $tenantId)
            ->where('style_id', $styleId)
            ->orderByDesc('revision_no')
            ->get();

        foreach ($revisions as $revision) {
            foreach (['material_direction', 'colour_direction', 'target_size_range'] as $field) {
                $revision->{$field} = is_string($revision->{$field})
                    ? json_decode($revision->{$field}, true, 512, JSON_THROW_ON_ERROR)
                    : $revision->{$field};
            }
        }

        return response()->json(['data' => $style, 'revisions' => $revisions]);
    }

    public function createStyle(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'product_brief_id' => [
                'required',
                'uuid',
                Rule::exists('product_briefs', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'style_code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/',
                Rule::unique('styles', 'style_code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'name' => ['required', 'string', 'max:180'],
            'category_code' => ['required', 'string', 'max:80'],
            'design_direction' => ['required', 'string', 'min:10', 'max:10000'],
            'silhouette' => ['nullable', 'string', 'max:180'],
            'fit_intent' => ['required', 'string', 'min:5', 'max:5000'],
            'construction_intent' => ['required', 'string', 'min:5', 'max:5000'],
            'material_direction' => ['nullable', 'array'],
            'colour_direction' => ['nullable', 'array'],
            'target_size_range' => ['nullable', 'array'],
            'change_summary' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $styleId = (string) Str::uuid();
        $revisionId = (string) Str::uuid();
        $now = now();

        DB::transaction(function () use ($tenantId, $actorId, $data, $styleId, $revisionId, $now): void {
            $brief = DB::table('product_briefs')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['product_brief_id'])
                ->lockForUpdate()
                ->first();

            abort_if($brief === null, 404);
            abort_unless(
                $brief->status === 'ACCEPTED' && (int) $brief->accepted_version === (int) $brief->current_version,
                422,
                'A style can only be created from the currently accepted product brief version.',
            );

            DB::table('styles')->insert([
                'id' => $styleId,
                'tenant_id' => $tenantId,
                'product_brief_id' => $data['product_brief_id'],
                'style_code' => strtoupper($data['style_code']),
                'name' => trim($data['name']),
                'category_code' => strtoupper($data['category_code']),
                'status' => 'DESIGN_IN_PROGRESS',
                'current_revision' => 1,
                'owner_user_id' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('style_revisions')->insert([
                'id' => $revisionId,
                'tenant_id' => $tenantId,
                'style_id' => $styleId,
                'revision_no' => 1,
                'design_direction' => trim($data['design_direction']),
                'silhouette' => $data['silhouette'] ?? null,
                'fit_intent' => trim($data['fit_intent']),
                'construction_intent' => trim($data['construction_intent']),
                'material_direction' => json_encode($data['material_direction'] ?? [], JSON_THROW_ON_ERROR),
                'colour_direction' => json_encode($data['colour_direction'] ?? [], JSON_THROW_ON_ERROR),
                'target_size_range' => json_encode($data['target_size_range'] ?? [], JSON_THROW_ON_ERROR),
                'status' => 'DESIGN_IN_PROGRESS',
                'change_summary' => trim($data['change_summary']),
                'created_by' => $actorId,
                'created_at' => $now,
            ]);

            $this->writeAudit($tenantId, $actorId, 'STYLE_CREATED', 'STYLE', $styleId, trim($data['change_summary']));
        });

        return response()->json([
            'data' => DB::table('styles')->where('tenant_id', $tenantId)->where('id', $styleId)->first(),
            'revision' => DB::table('style_revisions')->where('tenant_id', $tenantId)->where('id', $revisionId)->first(),
        ], 201);
    }

    public function createStyleRevision(string $styleId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'design_direction' => ['required', 'string', 'min:10', 'max:10000'],
            'silhouette' => ['nullable', 'string', 'max:180'],
            'fit_intent' => ['required', 'string', 'min:5', 'max:5000'],
            'construction_intent' => ['required', 'string', 'min:5', 'max:5000'],
            'material_direction' => ['nullable', 'array'],
            'colour_direction' => ['nullable', 'array'],
            'target_size_range' => ['nullable', 'array'],
            'change_summary' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $result = DB::transaction(function () use ($styleId, $tenantId, $actorId, $data, $tenantContext): object {
            $style = DB::table('styles')
                ->where('tenant_id', $tenantId)
                ->where('id', $styleId)
                ->lockForUpdate()
                ->first();

            abort_if($style === null, 404);
            abort_unless($style->owner_user_id === $actorId || $tenantContext->canAdministerTenant(), 403);
            abort_unless($style->status === 'DESIGN_IN_PROGRESS', 422, 'Only a style in design development may be revised in this stage.');

            $revisionNo = (int) $style->current_revision + 1;
            $revisionId = (string) Str::uuid();

            DB::table('style_revisions')->insert([
                'id' => $revisionId,
                'tenant_id' => $tenantId,
                'style_id' => $styleId,
                'revision_no' => $revisionNo,
                'design_direction' => trim($data['design_direction']),
                'silhouette' => $data['silhouette'] ?? null,
                'fit_intent' => trim($data['fit_intent']),
                'construction_intent' => trim($data['construction_intent']),
                'material_direction' => json_encode($data['material_direction'] ?? [], JSON_THROW_ON_ERROR),
                'colour_direction' => json_encode($data['colour_direction'] ?? [], JSON_THROW_ON_ERROR),
                'target_size_range' => json_encode($data['target_size_range'] ?? [], JSON_THROW_ON_ERROR),
                'status' => 'DESIGN_IN_PROGRESS',
                'change_summary' => trim($data['change_summary']),
                'created_by' => $actorId,
                'created_at' => now(),
            ]);

            DB::table('styles')->where('tenant_id', $tenantId)->where('id', $styleId)->update([
                'current_revision' => $revisionNo,
                'updated_at' => now(),
            ]);

            $this->writeAudit($tenantId, $actorId, 'STYLE_REVISION_CREATED', 'STYLE', $styleId, trim($data['change_summary']));

            return DB::table('styles')->where('tenant_id', $tenantId)->where('id', $styleId)->first();
        });

        return response()->json(['data' => $result]);
    }

    private function writeAudit(
        string $tenantId,
        string $actorId,
        string $action,
        string $objectType,
        string $objectId,
        string $reason,
    ): void {
        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'scope' => json_encode(['module' => 'PRODUCT_DEVELOPMENT'], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
