<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/users', [UserController::class, 'index']);
Route::post('/users/register', [UserController::class, 'create']);
Route::post('/users/login', [UserController::class, 'login']);
Route::put('/users/update-name', [UserController::class, 'updateName']);