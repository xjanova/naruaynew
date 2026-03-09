<?php

use App\Models\User;
use App\Models\UserBalance;
use App\Models\Setting;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    UserBalance::create(['user_id' => $this->admin->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);
    Setting::create(['key' => 'company_name', 'value' => 'Naruay MLM', 'group' => 'company']);
    Setting::create(['key' => 'default_currency', 'value' => 'THB', 'group' => 'currency']);
    Setting::create(['key' => 'currency_symbol', 'value' => '฿', 'group' => 'currency']);
});

test('admin can view dashboard', function () {
    $response = $this->actingAs($this->admin)->get('/admin/dashboard');
    $response->assertStatus(200);
});

test('admin can view members list', function () {
    $response = $this->actingAs($this->admin)->get('/admin/members');
    $response->assertStatus(200);
});

test('admin can view member details', function () {
    $member = User::factory()->create(['sponsor_id' => $this->admin->id]);
    UserBalance::create(['user_id' => $member->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $response = $this->actingAs($this->admin)->get("/admin/members/{$member->id}");
    $response->assertStatus(200);
});

test('admin can view binary tree', function () {
    $response = $this->actingAs($this->admin)->get('/admin/tree');
    $response->assertStatus(200);
});

test('admin can view commissions', function () {
    $response = $this->actingAs($this->admin)->get('/admin/commissions');
    $response->assertStatus(200);
});

test('admin can view products', function () {
    $response = $this->actingAs($this->admin)->get('/admin/products');
    $response->assertStatus(200);
});

test('admin can view payouts', function () {
    $response = $this->actingAs($this->admin)->get('/admin/payouts');
    $response->assertStatus(200);
});

test('admin can view settings', function () {
    $response = $this->actingAs($this->admin)->get('/admin/settings');
    $response->assertStatus(200);
});

test('admin can view ranks', function () {
    $response = $this->actingAs($this->admin)->get('/admin/ranks');
    $response->assertStatus(200);
});

test('admin can view orders', function () {
    $response = $this->actingAs($this->admin)->get('/admin/orders');
    $response->assertStatus(200);
});

test('admin can view epins', function () {
    $response = $this->actingAs($this->admin)->get('/admin/epins');
    $response->assertStatus(200);
});
