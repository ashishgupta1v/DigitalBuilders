<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCrmAdmin
{
    /**
     * Handle an incoming request.
     * Redirect unauthenticated requests to /crm/login.
     * Deny non-admin users with 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return redirect()->guest(route('crm.login'));
            }
            return redirect()->guest(route('crm.login'));
        }

        if (! Auth::user()->is_admin) {
            abort(403, 'Unauthorized. DigitalBuilders Founder/Admin access required.');
        }

        return $next($request);
    }
}
