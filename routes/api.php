<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\MedicationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PharmacovigilanceAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::post('/auth/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::post('/login', [PharmacovigilanceAuthController::class, 'login']);

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/medications/search', [MedicationController::class, 'search']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/export/csv', [OrderController::class, 'exportCsv']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::post('/alerts/send', [AlertController::class, 'send']);
    Route::post('/alerts/send-bulk', [AlertController::class, 'sendBulk']);
});

Route::group([
    'prefix' => 'auth',
    'middleware' => ['auth:api', 'role:admin'],
], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});
