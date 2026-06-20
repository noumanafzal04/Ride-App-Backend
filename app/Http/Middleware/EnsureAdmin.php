<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Allow only team members (users.is_admin = true). Runs after auth:api.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden — admin access required.',
                'errors'  => null,
                'meta'    => [
                    'timestamp' => now()->toISOString(),
                    'version'   => $request->attributes->get('version'),
                    'trace_id'  => $request->attributes->get('trace_id'),
                ],
            ], 403);
        }

        return $next($request);
    }
}
