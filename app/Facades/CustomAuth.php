<?php

namespace App\Facades;

use App\Http\Controllers\Auth\CustomSessionGuard;
use Illuminate\Support\Facades\Auth as BaseAuth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;

class CustomAuth extends BaseAuth
{
    /**
     * Login user without session regeneration using custom guard
     */
    public static function loginWithoutRegeneration(Authenticatable $user, bool $remember = false): bool
    {
        $guard = static::guard();

        Log::info('CustomAuth loginWithoutRegeneration called', [
            'guard_class' => get_class($guard),
            'user_id' => $user->getAuthIdentifier(),
        ]);

        if ($guard instanceof CustomSessionGuard) {
            $guard->loginWithoutRegeneration($user, $remember);
            return true;
        }

        Log::warning('CustomSessionGuard not available, using standard login');
        static::login($user, $remember);
        return false;
    }

    /**
     * Get authentication status from custom guard
     */
    public static function getAuthStatus(): array
    {
        $guard = static::guard();

        if ($guard instanceof CustomSessionGuard) {
            return $guard->getAuthStatus();
        }

        return [
            'authenticated' => static::check(),
            'user_id' => static::id(),
            'user_exists' => static::user() !== null,
            'session_id' => session()->getId(),
            'guard_class' => get_class($guard),
        ];
    }
}
