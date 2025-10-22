<?php

use Illuminate\Support\Facades\Http;

it('logs in through the IAM callback', function () {
    config()->set('services.iam.verify', 'http://iam.example/api/sso/verify');

    Http::fake([
        'http://iam.example/api/sso/verify' => Http::response([
            'email' => 'user@example.com',
            'name' => 'Example User',
            'roles' => ['admin'],
            'perms' => ['read'],
            'sub' => '12345',
            'app' => 'client-example',
        ], 200),
    ]);

    $response = $this->get('/auth/callback?token=test-token');

    $response->assertRedirect('/');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'user@example.com']);

    $iam = session('iam');
    expect($iam)->not->toBeNull();

    expect($iam['roles'])->toBe(['admin'])
        ->and($iam['perms'])->toBe(['read'])
        ->and($iam['sub'])->toBe('12345');
});
