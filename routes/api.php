<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['middleware' => 'auth:sanctum'],function (){
   Route::get('/', 'IndexController') ;
});

Route::group(['prefix' => 'admin/users/chat', 'namespace' => 'Admin\Message'], function () {
    Route::get('/', 'IndexController');
    Route::post('/', 'StoreController');
    Route::delete('/{message}', 'DeleteController');
    Route::patch('/{message}', 'UpdateController');
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'admin/users', 'namespace' => 'Admin\User'], function () {
    Route::get('/', 'IndexController');
    Route::post('/create', 'StoreController');
    Route::patch('/{user}', 'UpdateController');
});