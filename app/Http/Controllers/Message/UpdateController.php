<?php

namespace App\Http\Controllers\Message;

use App\Events\Message as EventsMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\UpdateRequest;
use App\Models\Message;
use App\Models\User;

class UpdateController extends Controller
{
    public function __invoke(UpdateRequest $request, Message $message)
    {
        $data = $request->validated();

        $userIdInMessageRequest = null;
        foreach ($data as $key => $value) {
            if ($key == 'from') {
                $userIdInMessageRequest = $value;
            }
        }
        $userIdInMessageDB = $message->from;
        if ($userIdInMessageDB === $userIdInMessageRequest) {
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
            $message->update($data);
            return $message;
        } else {
            abort(403, 'Error!!!.');
        }
    }
}
