<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrackerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('tracker.shared_token');

        if ($expectedToken === '') {
            return new JsonResponse([
                'message' => 'Tracker shared token is not configured.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $providedToken = $request->bearerToken();

        if (! hash_equals($expectedToken, (string) $providedToken)) {
            return new JsonResponse([
                'message' => 'Unauthorized tracker request.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
