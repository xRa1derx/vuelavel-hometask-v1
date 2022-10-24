<?php

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Http\Requests\Message\NotifyRequest;
use App\Models\Message;

class NotifyController extends Controller
{
    public function __invoke(NotifyRequest $request)
    {
        $data = $request->validated();
        foreach ($data as $key => $value) {
            if ($key == 'to') {
                $userIdTo = $value;
            }
            if ($key == 'from') {
                $userIdFrom = $value;
            }
        }
        $messages = Message::where('from', $userIdTo)->where('to', $userIdFrom)->get();

        foreach ($messages as $message) {
            $message->timestamps = false;
            $message->update(['is_notified' => $request->is_notified]);
        }
    }
}
