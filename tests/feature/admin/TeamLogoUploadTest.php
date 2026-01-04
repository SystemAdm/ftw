<?php

use App\Enums\RolesEnum;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole(RolesEnum::ADMIN->value);
    $this->actingAs($this->admin);
});

test('admin can upload a team logo during creation', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('team-logo.png');

    $response = $this->post(route('admin.teams.store'), [
        'name' => 'Test Team',
        'slug' => 'test-team',
        'description' => 'Test description',
        'logo' => $file,
        'active' => true,
    ]);

    $response->assertRedirect();
    $team = Team::where('name', 'Test Team')->first();
    expect($team->logo)->not->toBeNull();

    // The path stored should be /storage/teams/logos/...
    expect($team->logo)->toContain('storage/teams/logos/');

    $path = str_replace('/storage/', '', $team->logo);
    Storage::disk('public')->assertExists($path);
});

test('admin can update a team logo', function () {
    Storage::fake('public');

    $team = Team::factory()->create([
        'logo' => '/storage/teams/logos/old-logo.png',
    ]);

    // Create the old file
    Storage::disk('public')->put('teams/logos/old-logo.png', 'old content');

    $file = UploadedFile::fake()->image('new-logo.png');

    $response = $this->post(route('admin.teams.update', $team), [
        '_method' => 'PUT',
        'name' => $team->name,
        'logo' => $file,
        'active' => true,
    ]);

    $response->assertRedirect();
    $team->refresh();

    expect($team->logo)->toContain('storage/teams/logos/');
    expect($team->logo)->not->toContain('old-logo.png');

    $path = str_replace('/storage/', '', $team->logo);
    Storage::disk('public')->assertExists($path);
    Storage::disk('public')->assertMissing('teams/logos/old-logo.png');
});
