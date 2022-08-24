<?php

namespace App\Http\Controllers\Admin\Message;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Message\StoreRequest;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Response;

class StoreController extends Controller
{
    public function __invoke(StoreRequest $request)
    {
        $data = $request->validated();
        $message =  Message::Create($data);
        return $message;
    }
}
