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
    return redirect('request-locker/' .  env('RASPBERRY_DEVICE_ID'));

});
Route::get('request-locker/{device_id}', [IndexController::class, 'request_locker'] );
Route::get('start-locker-request/{device_id}/{locker_id}', [IndexController::class, 'start_order'] )->name('start')->middleware('signed');;
Route::get('u/{order_id}', [IndexController::class, 'unlock_order'] )->name('unlock')->middleware('signed');

Route::get('open', [IndexController::class, 'show_open_locker_page'] );
Route::post('open', [IndexController::class, 'request_open_locker'] );


//Route::post('device-feed/{device_id}', [IndexController::class, 'get_device_feed'] );

//Route::get('reset-device-feed/{device_id}', [IndexController::class, 'reset_device_feed'] );
