<?php

use App\Models\User;
use App\Models\UserBalance;
use App\Models\Setting;

beforeEach(function () {
    $this->member = User::factory()->create(['user_level' => 1]);
    UserBalance::create(['user_id' => $this->member->id, 'balance_amount' => 5000, 'purchase_wallet' => 0]);
    Setting::create(['key' => 'company_name', 'value' => 'Naruay MLM', 'group' => 'company']);
    Setting::create(['key' => 'default_currency', 'value' => 'THB', 'group' => 'currency']);
    Setting::create(['key' => 'currency_symbol', 'value' => '฿', 'group' => 'currency']);
});

test('member can view dashboard', function () {
    $response = $this->actingAs($this->member)->get('/user/dashboard');
    $response->assertStatus(200);
});

test('member can view binary tree', function () {
    $response = $this->actingAs($this->member)->get('/user/tree/binary');
    $response->assertStatus(200);
});

test('member can view sponsor tree', function () {
    $response = $this->actingAs($this->member)->get('/user/tree/sponsor');
    $response->assertStatus(200);
});

test('member can view wallet', function () {
    $response = $this->actingAs($this->member)->get('/user/wallet');
    $response->assertStatus(200);
});

test('member can view commissions', function () {
    $response = $this->actingAs($this->member)->get('/user/wallet/commissions');
    $response->assertStatus(200);
});

test('member can view profile', function () {
    $response = $this->actingAs($this->member)->get('/user/profile');
    $response->assertStatus(200);
});

test('member can view shop', function () {
    $response = $this->actingAs($this->member)->get('/user/shop');
    $response->assertStatus(200);
});
