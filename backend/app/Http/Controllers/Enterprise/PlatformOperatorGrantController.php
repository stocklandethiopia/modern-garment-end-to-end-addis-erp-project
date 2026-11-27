<?php

declare(strict_types=1);

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Services\PlatformAdministrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlatformOperatorGrantController extends Controller
{
    public function index(Request $request, PlatformAdministrationService $platform): JsonResponse
    {
        return response()->json(['data' => $platform->operatorGrants((string) $request->user()->getAuthIdentifier())]);
    }

    public function store(Request $request, PlatformAdministrationService $platform): JsonResponse
    {
        $data = $request->validate([
            'user_email' => ['required', 'email:rfc', 'max:254'],
            'role_code' => ['required', 'string', 'in:PLATFORM_ADMIN,PLATFORM_SUPPORT'],
            'valid_from' => ['required', 'date_format:Y-m-d'],
            'valid_to' => ['required', 'date_format:Y-m-d', 'after_or_equal:valid_from'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        return response()->json([
            'data' => $platform->requestOperatorGrant(
                (string) $request->user()->getAuthIdentifier(),
                mb_strtolower(trim($data['user_email'])),
                $data['role_code'],
                $data['valid_from'],
                $data['valid_to'],
                trim($data['reason']),
            ),
        ], 201);
    }

    public function approve(
        Request $request,
        string $grantId,
        PlatformAdministrationService $platform,
    ): JsonResponse {
        abort_unless(Str::isUuid($grantId), 404);
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        return response()->json([
            'data' => $platform->approveOperatorGrant((string) $request->user()->getAuthIdentifier(), $grantId, trim($data['reason'])),
        ]);
    }

    public function revoke(
        Request $request,
        string $grantId,
        PlatformAdministrationService $platform,
    ): JsonResponse {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        return response()->json([
            'data' => $platform->revokeOperatorGrant((string) $request->user()->getAuthIdentifier(), $grantId, trim($data['reason'])),
        ]);
    }
}
