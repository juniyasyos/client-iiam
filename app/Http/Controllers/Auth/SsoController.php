<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Facades\CustomAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $app = config('services.iam.app', 'client-example');
        $iam = rtrim((string) config('services.iam.host'), '/');

        $callback = urlencode(route('sso.callback'));

        return redirect()->away("{$iam}/sso/redirect?app={$app}&callback={$callback}");
    }

    public function callback(): RedirectResponse
    {
        $token = request('token');

        Log::info('SSO callback received', [
            'token' => $token ? 'present' : 'missing',
            'session_id' => session()->getId(),
        ]);

        abort_if(! $token, 400, 'Missing token');

        $verifyEndpoint = config('services.iam.verify');
        abort_if(! $verifyEndpoint, 500, 'IAM verify endpoint is not configured');

        try {
            $response = Http::timeout(10)->asJson()->post($verifyEndpoint, ['token' => $token]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('IAM server unavailable during token verification', [
                'token_preview' => substr($token, 0, 10) . '...',
                'endpoint' => $verifyEndpoint,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'sso' => 'Authentication server temporarily unavailable. Please try again.',
            ]);
        }

        if (! $response->ok()) {
            Log::warning('SSO verify failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return redirect()->route('login')->withErrors([
                'sso' => 'SSO token invalid/expired',
            ]);
        }

        $payload = $response->json();
        abort_unless(isset($payload['email']) && is_string($payload['email']), 422, 'Missing user email');

        $user = User::query()->updateOrCreate(
            ['email' => $payload['email']],
            [
                'name' => $payload['name'] ?? $payload['email'],
                'password' => Str::password(32),
            ],
        );

        // Use CustomAuth facade to login without session regeneration
        $success = CustomAuth::loginWithoutRegeneration($user, true);

        if (!$success) {
            Log::warning('CustomAuth failed, falling back to alternative methods');
            // Fallback to manual method if custom guard is not available
            try {
                CustomAuthController::loginUserManually($user, true);
                Log::info('Used fallback CustomAuthController method');
            } catch (\Exception $e) {
                Log::warning('Manual login failed, using Auth::loginUsingId', [
                    'error' => $e->getMessage(),
                ]);
                Auth::loginUsingId($user->id, true);
            }
        }

        session([
            'iam' => [
                'sub' => $payload['sub'] ?? null,
                'app' => $payload['app'] ?? null,
                'roles' => $payload['roles'] ?? [],
                'perms' => $payload['perms'] ?? [],
            ],
        ]);

        Log::info('SSO callback OK', [
            'user' => $user->id,
            'email' => $user->email,
            'sid' => session()->getId(),
            'auth_check' => Auth::check(),
        ]);

        return redirect()->route('home');
    }

    public function logout(): RedirectResponse
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        Log::info('SSO logout initiated', [
            'user_id' => $userId,
            'session_id' => $sessionId,
        ]);

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        Log::info('SSO logout completed', [
            'previous_user_id' => $userId,
            'old_session_id' => $sessionId,
            'new_session_id' => session()->getId(),
        ]);

        return redirect()->route('home')->with('message', 'You have been logged out successfully.');
    }
}
