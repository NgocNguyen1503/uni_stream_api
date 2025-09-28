<?php

use App\Http\Controllers\DemoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\notificationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [LoginController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/list-data', [DemoController::class, 'list']);
    Route::get('/list-notification', [NotificationController::class, 'listNotification']);
    Route::get('/index', [UserController::class, 'index']);
    Route::get('/google-live-auth-url', [UserController::class, 'getGoogleLiveAuthURL']);
    Route::get('/save-token', [UserController::class, 'saveToken']);
    Route::post('/youtube-register-live', [UserController::class, 'youtubeRegisterLive']);
    Route::get('/live-detail', [UserController::class, 'liveDetail']);
});