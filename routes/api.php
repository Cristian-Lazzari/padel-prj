<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SettingController;



Route::get('setting',           [SettingController::class, 'index'])->name('api.setting.index');


