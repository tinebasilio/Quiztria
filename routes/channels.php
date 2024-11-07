<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

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

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('presence-room.{roomId}', function ($Participant, $roomId) {
    // Add authorization logic here if needed
    return ['id' => $user->id, 'name' => $Participant->name];
});

Broadcast::channel('room.{roomId}', function ($Participant, $roomId) {
    return true; // Allow all for testing. For production, ensure only allowed users are authorized.
});
