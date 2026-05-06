<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StatementController;
use Illuminate\Support\Facades\Route;

Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);

Route::post('/users/{id}/transactions', [TransactionController::class, 'store']);
Route::get('/users/{id}/transactions', [TransactionController::class, 'index']);

Route::get('/users/{id}/statement', [StatementController::class, 'show']);
