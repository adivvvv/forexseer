<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AssetController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/forex/{symbol}',  [AssetController::class, 'show'])->name('forex.show');
Route::get('/stocks/{symbol}', [AssetController::class, 'show'])->name('stocks.show');
Route::get('/crypto/{symbol}', [AssetController::class, 'show'])->name('crypto.show');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
