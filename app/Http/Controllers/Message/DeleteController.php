<?php

namespace App\Http\Controllers\Message;

use App\Events\Message as EventsMessage;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class DeleteController extends Controller
{
    public function __invoke(Message $message)
    {
        $message->delete();
        $user = Auth::user();
        broadcast(new EventsMessage($message, $user));
        return $message;
    }
}
