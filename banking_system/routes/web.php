<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login',[AuthController::class, 'login']);
Route::post('/users',[UserController::class, 'store']);
