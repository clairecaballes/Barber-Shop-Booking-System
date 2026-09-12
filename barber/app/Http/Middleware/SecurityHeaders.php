<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Apply OWASP-recommended security headers to every response.
     *
     * The CSP is intentionally permissive about inline scripts/styles because
     * Alpine.js compiles directives with `new Function` at runtime (needs
     * 'unsafe-eval') and several Blade views ship inline <script>/<style>.
     * frame-ancestors 'none' + X-Frame-Options DENY block clickjacking.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('X-XSS-Protection', '0');

        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ];

        // Local dev only: the Vite HMR server lives on its own origin.
        if (app()->environment('local')) {
            $vite = 'http://localhost:5173 http://127.0.0.1:5173';
            $csp[1] .= ' '.$vite;
            $csp[2] .= ' '.$vite;
            $csp[3] .= ' '.$vite;
            $csp[4] .= ' '.$vite;
            $csp[5] .= ' ws://localhost:5173 ws://127.0.0.1:5173';
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
