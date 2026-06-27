<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\RunController;
use Illuminate\Support\Facades\Route;

// Landing
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Locale
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Auth (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Processes (admin/team_lead can manage, all can view active)
    Route::get('/processes', [ProcessController::class, 'index'])->name('processes.index');
    Route::get('/processes/{process}', [ProcessController::class, 'show'])->name('processes.show');

    Route::middleware('role:admin,team_lead')->group(function () {
        Route::get('/processes-create', [ProcessController::class, 'create'])->name('processes.create');
        Route::post('/processes', [ProcessController::class, 'store'])->name('processes.store');
        Route::get('/processes/{process}/edit', [ProcessController::class, 'edit'])->name('processes.edit');
        Route::put('/processes/{process}', [ProcessController::class, 'update'])->name('processes.update');
        Route::delete('/processes/{process}', [ProcessController::class, 'destroy'])->name('processes.destroy');
        Route::post('/processes/{process}/activate', [ProcessController::class, 'activate'])->name('processes.activate');
        Route::post('/processes/{process}/archive', [ProcessController::class, 'archive'])->name('processes.archive');

        // Steps
        Route::post('/processes/{process}/steps', [ProcessController::class, 'storeStep'])->name('steps.store');
        Route::put('/processes/{process}/steps/{step}', [ProcessController::class, 'updateStep'])->name('steps.update');
        Route::delete('/processes/{process}/steps/{step}', [ProcessController::class, 'destroyStep'])->name('steps.destroy');
        Route::post('/processes/{process}/steps/reorder', [ProcessController::class, 'reorderSteps'])->name('steps.reorder');

        // Options
        Route::post('/steps/{step}/options', [ProcessController::class, 'storeOption'])->name('options.store');
        Route::delete('/options/{option}', [ProcessController::class, 'destroyOption'])->name('options.destroy');

        // Transitions
        Route::post('/steps/{step}/transitions', [ProcessController::class, 'storeTransition'])->name('transitions.store');
        Route::delete('/transitions/{transition}', [ProcessController::class, 'destroyTransition'])->name('transitions.destroy');
    });

    // Runs
    Route::get('/runs', [RunController::class, 'index'])->name('runs.index');
    Route::post('/runs/start/{process}', [RunController::class, 'start'])->name('runs.start');
    Route::get('/runs/{run}', [RunController::class, 'execute'])->name('runs.execute');
    Route::post('/runs/{run}/answer', [RunController::class, 'answer'])->name('runs.answer');
    Route::post('/runs/{run}/pause', [RunController::class, 'pause'])->name('runs.pause');
    Route::post('/runs/{run}/resume', [RunController::class, 'resume'])->name('runs.resume');
    Route::post('/runs/{run}/cancel', [RunController::class, 'cancel'])->name('runs.cancel');
    Route::get('/runs/{run}/summary', [RunController::class, 'summary'])->name('runs.summary');

    // Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
        Route::get('/audit-log', [AdminController::class, 'auditLog'])->name('admin.audit');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    });
});
