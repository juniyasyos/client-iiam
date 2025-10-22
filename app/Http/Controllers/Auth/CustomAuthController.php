<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CustomAuthController extends Controller
{
    /**
     * Alternative login method that avoids session regeneration
     */
    public static function loginUserManually(User $user, bool $remember = false): void
    {
        // Store current session ID before auth
        $currentSessionId = session()->getId();

        // Get the user guard
        $guard = Auth::guard();

        // Set the user manually without regenerating session
        $guard->setUser($user);

        // Set remember token if needed
        if ($remember && method_exists($user, 'setRememberToken')) {
            $user->setRememberToken(\Illuminate\Support\Str::random(60));
            $user->save();
        }

        // Update last_login timestamp if column exists
        if (method_exists($user, 'updateLastLogin')) {
            $user->updateLastLogin();
        }

        // Log the manual login
        Log::info('Manual auth login completed', [
            'user_id' => $user->id,
            'session_id_before' => $currentSessionId,
            'session_id_after' => session()->getId(),
            'session_changed' => $currentSessionId !== session()->getId(),
        ]);
    }

    /**
     * Check if a user is properly authenticated
     */
    public static function verifyAuthentication(): array
    {
        return [
            'auth_check' => Auth::check(),
            'auth_id' => Auth::id(),
            'auth_user_exists' => Auth::user() !== null,
            'session_id' => session()->getId(),
            'session_auth_id' => session()->get('login_web_' . Auth::getDefaultDriver() . '_' . sha1(static::class)),
        ];
    }
}
