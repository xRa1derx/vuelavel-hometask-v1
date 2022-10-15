<?php

namespace App\Http\Controllers\Message;

use App\Events\Message as EventsMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreRequest;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function __invoke(StoreRequest $request)
    {
        // dd($request);
        $data = $request->validated();
        $message =  Message::Create($data);
        $user = Auth::user();

        foreach ($data as $key => $value) {
            if ($key == 'to') {
                $userIdInMessageRequest = $value;
            }
        }

        $isAdmin = User::findOrFail($userIdInMessageRequest);

        if ($user->role === 0) {
            broadcast(new EventsMessage(
                $message,
                $isAdmin
            ));
        } else {
            broadcast(new EventsMessage(
                $message,
                $user
            ));
        }

        return $message;
    }
}
