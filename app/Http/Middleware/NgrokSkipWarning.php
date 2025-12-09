<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NgrokSkipWarning
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add ngrok-skip-browser-warning header to bypass ngrok's interstitial page
        $response->headers->set('ngrok-skip-browser-warning', 'true');

        return $response;
    }
}
