<?php

use App\Models\User;

test('admin user trying to access superadmin dashboard gets redirected to admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/superadmin/dashboard');

    $response->assertRedirect(route('admin.dashboard'));
});

test('regular user trying to access superadmin dashboard gets redirected to user dashboard', function () {
    $user = User::factory()->user()->create();

    $response = $this->actingAs($user)->get('/superadmin/dashboard');

    $response->assertRedirect(route('user.dashboard'));
});

test('regular user trying to access admin dashboard gets redirected to user dashboard', function () {
    $user = User::factory()->user()->create();

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertRedirect(route('user.dashboard'));
});

test('superadmin trying to access admin dashboard gets redirected to superadmin dashboard', function () {
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->get('/admin/dashboard');

    $response->assertRedirect(route('superadmin.dashboard'));
});

test('superadmin trying to access user dashboard gets redirected to superadmin dashboard', function () {
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->get('/user/dashboard');

    $response->assertRedirect(route('superadmin.dashboard'));
});

test('users can access their correct dashboards', function () {
    // Create users of each role
    $superadmin = User::factory()->superadmin()->create();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->user()->create();

    // Test superadmin can access superadmin dashboard
    $response = $this->actingAs($superadmin)->get('/superadmin/dashboard');
    $response->assertStatus(200);

    // Test admin can access admin dashboard
    $response = $this->actingAs($admin)->get('/admin/dashboard');
    $response->assertStatus(200);

    // Test user can access user dashboard
    $response = $this->actingAs($user)->get('/user/dashboard');
    $response->assertStatus(200);
});
