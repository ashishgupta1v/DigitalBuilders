<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and attach hardening security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Suppress PHP info exposure
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }

        /** @var Response $response */
        $response = $next($request);

        // Security headers
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Content Security Policy
        $csp = "default-src 'self' https: data: blob: 'unsafe-inline' 'unsafe-eval'; "
            . "img-src 'self' https: data: blob:; "
            . "font-src 'self' https: data:; "
            . "connect-src 'self' https: wss:; "
            . "frame-ancestors 'none';";
        $response->headers->set('Content-Security-Policy', $csp);

        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
