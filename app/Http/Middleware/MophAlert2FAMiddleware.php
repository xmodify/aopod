<?php

namespace App\Http\Middleware;

use App\Models\MainSetting;
use Closure;
use Illuminate\Http\Request;

class MophAlert2FAMiddleware
{
    /**
     * Handle an incoming request for 2FA verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Exclude AJAX, JSON, and static asset requests to prevent duplicate redirects
        if ($request->ajax() || $request->wantsJson() || preg_match('/\.(ico|png|jpg|jpeg|gif|css|js|svg|map|woff|woff2|ttf|eot)$/i', $request->path())) {
            return $next($request);
        }

        // 2. If user is not logged in, let standard auth middleware handle it
        if (!auth()->check()) {
            return $next($request);
        }

        // 3. Exclude authentication and 2FA verification routes from redirection
        if ($request->routeIs('auth.2fa.*') || $request->is('login/verify-2fa*') || $request->is('login/resend-2fa*') || $request->is('logout') || $request->is('login')) {
            return $next($request);
        }

        // 4. Check if Moph Alert 2FA is active
        $mophAlertActive = MainSetting::get('moph_alert_active', config('moph.alert.active', 'N'));
        if ($mophAlertActive !== 'Y') {
            return $next($request);
        }

        // 5. If logged in but 2FA is not verified yet, redirect to 2FA verification page
        if (session('moph_alert_2fa_verified') === false) {
            return redirect()->route('auth.2fa.index');
        }

        return $next($request);
    }
}
