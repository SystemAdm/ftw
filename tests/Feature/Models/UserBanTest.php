<?php

use App\Models\User;
use App\Models\UserBan;

use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a user ban and relates to user and bannedBy', function (): void {
    $target = User::factory()->create();
    $admin = User::factory()->create();

    $ban = UserBan::create([
        'user_id' => $target->id,
        'banned_at' => now(),
        'banned_to' => now()->addDays(7),
        'banned_by' => $admin->id,
        'reason' => 'Violation of rules',
    ]);

    expect($ban->user->is($target))->toBeTrue();
    expect($ban->bannedBy->is($admin))->toBeTrue();
    expect($target->bans()->count())->toBe(1);

    assertDatabaseHas('user_bans', [
        'id' => $ban->id,
        'user_id' => $target->id,
        'banned_by' => $admin->id,
        'reason' => 'Violation of rules',
    ]);
});
