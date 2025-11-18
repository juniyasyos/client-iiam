<?php

use App\Http\Controllers\IamTestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Juniyasyos\IamClient\Http\Controllers\SsoLoginRedirectController;
use Juniyasyos\IamClient\Http\Controllers\SsoCallbackController;
use Juniyasyos\IamClient\Http\Controllers\LogoutController;

Route::middleware(['web'])->group(function () {
    // Public routes (no authentication required)
    Route::get('/', function () {
        if (Auth::check()) {
            return view('welcome-authenticated');
        }
        return view('welcome-public');
    })->name('home');

    // SSO Routes - menggunakan package controller
    Route::get('/login', SsoLoginRedirectController::class)->name('login');
    Route::get('/auth/callback', SsoCallbackController::class)->name('sso.callback');
    Route::view('/status', 'auth-status')->name('status');

    // Debug routes
    Route::get('/debug-session', function () {
        return response()->json([
            'session_id' => session()->getId(),
            'session_started' => session()->isStarted(),
            'auth_check' => Auth::check(),
            'auth_id' => Auth::id(),
            'auth_user' => Auth::user(),
            'session_data' => session()->all(),
            'cookies' => request()->cookies->all(),
            'laravel_session_cookie' => request()->cookie('laravel_session'),
        ]);
    })->name('debug.session');

    Route::get('/debug-auth', function () {
        return response()->json([
            'custom_auth_status' => \App\Facades\CustomAuth::getAuthStatus(),
            'standard_auth' => [
                'check' => Auth::check(),
                'id' => Auth::id(),
                'user' => Auth::user(),
            ],
        ]);
    })->name('debug.auth');

    // IAM Client Package Test Routes
    Route::get('/iam-test', [IamTestController::class, 'index'])->name('iam.test');
    Route::get('/iam-test/login', [IamTestController::class, 'testLogin'])->name('iam.test.login');

    // Authenticated routes
    Route::middleware('sso.auth')->group(function () {
        Route::post('/logout', LogoutController::class)->name('logout');
        Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    });
});
