<?php

use App\Enums\RolesEnum;
use App\Models\BuildingLog;
use App\Models\Event;
use App\Models\EventLog;
use App\Models\PostalCode;
use App\Models\User;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0]);
});

it('renders the admin user show page', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole(RolesEnum::ADMIN->value);
    $user = User::factory()->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.users.show', $user));

    $response->assertStatus(200);

    $props = inertiaPropsFromHtml($response->getContent());
    expect($props['user']['id'])->toBe($user->id);
    expect($props['user']['name'])->toBe($user->name);
});

it('shows event logs with translated actions', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole(RolesEnum::ADMIN->value);
    $user = User::factory()->create();

    // Create postal codes to satisfy location foreign key constraint
    PostalCode::factory()->create(['postal_code' => 1300]);
    PostalCode::factory()->create(['postal_code' => 1353]);
    PostalCode::factory()->create(['postal_code' => 1350]);
    PostalCode::factory()->create(['postal_code' => 3512]);

    $event = Event::factory()->create();

    EventLog::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'action' => 'in',
        'created_at' => now()->subMinute(),
    ]);

    EventLog::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'action' => 'out',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.users.show', $user));

    $response->assertStatus(200);

    $props = inertiaPropsFromHtml($response->getContent());
    $logs = $props['user']['logs'];

    expect($logs)->toHaveCount(2);
    $inLog = collect($logs)->firstWhere('action', 'in');
    $outLog = collect($logs)->firstWhere('action', 'out');

    expect($inLog)->not->toBeNull();
    expect($outLog)->not->toBeNull();
});

it('shows building logs in recent activity', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole(RolesEnum::ADMIN->value);
    $user = User::factory()->create();

    BuildingLog::create([
        'user_id' => $user->id,
        'action' => 'in',
        'created_at' => now()->subMinutes(5),
    ]);

    BuildingLog::create([
        'user_id' => $user->id,
        'action' => 'out',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.users.show', $user));

    $response->assertStatus(200);

    $props = inertiaPropsFromHtml($response->getContent());
    $userProps = $props['user'];

    expect($userProps['building_logs'])->toHaveCount(2);
});

if (! function_exists('inertiaPropsFromHtml')) {
    function inertiaPropsFromHtml(string $html): array
    {
        if (preg_match('/data-page="([^"]+)"/i', $html, $m)) {
            $json = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
            $page = json_decode($json, true);

            return $page['props'] ?? [];
        }
        if (preg_match('/window\.\__INERTIA__\s*=\s*(\{.*?\});/s', $html, $m)) {
            $page = json_decode($m[1], true);

            return $page['props'] ?? [];
        }

        return [];
    }
}

it('restricts access to admin user show page for unauthenticated users', function () {
    $user = User::factory()->create();

    $this->get(route('admin.users.show', $user))
        ->assertRedirect(route('login'));
});
