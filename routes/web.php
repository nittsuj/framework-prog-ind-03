<?php

use App\Agents\DatabaseHealthAgent;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:Admin|Staf'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])
        ->middleware('role:Admin')->name('projects.destroy');
});

// Endpoint JSON Agentic AI database-health (read-only, milik user login).
Route::get('/dashboard/health', function (DatabaseHealthAgent $health) {
    return response()->json($health->analyze());
})->middleware(['auth'])->name('dashboard.health');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
