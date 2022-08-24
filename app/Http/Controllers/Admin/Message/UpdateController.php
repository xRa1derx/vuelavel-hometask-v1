<?php

namespace App\Http\Controllers\Admin\Message;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Message\UpdateRequest;
use App\Models\Message;
use Illuminate\Support\Facades\Response;

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
            $message->update($data);
        } else {
            abort(403, 'Error!!!.');
        }
    }
}
