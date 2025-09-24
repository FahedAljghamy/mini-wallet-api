<?php

/**
 * Broadcast Channels
 *
 * Channel authorization for real-time broadcasting
 *
 * @author Fahed
 */

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

// Default user channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Transaction channels for authenticated users
Broadcast::channel('transactions.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
