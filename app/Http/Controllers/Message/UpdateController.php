<?php

namespace App\Http\Controllers\Message;

use App\Events\Message as EventsMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\UpdateRequest;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

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
            $user = Auth::user();
            broadcast(new EventsMessage($message, $user));
            $message->update($data);
            return $message;
        } else {
            abort(403, 'Error!!!.');
        }
    }
}
