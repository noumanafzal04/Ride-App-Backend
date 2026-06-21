<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    /**
     * Gate an admin route by a permission key (Super Admin bypasses).
     * Usage: ->middleware('permission:inspections.view')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $admin = $request->user();

        if (!$admin) {
            return $this->deny('Unauthenticated.', 401, $request);
        }
        if (!$admin->hasPermission($permission)) {
            return $this->deny('You do not have permission for this action.', 403, $request);
        }

        return $next($request);
    }

    private function deny(string $message, int $status, Request $request): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => null,
            'meta'    => [
                'timestamp' => now()->toISOString(),
                'version'   => $request->attributes->get('version'),
                'trace_id'  => $request->attributes->get('trace_id'),
            ],
        ], $status);
    }
}
