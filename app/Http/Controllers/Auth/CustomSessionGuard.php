<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;

class CustomSessionGuard extends SessionGuard
{
    /**
     * Log in a user without regenerating the session ID.
     */
    public function loginWithoutRegeneration(Authenticatable $user, $remember = false)
    {
        $sessionId = $this->session->getId();

        Log::info('Custom login started', [
            'user_id' => $user->getAuthIdentifier(),
            'session_id' => $sessionId,
            'remember' => $remember,
        ]);

        // Update the session with user ID
        $this->updateSession($user->getAuthIdentifier());

        // Fire login event
        $this->fireLoginEvent($user, $remember);

        // Set the user
        $this->setUser($user);

        // Handle remember token without regenerating session
        if ($remember) {
            $this->ensureRememberTokenIsSet($user);
            $this->queueRecallerCookie($user);
        }

        Log::info('Custom login completed', [
            'user_id' => $user->getAuthIdentifier(),
            'session_id_before' => $sessionId,
            'session_id_after' => $this->session->getId(),
            'session_changed' => $sessionId !== $this->session->getId(),
            'auth_check' => $this->check(),
        ]);

        return $this;
    }

    /**
     * Original login method that regenerates session (for comparison)
     */
    public function loginWithRegeneration(Authenticatable $user, $remember = false)
    {
        return parent::login($user, $remember);
    }

    /**
     * Update the session with the user ID (without regenerating)
     */
    protected function updateSession($id)
    {
        $this->session->put($this->getName(), $id);
        // Do NOT call migrate() as it regenerates session ID
        // $this->session->migrate(true); // Commented out to preserve session ID
    }

    /**
     * Queue the recaller cookie (remember me functionality)
     */
    protected function queueRecallerCookie(Authenticatable $user)
    {
        $this->getCookieJar()->queue($this->createRecaller(
            $user->getAuthIdentifier().'|'.$user->getRememberToken().'|'.$user->getAuthPassword()
        ));
    }

    /**
     * Get authentication status details for debugging
     */
    public function getAuthStatus(): array
    {
        return [
            'authenticated' => $this->check(),
            'user_id' => $this->id(),
            'user_exists' => $this->user() !== null,
            'session_id' => $this->session->getId(),
            'session_name' => $this->getName(),
            'session_value' => $this->session->get($this->getName()),
            'remember_cookie_name' => $this->getRecallerName(),
        ];
    }
}
