<?php

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

Route::get('/health', [HealthController::class, 'status']);

Route::middleware(['api', 'auth:sanctum'])->prefix('v1')->group(function () {

    // ---- Projects ----
    Route::apiResource('projects', ProjectController::class);
    Route::get('projects/{project}/stats', [ProjectController::class, 'stats']);

    // ---- Requirements (nested in projects) ----
    Route::apiResource('projects.requirements', RequirementController::class);

    // ---- Requirement Actas (nested in requirements) ----
    Route::get('requirements/{requirement}/actas', [RequirementActaController::class, 'index']);
    Route::post('requirements/{requirement}/actas', [RequirementActaController::class, 'store']);
    Route::get('requirements/{requirement}/actas/{acta}', [RequirementActaController::class, 'show']);
    Route::put('requirements/{requirement}/actas/{acta}', [RequirementActaController::class, 'update']);
    Route::delete('requirements/{requirement}/actas/{acta}', [RequirementActaController::class, 'destroy']);

    // ---- Activities (nested in requirements) ----
    Route::get('requirements/{requirement}/activities', [ActivityController::class, 'index']);
    Route::post('requirements/{requirement}/activities', [ActivityController::class, 'store']);
    Route::get('requirements/{requirement}/activities/{activity}', [ActivityController::class, 'show']);
    Route::put('requirements/{requirement}/activities/{activity}', [ActivityController::class, 'update']);
    Route::delete('requirements/{requirement}/activities/{activity}', [ActivityController::class, 'destroy']);

    // ---- Products (nested in activities) ----
    Route::get('activities/{activity}/products', [ProductController::class, 'index']);
    Route::post('activities/{activity}/products', [ProductController::class, 'store']);
    Route::get('activities/{activity}/products/{product}', [ProductController::class, 'show']);
    Route::put('activities/{activity}/products/{product}', [ProductController::class, 'update']);
    Route::delete('activities/{activity}/products/{product}', [ProductController::class, 'destroy']);

    // ---- Development Logs (nested in activities) ----
    Route::get('activities/{activity}/development-logs', [DevelopmentLogController::class, 'index']);
    Route::post('activities/{activity}/development-logs', [DevelopmentLogController::class, 'store']);
    Route::get('activities/{activity}/development-logs/{log}', [DevelopmentLogController::class, 'show']);
    Route::put('activities/{activity}/development-logs/{log}', [DevelopmentLogController::class, 'update']);
    Route::delete('activities/{activity}/development-logs/{log}', [DevelopmentLogController::class, 'destroy']);

    // ---- Team Members (nested in projects) ----
    Route::get('projects/{project}/team-members', [TeamMemberController::class, 'index']);
    Route::post('projects/{project}/team-members', [TeamMemberController::class, 'store']);
    Route::get('projects/{project}/team-members/{member}', [TeamMemberController::class, 'show']);
    Route::put('projects/{project}/team-members/{member}', [TeamMemberController::class, 'update']);
    Route::delete('projects/{project}/team-members/{member}', [TeamMemberController::class, 'destroy']);

    // ---- Reports / Exports ----
    Route::prefix('reports')->group(function () {
        Route::get('summary', [ReportController::class, 'summary']);
        Route::get('projects/{project}', [ReportController::class, 'report']);
        Route::get('projects/{project}/pdf', [ReportController::class, 'exportPdf']);
        Route::get('projects/{project}/csv', [ReportController::class, 'exportCsv']);
        Route::get('developers/{developer}', [ReportController::class, 'devReport']);
        Route::get('developers/{developer}/pdf', [ReportController::class, 'devExportPdf']);
        Route::get('team', [ReportController::class, 'teamReport']);
        Route::get('team/pdf', [ReportController::class, 'teamExportPdf']);
    });
});
