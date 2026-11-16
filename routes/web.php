<?php

use App\Http\Controllers\ExpiringSecretController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ExpiringSecretController::class, 'create'])->name('create');
Route::post('/', [ExpiringSecretController::class, 'store'])->name('store');
Route::get('/{expiringSecret}', [ExpiringSecretController::class, 'show'])->name('show');
