<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function __invoke()
    {
        $users = User::all();
        $user = Auth::user()->id;
        return Response::json(array(
            'users' => $users,
            'user' => $user
        ));
    }
}
