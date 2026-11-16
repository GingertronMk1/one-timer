<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\ExpiringSecretController::class, 'create'])->name('create');
Route::post('/', [\App\Http\Controllers\ExpiringSecretController::class, 'store'])->name('store');
Route::get('/{expiringSecret}', [\App\Http\Controllers\ExpiringSecretController::class, 'show'])->name('show');
