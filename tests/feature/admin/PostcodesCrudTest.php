<?php

use App\Enums\RolesEnum;
use App\Models\PostalCode;
use App\Models\User;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0]);
    $this->user = User::factory()->create(['email_verified_at' => now()]);
    $this->user->assignRole(RolesEnum::ADMIN->value);
});

it('shows admin postcodes index', function (): void {
    $response = $this->actingAs($this->user)->get('/admin/postcodes');
    $response->assertSuccessful();
    $response->assertSee(trans('pages.settings.postcodes.title'));
});

it('can create, update, soft delete, restore and force delete a postal code', function (): void {
    // Create
    $this->withSession(['_token' => 'test']);
    $store = $this->actingAs($this->user)->post('/admin/postcodes', [
        'postal_code' => 54321,
        'city' => 'Sample City',
        'state' => 'SC',
        'country' => 'US',
        'municipality' => 'Sample Municipality',
        '_token' => 'test',
    ]);
    $store->assertRedirect();

    $pc = PostalCode::query()->where('postal_code', 54321)->first();
    expect($pc)->not->toBeNull();
    expect($pc->city)->toBe('Sample City');

    // Update
    $this->withSession(['_token' => 'test']);
    $update = $this->actingAs($this->user)->put('/admin/postcodes/54321', [
        'city' => 'Updated City',
        'state' => 'USC',
        'country' => 'USA',
        'municipality' => 'Updated Municipality',
        '_token' => 'test',
    ]);
    $update->assertRedirect();

    $pc->refresh();
    expect($pc->city)->toBe('Updated City');

    // Soft delete
    $this->withSession(['_token' => 'test']);
    $del = $this->actingAs($this->user)->delete('/admin/postcodes/54321', ['_token' => 'test']);
    $del->assertRedirect('/admin/postcodes');
    $this->assertSoftDeleted('postal_codes', ['postal_code' => 54321]);

    // Restore
    $this->withSession(['_token' => 'test']);
    $restore = $this->actingAs($this->user)->post('/admin/postcodes/54321/restore', ['_token' => 'test']);
    $restore->assertRedirect('/admin/postcodes');
    $this->assertDatabaseHas('postal_codes', ['postal_code' => 54321, 'deleted_at' => null]);

    // Force delete
    $this->withSession(['_token' => 'test']);
    // delete first then force
    $this->actingAs($this->user)->delete('/admin/postcodes/54321', ['_token' => 'test'])->assertRedirect();
    $force = $this->actingAs($this->user)->delete('/admin/postcodes/54321/force', ['_token' => 'test']);
    $force->assertRedirect('/admin/postcodes');
    $this->assertDatabaseMissing('postal_codes', ['postal_code' => 54321]);
});
