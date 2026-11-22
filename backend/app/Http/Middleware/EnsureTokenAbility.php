<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->tokenCan($ability)) {
            return response()->json(['message' => 'The access token does not grant this operation.'], 403);
        }

        return $next($request);
    }
}
