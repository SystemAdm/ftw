<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

it('shows permissions index', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get('/admin/permissions');
    $response->assertSuccessful();
});

it('creates a permission', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $this->withSession(['_token' => 'test']);
    $response = $this->post('/admin/permissions', [
        'name' => 'articles.view',
        'guard_name' => 'web',
        '_token' => 'test',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('permissions', ['name' => 'articles.view']);
});

it('updates a permission', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $permission = Permission::firstOrCreate(['name' => 'posts.create', 'guard_name' => 'web']);

    $this->withSession(['_token' => 'test']);
    $response = $this->put("/admin/permissions/{$permission->id}", [
        'name' => 'posts.publish',
        'guard_name' => 'web',
        '_token' => 'test',
    ]);

    $response->assertRedirect();
    $permission->refresh();
    expect($permission->name)->toBe('posts.publish');
});

it('deletes a permission', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $permission = Permission::firstOrCreate(['name' => 'temp.delete', 'guard_name' => 'web']);

    $this->withSession(['_token' => 'test']);
    $response = $this->delete("/admin/permissions/{$permission->id}", ['_token' => 'test']);
    $response->assertRedirect('/admin/permissions');

    $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
});
