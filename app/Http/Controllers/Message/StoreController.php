<?php

namespace App\Http\Controllers\Message;

use App\Events\Message as EventsMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreRequest;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function __invoke(StoreRequest $request)
    {
        $data = $request->validated();
        $message =  Message::Create($data);
        $user = Auth::user();
        broadcast(new EventsMessage(
            $message,
            $user
        ));
        return $message;
    }
}
