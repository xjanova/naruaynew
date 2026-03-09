<?php

use App\Models\User;
use App\Models\UserBalance;

test('login page can be rendered', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
});

test('user cannot login with wrong password', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('user cannot login with non-existent email', function () {
    $response = $this->post('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout');

    $this->assertGuest();
});

test('admin middleware returns 403 for non-admin users', function () {
    $member = User::factory()->create(['user_level' => 1]);

    $response = $this->actingAs($member)->get('/admin/dashboard');
    $response->assertStatus(403);
});

test('user dashboard requires authentication', function () {
    $response = $this->get('/user/dashboard');
    $response->assertRedirect('/login');
});

test('admin dashboard requires authentication', function () {
    $response = $this->get('/admin/dashboard');
    $response->assertRedirect('/login');
});

test('unauthenticated user is redirected to login', function () {
    $response = $this->get('/user/wallet');
    $response->assertRedirect('/login');
});

test('email verification page can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');
    $response->assertStatus(200);
});
