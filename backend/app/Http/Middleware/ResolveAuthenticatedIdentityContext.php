<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Sets the authenticated global user identity for invitation acceptance.
 * It deliberately does not set a tenant: an invitee is not a member yet.
 */
class ResolveAuthenticatedIdentityContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Authentication is required.'], 401);
        }

        $connection = DB::connection('pgsql');
        $connection->beginTransaction();

        try {
            $connection->select(
                "select set_config('app.current_user_id', ?, true)",
                [(string) $user->getAuthIdentifier()],
            );
            $response = $next($request);
            $connection->commit();

            return $response;
        } catch (Throwable $exception) {
            if ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }
}
