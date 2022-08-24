<?php

namespace App\Http\Controllers\Admin\Message;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Response;

class IndexController extends Controller
{
    public function __invoke()
    {
        $users = User::all();
        $messages = Message::all();
        return Response::json(array(
            'messages' => $messages,
            'users' => $users
        ));
    }
}
