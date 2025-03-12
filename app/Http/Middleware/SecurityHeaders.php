<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY'); // Prevent Clickjacking
        $response->headers->set('X-Content-Type-Options', 'nosniff'); // Prevent MIME sniffing
        $response->headers->set('X-XSS-Protection', '1; mode=block'); // Prevent XSS attacks
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade'); // Control referrer information
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload'); // Enforce HTTPS
        $response->headers->set('Content-Security-Policy', "default-src 'self'"); // Restrict resource loading
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=()'); // Restrict browser features

        return $response;
    }
}
