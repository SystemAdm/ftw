<?php

use App\Enums\RolesEnum;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TeamSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(TeamSeeder::class);
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    setPermissionsTeamId(0);
    $this->admin->assignRole(RolesEnum::ADMIN->value);
    $this->actingAs($this->admin);
});

it('shows roles index with scope information', function (): void {
    $team = Team::where('slug', 'SH')->first();
    Role::firstOrCreate(['name' => 'TeamRole', 'guard_name' => 'web', 'team_id' => $team->id]);
    Role::firstOrCreate(['name' => 'GlobalRole', 'guard_name' => 'web', 'team_id' => 0]);

    $response = $this->get('/admin/roles');
    $response->assertSuccessful();
    $response->assertSee('Global');
    $response->assertSee($team->name);
});

it('shows roles index', function (): void {
    $response = $this->get('/admin/roles');
    $response->assertSuccessful();
});

it('creates a role with permissions', function (): void {
    $permA = Permission::firstOrCreate(['name' => 'posts.view']);
    $permB = Permission::firstOrCreate(['name' => 'posts.edit']);

    $this->withSession(['_token' => 'test']);
    $response = $this->post('/admin/roles', [
        'name' => 'Editor',
        'guard_name' => 'web',
        'permissions' => [$permA->id, $permB->id],
        '_token' => 'test',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('roles', ['name' => 'Editor']);

    $role = Role::where('name', 'Editor')->firstOrFail();
    expect($role->permissions()->pluck('name')->all())
        ->toContain('posts.view', 'posts.edit');
});

it('updates a role and permissions', function (): void {
    $role = Role::firstOrCreate(['name' => 'Author', 'guard_name' => 'web']);
    $p1 = Permission::firstOrCreate(['name' => 'articles.create']);
    $p2 = Permission::firstOrCreate(['name' => 'articles.publish']);

    $this->withSession(['_token' => 'test']);
    $response = $this->put("/admin/roles/{$role->id}", [
        'name' => 'Publisher',
        'guard_name' => 'web',
        'permissions' => [$p2->id],
        '_token' => 'test',
    ]);
    $response->assertRedirect();

    $role->refresh();
    expect($role->name)->toBe('Publisher');
    expect($role->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing(['articles.publish']);
});

it('creates a team-specific role', function (): void {
    $team = Team::where('slug', 'SH')->first();

    $this->withSession(['_token' => 'test']);
    $response = $this->post('/admin/roles', [
        'name' => 'TeamEditor',
        'guard_name' => 'web',
        'team_id' => $team->id,
        '_token' => 'test',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('roles', [
        'name' => 'TeamEditor',
        'team_id' => $team->id,
    ]);
});

it('updates a role scope', function (): void {
    $team = Team::where('slug', 'SH')->first();
    $role = Role::firstOrCreate(['name' => 'Author', 'guard_name' => 'web', 'team_id' => 0]);

    $this->withSession(['_token' => 'test']);
    $response = $this->put("/admin/roles/{$role->id}", [
        'name' => 'Author',
        'guard_name' => 'web',
        'team_id' => $team->id,
        '_token' => 'test',
    ]);
    $response->assertRedirect();

    $role->refresh();
    expect($role->team_id)->toEqual($team->id);
});

it('deletes a role', function (): void {
    $role = Role::firstOrCreate(['name' => 'TempRole', 'guard_name' => 'web']);

    $this->withSession(['_token' => 'test']);
    $response = $this->delete("/admin/roles/{$role->id}", ['_token' => 'test']);
    $response->assertRedirect('/admin/roles');

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

it('searches users excluding users already with role', function (): void {
    $role = Role::firstOrCreate(['name' => 'Searcher', 'guard_name' => 'web']);
    $u1 = User::factory()->create(['name' => 'Alice Finder', 'email_verified_at' => now()]);
    $u2 = User::factory()->create(['name' => 'Bob Finder', 'email_verified_at' => now()]);
    $u2->assignRole($role);

    $response = $this->getJson("/admin/roles/{$role->id}/users/search?q=Finder");
    $response->assertSuccessful();
    $names = collect($response->json('data'))->pluck('name')->all();
    expect($names)->toContain('Alice Finder');
    expect($names)->not->toContain('Bob Finder');
});

it('assigns and removes user from role', function (): void {
    $role = Role::firstOrCreate(['name' => 'Managers', 'guard_name' => 'web']);
    $target = User::factory()->create(['email_verified_at' => now()]);

    $this->withSession(['_token' => 'test']);
    $assignResp = $this->post("/admin/roles/{$role->id}/users", ['user_id' => $target->id, '_token' => 'test']);
    $assignResp->assertRedirect("/admin/roles/{$role->id}");
    expect($target->fresh()->hasRole($role))->toBeTrue();

    $this->withSession(['_token' => 'test']);
    $removeResp = $this->delete("/admin/roles/{$role->id}/users/{$target->id}", ['_token' => 'test']);
    $removeResp->assertRedirect("/admin/roles/{$role->id}");
    expect($target->fresh()->hasRole($role))->toBeFalse();
});
