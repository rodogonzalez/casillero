<?php

use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;

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
    return redirect('request-locker/' . env('RASPBERRY_DEVICE_ID'));
});
Route::get('request-locker/{device_id}', [IndexController::class, 'request_locker']);
Route::get('start-locker-request/{device_id}/{locker_id}', [IndexController::class, 'start_order'])->name('start');
Route::get('u/{order_id}', [IndexController::class, 'unlock_order'])->name('unlock');
Route::get('woo/u/{order_id}', [IndexController::class, 'unlock_paid_order'])->name('unlock-woo')->middleware('signed');
Route::get('open', [IndexController::class, 'show_open_locker_page']);
Route::get('unlock', [IndexController::class, 'request_open_locker']);
Route::post('pay', [IndexController::class, 'request_payment'])->name('pay');

Route::get('blockbee-callback/{order_id}', [IndexController::class, 'blockbee_callback'])->name('blockbee_callback');
Route::get('payment-status/{order_id}', [IndexController::class, 'payment_status'])->name('payment_status');
//

//Route::get('reset-device-feed/{device_id}', [IndexController::class, 'reset_device_feed'] );
