<?php

namespace Tests\feature\admin;

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole(RolesEnum::ADMIN->value);
});

test('admin can verify a user', function () {
    $user = User::factory()->create(['verified_at' => null]);
    $userId = $user->id;

    $response = $this->actingAs($this->admin)
        ->post("/admin/users/{$userId}/verify");

    $response->assertRedirect();
    $user = $user->fresh();
    expect($user->verified_at)->not->toBeNull();
    expect($user->verified_by)->toBe($this->admin->id);
});

test('admin can send password reset link', function () {
    Notification::fake();
    $user = User::factory()->create();
    $userId = $user->id;

    $response = $this->actingAs($this->admin)
        ->post("/admin/users/{$userId}/reset-password");

    $response->assertRedirect();
    Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
});

test('admin can ban a user', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    $response = $this->actingAs($this->admin)
        ->post("/admin/users/{$userId}/ban", [
            'reason' => 'Bad behavior',
            'banned_to' => now()->addDays(7)->format('Y-m-d H:i:s'),
        ]);

    $response->assertRedirect();
    $user = $user->fresh();
    expect($user->isBanned())->toBeTrue();
    $this->assertDatabaseHas('user_bans', [
        'user_id' => $user->id,
        'reason' => 'Bad behavior',
    ]);
});

test('admin can unban a user', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    $user->update([
        'banned_at' => now(),
        'banned_by' => $this->admin->id,
    ]);

    $user->bans()->create([
        'reason' => 'Testing',
        'banned_by' => $this->admin->id,
        'banned_at' => now(),
    ]);

    expect($user->isBanned())->toBeTrue();

    $response = $this->actingAs($this->admin)
        ->post("/admin/users/{$userId}/unban");

    $response->assertRedirect();
    $user = $user->fresh();
    expect($user->isBanned())->toBeFalse();
});
