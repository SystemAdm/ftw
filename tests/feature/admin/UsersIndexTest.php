<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admin users index page is displayed', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(\App\Enums\RolesEnum::ADMIN->value);

    $response = $this
        ->actingAs($user)
        ->get('/admin/users');

    $response->assertOk();
});

test('owner can access admin users index page', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(\App\Enums\RolesEnum::OWNER->value);

    $response = $this
        ->actingAs($user)
        ->get('/admin/users');

    $response->assertOk();
});

if (! function_exists('inertiaPropsFromHtml')) {
    function inertiaPropsFromHtml(string $html): array
    {
        if (preg_match('/data-page="([^"]+)"/i', $html, $m)) {
            $json = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
            $page = json_decode($json, true);

            return $page['props'] ?? [];
        }
        if (preg_match('/window\.\__INERTIA__\s*=\s*(\{.*?\});/s', $html, $m)) {
            $page = json_decode($m[1], true);

            return $page['props'] ?? [];
        }

        return [];
    }
}

test('admin users index page shows verification and ban status', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole(\App\Enums\RolesEnum::ADMIN->value);

    $user1 = User::factory()->create([
        'name' => 'Verified User',
        'email_verified_at' => now(),
        'verified_at' => now(),
        'verified_by' => $admin->id,
    ]);

    $user2 = User::factory()->create([
        'name' => 'Banned User',
        'banned_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get('/admin/users');

    $response->assertOk();
    $response->assertSee('Verified User');
    $response->assertSee('Banned User');

    $props = inertiaPropsFromHtml($response->getContent());
    $users = $props['users']['data'];

    $verifiedUser = collect($users)->firstWhere('name', 'Verified User');
    expect($verifiedUser['email_verified_at'])->not->toBeNull();
    expect($verifiedUser['verified_at'])->not->toBeNull();
    expect($verifiedUser['verifier']['name'])->toBe($admin->name);

    $bannedUser = collect($users)->firstWhere('name', 'Banned User');
    expect($bannedUser['is_banned'])->toBeTrue();
});
