<?php

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('seeder permission membuat role super-admin idempotent', function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(PermissionSeeder::class);

    expect(Role::where('guard_name', 'web')->where('name', 'super-admin')->count())->toBe(1);
    expect(Permission::where('guard_name', 'web')->count())->toBe(count(PermissionSeeder::KERNEL_PERMISSIONS));
});

test('user evelin di-assign sebagai super-admin oleh seeder', function () {
    User::query()->create([
        'name' => 'Eve Lin',
        'email' => 'evelin@school.jmediatech.online',
        'password' => 'password',
    ]);

    $this->seed(PermissionSeeder::class);

    $user = User::where('email', 'evelin@school.jmediatech.online')->firstOrFail();

    expect($user->isSuperAdmin())->toBeTrue();
    expect($user->hasRole('super-admin'))->toBeTrue();
    expect($user->can('monitoring.monitoring.lihat'))->toBeTrue();
});

test('super-admin mendapat semua permission lewat Gate::before', function () {
    $user = User::query()->create([
        'name' => 'Eve Lin',
        'email' => 'evelin@school.jmediatech.online',
        'password' => 'password',
    ]);

    $this->seed(PermissionSeeder::class);

    expect($user->can('permission.acak.masa.depan'))->toBeTrue();
    expect($user->can('viewPulse'))->toBeTrue();
});

test('user tanpa role super-admin tidak punya akses dashboard monitoring', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create();

    expect($user->isSuperAdmin())->toBeFalse();
    expect($user->can('monitoring.monitoring.lihat'))->toBeFalse();

    // Gate dashboard dibuat default-mengizinkan; penegakan permission ada di
    // middleware 'superaccess' (EnsureSuperAccess).
    expect($user->can('viewPulse'))->toBeTrue();

    $this->actingAs($user)->get('/pulse')->assertStatus(status: 403);
});
