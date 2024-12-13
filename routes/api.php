<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\TransientTokenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
});

Route::controller(UserController::class)->group(function () {
    Route::middleware('auth:api')->get('/users', 'index');
    Route::middleware('auth:api')->get('/users/{id}', 'show');
    Route::post('/users', 'store');
    Route::middleware('auth:api')->put('/users/{id}', 'update');
    Route::middleware('auth:api')->delete('/users/{id}', 'destroy');
});