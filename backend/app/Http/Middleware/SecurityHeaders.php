<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), midi=(), sync-xhr=(), microphone=(), camera=(), magnetometer=(), gyroscope=(), fullscreen=(self), payment=()');

        // Content Security Policy — includes GA4, Sentry, Google Fonts
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://www.google-analytics.com http://localhost:5173 http://127.0.0.1:5173; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net; ";
        $csp .= "img-src 'self' data: https: https://www.google-analytics.com https://www.googletagmanager.com; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net; ";
        $csp .= "connect-src 'self' {$appHost} https://www.google-analytics.com https://analytics.google.com https://*.sentry.io http://localhost:5173 ws://localhost:5173 http://127.0.0.1:5173 ws://127.0.0.1:5173; ";
        $csp .= "frame-ancestors 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        // HSTS (Only if HTTPS)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
