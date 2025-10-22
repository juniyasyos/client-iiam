<?php

use App\Models\User;

it('is accessible for guests', function () {
    $response = $this->get('/status');
    $response->assertOk();
    $response->assertSeeText('Anda belum login');
});

it('shows authenticated message when logged in', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/status');
    $response->assertOk();
    $response->assertSeeText('Anda sudah login sebagai');
});
