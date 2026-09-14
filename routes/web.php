<?php

use App\Http\Controllers\DebugController;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'can:monitoring.monitoring.lihat'])
    ->get('health', HealthCheckResultsController::class)
    ->name('health');

Route::middleware(['web'])->group(function () {
    // Bisa diakses tanpa login untuk cek reverse proxy & PHP
    Route::get('/admin/system/debug', [DebugController::class, 'index'])
        ->name('debug.pulse');
});

require __DIR__.'/settings.php';
