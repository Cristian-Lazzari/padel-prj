<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ReservationController;



Route::get('setting',                       [SettingController::class,      'index'])->name('api.setting.index');
Route::get('reservation/get_date',          [ReservationController::class,  'get_date'])->name('api.reservation.get_date');
Route::get('search_nn',                     [PlayerController::class,       'search_nn'])->name('api.player.search_nn');
Route::get('login',                         [PlayerController::class,       'login'])->name('api.player.login');
Route::get('verifyOtp',                     [PlayerController::class,       'verifyOtp'])->name('api.player.verifyOtp');


