<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('events.{eventId}', function ($user, int $eventId) {
    // For now, allow any authenticated user to listen to admin event updates.
    // You can tighten this to specific roles if needed.
    return (bool) $user;
});
