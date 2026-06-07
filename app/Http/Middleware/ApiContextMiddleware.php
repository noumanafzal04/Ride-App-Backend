<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $traceId = Str::uuid()->toString();

        $request->attributes->set('version', 'v1');
        $request->attributes->set('trace_id', $traceId);

        $context = [
            'trace_id' => $traceId,
            'user_id'  => auth()->id(),
            'ip'       => $request->ip(),
            'method'   => $request->method(),
            'uri'      => $request->path(),
        ];

        $request->attributes->set('log_context', $context);

        Log::withContext($context);
        
        return $next($request);
    }
}
