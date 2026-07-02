<?php

use App\Http\Controllers\Api\ProcessApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('/user', function (Request $request) {
        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'locale' => $request->user()->locale,
                'company' => $request->user()->company ? [
                    'id' => $request->user()->company->id,
                    'name' => $request->user()->company->name,
                    'slug' => $request->user()->company->slug,
                ] : null,
            ],
        ]);
    });

    // Processes
    Route::get('/processes', [ProcessApiController::class, 'listProcesses']);
    Route::get('/processes/{process}', [ProcessApiController::class, 'showProcess']);

    // Runs
    Route::get('/runs', [ProcessApiController::class, 'listRuns']);
    Route::post('/runs/start/{process}', [ProcessApiController::class, 'startRun']);
    Route::get('/runs/{run}', [ProcessApiController::class, 'showRun']);
    Route::post('/runs/{run}/answer', [ProcessApiController::class, 'submitAnswer']);
    Route::post('/runs/{run}/pause', [ProcessApiController::class, 'pauseRun']);
    Route::post('/runs/{run}/resume', [ProcessApiController::class, 'resumeRun']);
    Route::post('/runs/{run}/cancel', [ProcessApiController::class, 'cancelRun']);
    Route::get('/runs/{run}/summary', [ProcessApiController::class, 'runSummary']);
});
