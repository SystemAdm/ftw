<?php

use App\Models\BuildingInside;
use App\Models\BuildingLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'mod']);
});

it('allows admin to access open page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/open');

    $response->assertOk();
    $response->assertSee('admin/Open');
});

it('denies non-admin access to admin open page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/open');

    $response->assertForbidden();
});

it('registers user entry with valid QR code', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create(['name' => 'John Doe']);
    $code = Crypt::encryptString((string) $user->id);

    $response = $this->actingAs($admin)->post('/admin/open', [
        'code' => $code,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'John Doe has entered the building.');

    $this->assertDatabaseHas('building_inside', [
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('building_logs', [
        'user_id' => $user->id,
        'action' => 'in',
    ]);
});

it('registers user exit when already inside', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create(['name' => 'John Doe']);
    BuildingInside::create(['user_id' => $user->id, 'entered_at' => now()]);

    $code = Crypt::encryptString((string) $user->id);

    $response = $this->actingAs($admin)->post('/admin/open', [
        'code' => $code,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'John Doe has left the building.');

    $this->assertDatabaseMissing('building_inside', [
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('building_logs', [
        'user_id' => $user->id,
        'action' => 'out',
    ]);
});

it('returns error for invalid QR code', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/open', [
        'code' => 'invalid-code',
    ]);

    $response->assertSessionHasErrors('code');
});

it('allows mod and admin to access mod overview', function () {
    $mod = User::factory()->create();
    $mod->assignRole('mod');

    $response = $this->actingAs($mod)->get('/mod/open');
    $response->assertOk();
    $response->assertSee('mod/Open');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/mod/open');
    $response->assertOk();
});

it('denies regular user access to mod overview', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/mod/open');

    $response->assertForbidden();
});

it('shows currently inside users and history on mod page', function () {
    $mod = User::factory()->create();
    $mod->assignRole('mod');

    $user1 = User::factory()->create(['name' => 'User One']);
    $user2 = User::factory()->create(['name' => 'User Two']);

    BuildingInside::create(['user_id' => $user1->id, 'entered_at' => now()]);
    BuildingLog::create(['user_id' => $user2->id, 'action' => 'in', 'created_at' => now()->subDay()]);
    BuildingLog::create(['user_id' => $user2->id, 'action' => 'out', 'created_at' => now()->subHours(12)]);

    $response = $this->actingAs($mod)->get('/mod/open');

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('data-page');
    expect($content)->toContain('mod/Open');
});
