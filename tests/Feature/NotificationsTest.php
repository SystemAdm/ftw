<?php

use App\Models\User;
use App\Notifications\Auth\GuardianRelationConfirmation;

test('notifications page is accessible', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk();
});

test('can see database notifications on the page', function () {
    $guardian = User::factory()->create();
    $minor = User::factory()->create(['name' => 'Little Timmy']);

    $guardian->notify(new GuardianRelationConfirmation($minor));

    expect($guardian->unreadNotifications)->toHaveCount(1);

    $this->actingAs($guardian)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Little Timmy');
});

test('can mark notification as read', function () {
    $user = User::factory()->create();
    $minor = User::factory()->create(['name' => 'Little Timmy']);

    $user->notify(new GuardianRelationConfirmation($minor));
    $notification = $user->unreadNotifications->first();

    $this->actingAs($user)
        ->post(route('notifications.markAsRead', $notification->id))
        ->assertRedirect();

    expect($user->refresh()->unreadNotifications)->toHaveCount(0);
});

test('can mark all notifications as read', function () {
    $user = User::factory()->create();
    $minor1 = User::factory()->create(['name' => 'Timmy']);
    $minor2 = User::factory()->create(['name' => 'Jimmy']);

    $user->notify(new GuardianRelationConfirmation($minor1));
    $user->notify(new GuardianRelationConfirmation($minor2));

    expect($user->unreadNotifications)->toHaveCount(2);

    $this->actingAs($user)
        ->post(route('notifications.markAllAsRead'))
        ->assertRedirect();

    expect($user->refresh()->unreadNotifications)->toHaveCount(0);
});

test('can delete notification', function () {
    $user = User::factory()->create();
    $minor = User::factory()->create(['name' => 'Little Timmy']);

    $user->notify(new GuardianRelationConfirmation($minor));
    $notification = $user->notifications->first();

    $this->actingAs($user)
        ->delete(route('notifications.destroy', $notification->id))
        ->assertRedirect();

    expect($user->refresh()->notifications)->toHaveCount(0);
});
