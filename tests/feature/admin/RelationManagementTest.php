<?php

namespace Tests\feature\admin;

use App\models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::create(['name' => 'admin']);
});

it('can view relations index', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $guardian = User::factory()->create();
    $minor = User::factory()->create();

    DB::table('guardian_user')->insert([
        'guardian_id' => $guardian->id,
        'minor_id' => $minor->id,
        'relationship' => 'Parent',
        'verified_user_at' => now(),
        'verified_guardian_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($admin)
        ->get(route('admin.relations.index'))
        ->assertOk();
});

it('can create a relation', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $guardian = User::factory()->create();
    $minor = User::factory()->create();

    actingAs($admin)
        ->post(route('admin.relations.store'), [
            'guardian_id' => $guardian->id,
            'minor_id' => $minor->id,
            'relationship' => 'Parent',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('guardian_user', [
        'guardian_id' => $guardian->id,
        'minor_id' => $minor->id,
        'relationship' => 'Parent',
        'verified_by' => $admin->id,
    ]);
});

it('can verify a relation', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $guardian = User::factory()->create();
    $minor = User::factory()->create();

    DB::table('guardian_user')->insert([
        'guardian_id' => $guardian->id,
        'minor_id' => $minor->id,
        'relationship' => 'Parent',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($admin)
        ->post(route('admin.relations.verify', [$guardian, $minor]))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('guardian_user', [
        'guardian_id' => $guardian->id,
        'minor_id' => $minor->id,
        'verified_by' => $admin->id,
    ]);
});

it('can delete a relation', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $guardian = User::factory()->create();
    $minor = User::factory()->create();

    DB::table('guardian_user')->insert([
        'guardian_id' => $guardian->id,
        'minor_id' => $minor->id,
        'relationship' => 'Parent',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($admin)
        ->delete(route('admin.relations.destroy', [$guardian, $minor]))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('guardian_user', [
        'guardian_id' => $guardian->id,
        'minor_id' => $minor->id,
    ]);
});

it('can search users for relations', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $user = User::factory()->create(['name' => 'Searchable User']);

    actingAs($admin)
        ->get(route('admin.relations.search-users', ['q' => 'Searchable']))
        ->assertOk()
        ->assertJsonFragment(['name' => 'Searchable User']);
});
