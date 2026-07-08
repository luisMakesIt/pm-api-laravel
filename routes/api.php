<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RequirementController;
use App\Http\Controllers\Api\RequirementActaController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\DevelopmentLogController;
use App\Http\Controllers\Api\TeamMemberController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All API routes prefixed with /api and protected with Sanctum auth
| except health check and token refresh
|
*/

// Health check (no auth)
Route::get('/health', [HealthController::class, 'status']);
Route::post('/refresh-token', [HealthController::class, 'refreshToken']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {

    // Projects CRUD
    Route::apiResource('projects', ProjectController::class);
    Route::get('projects/{project}/stats', [ProjectController::class, 'stats']);

    // Requirements CRUD
    Route::apiResource('projects.requirements', RequirementController::class)->scoped();
    Route::patch('requirements/{requirement}/status', [RequirementController::class, 'updateStatus']);

    // Requirement Actas CRUD
    Route::apiResource('requirements.actas', RequirementActaController::class)->scoped(['require' => 'requirement']);
    Route::post('actas/{acta}/update-firmas', [RequirementActaController::class, 'updateFirmas']);

    // Activities CRUD
    Route::apiResource('requirements.activities', ActivityController::class)->scoped();
    Route::apiResource('projects.activities', ActivityController::class)->scoped();
    Route::patch('activities/{activity}/status', [ActivityController::class, 'updateStatus']);
    Route::put('activities/{activity}/time', [ActivityController::class, 'updateTime']);

    // Products CRUD
    Route::apiResource('activities.products', ProductController::class)->scoped();

    // Development Logs CRUD
    Route::apiResource('activities.development-logs', DevelopmentLogController::class)->scoped();

    // Team Members CRUD
    Route::apiResource('projects.team-members', TeamMemberController::class)->scoped();

    // Reports / Exports
    Route::prefix('reports')->group(function () {
        // Project report
        Route::get('projects/{project}', [ReportController::class, 'report']);
        Route::get('projects/{project}/pdf', [ReportController::class, 'exportPdf']);
        Route::get('projects/{project}/csv', [ReportController::class, 'exportCsv']);

        // Developer report
        Route::get('developers/{developer}', [ReportController::class, 'devReport']);
        Route::get('developers/{developer}/pdf', [ReportController::class, 'devExportPdf']);

        // Team / overview report
        Route::get('team', [ReportController::class, 'teamReport']);
        Route::get('team/pdf', [ReportController::class, 'teamExportPdf']);

        // Project summary
        Route::get('summary', [ReportController::class, 'summary']);
    });
});
