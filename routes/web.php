<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameSyncController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/manage', [GameController::class, 'index'])->name('manage');
Route::post('/games', [GameController::class, 'store'])->name('games.store');
Route::put('/games/{game}', [GameController::class, 'update'])->name('games.update');
Route::delete('/games/{game}', [GameController::class, 'destroy'])->name('games.destroy');
Route::post('/sync', [GameSyncController::class, 'sync'])->name('sync');
Route::get('/sync/status', [GameSyncController::class, 'lastSync'])->name('sync.status');

require __DIR__.'/auth.php';
