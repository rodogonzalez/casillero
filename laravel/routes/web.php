<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');

});
Route::get('/request-locker/{$device_id}', [IndexController::class, 'request_locker'] );
Route::get('/start-locker-request/{device_id}/{locker_id}', [IndexController::class, 'start_order'] );


