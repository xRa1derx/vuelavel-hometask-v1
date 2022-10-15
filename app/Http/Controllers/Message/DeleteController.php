<?php

namespace App\Http\Controllers\Message;

use App\Events\Message as EventsMessage;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;

class DeleteController extends Controller
{
    public function __invoke(Message $message)
    {
        $message->delete();
        if ($message['from'] === 1) {
            $userId = $message['to'];
        } else {
            $userId = $message['from'];
        }

        $user = User::findOrFail($userId);

        broadcast(new EventsMessage(
            $message,
            $user
        ));

        return $message;
    }
}
