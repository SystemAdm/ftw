<?php

use App\models\Team;
use App\models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin teams index page is displayed', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/teams');

    $response->assertOk();
    $response->assertSee(trans('pages.settings.teams.title'));
});

test('can create a team', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->post('/admin/teams', [
            'name' => 'New Team',
            'slug' => 'new-team',
            'description' => 'Team description',
            'active' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('teams', ['name' => 'New Team']);
});

test('can update a team', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $team = Team::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put("/admin/teams/{$team->id}", [
            'name' => 'Updated Team Name',
            'slug' => 'updated-team-name',
            'active' => false,
        ]);

    $response->assertRedirect();
    expect($team->refresh()->name)->toBe('Updated Team Name');
    expect((bool) $team->active)->toBeFalse();
});

test('can soft delete, restore, and force delete a team', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $team = Team::factory()->create();

    // Soft delete
    $response = $this
        ->actingAs($user)
        ->delete("/admin/teams/{$team->id}");

    $response->assertRedirect('/admin/teams');
    $this->assertSoftDeleted('teams', ['id' => $team->id]);

    // Restore
    $response = $this
        ->actingAs($user)
        ->post("/admin/teams/{$team->id}/restore");

    $response->assertRedirect('/admin/teams');
    $this->assertDatabaseHas('teams', ['id' => $team->id, 'deleted_at' => null]);

    // Force delete
    $this->delete("/admin/teams/{$team->id}"); // soft delete again
    $response = $this
        ->actingAs($user)
        ->delete("/admin/teams/{$team->id}/force");

    $response->assertRedirect('/admin/teams');
    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});
