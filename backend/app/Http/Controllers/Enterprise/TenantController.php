<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->getAuthIdentifier();

        $memberships = DB::transaction(function () use ($userId) {
            DB::select("select set_config('app.current_user_id', ?, true)", [$userId]);

            return DB::table('tenant_memberships')
                ->join('tenants', 'tenants.id', '=', 'tenant_memberships.tenant_id')
                ->where('tenant_memberships.user_id', $userId)
                ->where('tenant_memberships.status', 'ACTIVE')
                ->where('tenants.status', 'ACTIVE')
                ->orderBy('tenants.name')
                ->get([
                    'tenants.id',
                    'tenants.code',
                    'tenants.name',
                    'tenants.locale',
                    'tenants.timezone',
                    'tenant_memberships.membership_role',
                ]);
        });

        return response()->json(['data' => $memberships]);
    }
}
