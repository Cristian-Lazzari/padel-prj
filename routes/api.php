<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ReservationController;



Route::get('setting',                       [SettingController::class,      'index'])->name('api.setting.index');
Route::get('search_nn',                     [PlayerController::class,       'search_nn'])->name('api.player.search_nn');
Route::get('reservation/get_date',          [ReservationController::class,  'get_date'])->name('api.reservation.get_date');

Route::post('get_reservation',               [ReservationController::class,       'get_reservation'])->name('api.player.get_reservation');
Route::post('login_client',                         [PlayerController::class,       'login_client'])->name('api.player.login_client');
Route::post('verifyOtp',                     [PlayerController::class,       'verifyOtp'])->name('api.player.verifyOtp');
Route::post('register',                      [PlayerController::class,       'register'])->name('api.player.register');
Route::get('client_default',                 [SettingController::class, 'client_default'])->name('api.client_default'); // annullamento tramite mail


