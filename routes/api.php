<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

/*
    Auth routes
*/
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::get('me', [AuthController::class, 'me']);
Route::post('refreshToken', [AuthController::class, 'refreshToken']);

/*
    Request routes
 */
Route::prefix('request')->middleware('jwt.verify')->group(function () {
    Route::get('all', [RequestController::class, 'index']);
    Route::get('get/{id}', [RequestController::class, 'show'])->where('id', '[0-9]+');
    Route::post('create', [RequestController::class, 'store']);
    Route::put('edit/{id}', [RequestController::class, 'update'])->where('id', '[0-9]+');
    Route::put('changeStatus/{id}', [RequestController::class, 'updateStatusHR'])->where('id', '[0-9]+');
    Route::put('complete/{id}', [RequestController::class, 'updateStatusManager'])->where('id', '[0-9]+');
});
