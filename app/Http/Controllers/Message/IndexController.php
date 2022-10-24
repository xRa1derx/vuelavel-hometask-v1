<?php

namespace App\Http\Controllers\Message;

use App\Events\Status;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class IndexController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user()->id;
        $users = User::all();
        $messages = Message::all();
        $newMessage = Message::where('is_notified', 0)->get();

        return Response::json(array(
            'messages' => $messages,
            'users' => $users,
            'user' => $user,
            'new_message' => $newMessage
        ));
    }
}
