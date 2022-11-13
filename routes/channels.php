<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });


// Broadcast::channel('users.{userId}', function ($user, $userId) {
//     return $user->id === User::find($userId);
// });

Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    if (Auth::check($user)) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    // return Auth::check($user);
});

// Broadcast::channel('presence.chat.{roomId}', function ($user) {
//     if (Auth::check($user)) {
//         return $user;
//     }
// });