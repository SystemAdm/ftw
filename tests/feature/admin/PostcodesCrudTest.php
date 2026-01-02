<?php

use App\Models\PostalCode;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows admin postcodes index', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get('/admin/postcodes');
    $response->assertSuccessful();
    $response->assertSee(trans('pages.settings.postcodes.title'));
});

it('can create, update, soft delete, restore and force delete a postal code', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    // Create
    $this->withSession(['_token' => 'test']);
    $store = $this->post('/admin/postcodes', [
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
    $update = $this->put('/admin/postcodes/54321', [
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
    $del = $this->delete('/admin/postcodes/54321', ['_token' => 'test']);
    $del->assertRedirect('/admin/postcodes');
    $this->assertSoftDeleted('postal_codes', ['postal_code' => 54321]);

    // Restore
    $this->withSession(['_token' => 'test']);
    $restore = $this->post('/admin/postcodes/54321/restore', ['_token' => 'test']);
    $restore->assertRedirect('/admin/postcodes');
    $this->assertDatabaseHas('postal_codes', ['postal_code' => 54321, 'deleted_at' => null]);

    // Force delete
    $this->withSession(['_token' => 'test']);
    // delete first then force
    $this->delete('/admin/postcodes/54321', ['_token' => 'test'])->assertRedirect();
    $force = $this->delete('/admin/postcodes/54321/force', ['_token' => 'test']);
    $force->assertRedirect('/admin/postcodes');
    $this->assertDatabaseMissing('postal_codes', ['postal_code' => 54321]);
});
