<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/users',[UserController::class, 'store']);
Route::post('/login',[AuthController::class, 'login']);

Route::group(['middleware' => ['auth']] , function(){
    Route::get('dashboard', [AuthController::class, 'dashbboard'])->name('dashboard');
});
Route::get('/show', [TransactionController::class, 'show']);
Route::get('/deposit', [TransactionController::class, 'depositTransactionShow']);
Route::post('/deposit', [TransactionController::class, 'depositTransaction']);
Route::get('/withdrawal', [TransactionController::class, 'withdrawalTransactionShow']);
