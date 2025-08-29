<?php

use App\Models\User;

test('manual URL manipulation attempts are handled correctly', function () {
    // Test various manual URL manipulation attempts

    // Create users
    $superadmin = User::factory()->superadmin()->create();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->user()->create();

    // Test superadmin trying to access other areas
    $response = $this->actingAs($superadmin)->get('/admin/dashboard');
    $response->assertRedirect(route('superadmin.dashboard'));

    $response = $this->actingAs($superadmin)->get('/user/dashboard');
    $response->assertRedirect(route('superadmin.dashboard'));

    // Test admin trying to access other areas
    $response = $this->actingAs($admin)->get('/superadmin/dashboard');
    $response->assertRedirect(route('admin.dashboard'));

    $response = $this->actingAs($admin)->get('/user/dashboard');
    $response->assertRedirect(route('admin.dashboard')); // Admin should be redirected to their own dashboard

    // Test user trying to access other areas
    $response = $this->actingAs($user)->get('/superadmin/dashboard');
    $response->assertRedirect(route('user.dashboard'));

    $response = $this->actingAs($user)->get('/admin/dashboard');
    $response->assertRedirect(route('user.dashboard'));
});

test('inactive users cannot access any dashboard', function () {
    $inactiveUser = User::factory()->create(['is_active' => false]);

    $response = $this->actingAs($inactiveUser)->get('/user/dashboard');
    $response->assertStatus(403);

    $response = $this->actingAs($inactiveUser)->get('/admin/dashboard');
    $response->assertStatus(403);

    $response = $this->actingAs($inactiveUser)->get('/superadmin/dashboard');
    $response->assertStatus(403);
});

test('legacy dashboard route redirects correctly for all roles', function () {
    $superadmin = User::factory()->superadmin()->create();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->user()->create();

    // Test legacy /dashboard route redirects
    $response = $this->actingAs($superadmin)->get('/dashboard');
    $response->assertRedirect(route('superadmin.dashboard'));

    $response = $this->actingAs($admin)->get('/dashboard');
    $response->assertRedirect(route('admin.dashboard'));

    $response = $this->actingAs($user)->get('/dashboard');
    $response->assertRedirect(route('user.dashboard'));
});
