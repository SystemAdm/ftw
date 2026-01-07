<?php

use App\Enums\RolesEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0]);
    $this->user = User::factory()->create(['email_verified_at' => now()]);
    $this->user->assignRole(RolesEnum::ADMIN->value);
});

it('shows permissions index', function (): void {
    $response = $this->actingAs($this->user)->get('/admin/permissions');
    $response->assertSuccessful();
});

it('creates a permission', function (): void {
    $this->withSession(['_token' => 'test']);
    $response = $this->actingAs($this->user)->post('/admin/permissions', [
        'name' => 'articles.view',
        'guard_name' => 'web',
        '_token' => 'test',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('permissions', ['name' => 'articles.view']);
});

it('updates a permission', function (): void {
    $permission = Permission::firstOrCreate(['name' => 'posts.create', 'guard_name' => 'web']);

    $this->withSession(['_token' => 'test']);
    $response = $this->actingAs($this->user)->put("/admin/permissions/{$permission->id}", [
        'name' => 'posts.publish',
        'guard_name' => 'web',
        '_token' => 'test',
    ]);

    $response->assertRedirect();
    $permission->refresh();
    expect($permission->name)->toBe('posts.publish');
});

it('deletes a permission', function (): void {
    $permission = Permission::firstOrCreate(['name' => 'temp.delete', 'guard_name' => 'web']);

    $this->withSession(['_token' => 'test']);
    $response = $this->actingAs($this->user)->delete("/admin/permissions/{$permission->id}", ['_token' => 'test']);
    $response->assertRedirect('/admin/permissions');

    $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
});
