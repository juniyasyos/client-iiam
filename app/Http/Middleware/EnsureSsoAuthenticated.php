<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureSsoAuthenticated
{
    public function handle($request, Closure $next)
    {
        // Force session start if not started
        if (!session()->isStarted()) {
            session()->start();
        }

        // Get consistent session ID
        $sessionId = session()->getId();

        if (! Auth::check()) {
            Log::info('SSO auth middleware: User not authenticated', [
                'path' => $request->path(),
                'session_id' => $sessionId,
                'session_started' => session()->isStarted(),
                'auth_session_key' => session('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'),
                'all_session_keys' => array_keys(session()->all()),
            ]);

            return redirect()->route('login');
        }

        Log::debug('SSO auth middleware: User authenticated', [
            'user_id' => Auth::id(),
            'path' => $request->path(),
            'session_id' => $sessionId,
        ]);

        return $next($request);
    }
}
