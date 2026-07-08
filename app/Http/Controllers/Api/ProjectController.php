<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Dashboard statistics across all projects.
     * GET /api/dashboard/stats
     */
    public function dashboardStats(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $stats = [
            'total_projects' => \App\Models\Project::count(),
            'active_projects' => \App\Models\Project::whereIn('status', ['planificacion', 'en_desarrollo', 'en_pruebas'])->count(),
            'completed_projects' => \App\Models\Project::where('status', 'completado')->count(),
            'total_requirements' => \App\Models\Requirement::count(),
            'completed_requirements' => \App\Models\Requirement::where('status', 'completado')->count(),
            'pending_requirements' => \App\Models\Requirement::where('status', 'pendiente')->count(),
            'total_activities' => \App\Models\Activity::count(),
            'activities_completed' => \App\Models\Activity::where('status', 'completada')->count(),
            'total_team_members' => \App\Models\TeamMember::count(),
            'total_dev_logs' => \App\Models\DevelopmentLog::count(),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * Display a listing of projects.
     * GET /api/projects
     */
    public function index(Request $request): JsonResponse
    {
        $query = Project::query()->with(['requirements', 'teamMembers']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('page')) {
            $perPage = $request->input('per_page', 15);
            $projects = $query->paginate($perPage);
        } else {
            $projects = $query->get();
        }

        return response()->json([
            'success' => true,
            'data' => $projects,
            'meta' => isset($projects->total) ? [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ] : [],
        ]);
    }

    /**
     * Store a newly created project.
     * POST /api/projects
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'git_repo_url' => 'nullable|url',
            'status' => 'required|in:planificacion,en_desarrollo,en_pruebas,completado,cancelado',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = Project::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'data' => $project->load('requirements', 'teamMembers'),
        ], 201);
    }

    /**
     * Display a single project.
     * GET /api/projects/{project}
     */
    public function show(Project $project): JsonResponse
    {
        $project->load(['requirements', 'activities', 'teamMembers']);

        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }

    /**
     * Update the specified project.
     * PUT/PATCH /api/projects/{project}
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'git_repo_url' => 'nullable|url',
            'status' => 'sometimes|required|in:planificacion,en_desarrollo,en_pruebas,completado,cancelado',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'data' => $project->fresh(['requirements', 'teamMembers']),
        ]);
    }

    /**
     * Remove the specified project.
     * DELETE /api/projects/{project}
     */
    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
    }

    /**
     * Get statistics for a project.
     * GET /api/projects/{project}/stats
     */
    public function stats(Project $project): JsonResponse
    {
        $stats = [
            'total_requirements' => $project->requirements()->count(),
            'completed_requirements' => $project->requirements()->where('status', 'completado')->count(),
            'in_progress_requirements' => $project->requirements()->where('status', 'en_progreso')->count(),
            'pending_requirements' => $project->requirements()->where('status', 'pendiente')->count(),
            'total_activities' => $project->activities()->count(),
            'activities_completed' => $project->activities()->where('status', 'completada')->count(),
            'team_size' => $project->teamMembers()->count(),
            'total_dev_time_hours' => $project->activities()
                    ->with(['requirement.project', 'developmentLogs'])
                    ->get()
                    ->sum('tiempo_real_horas'),
            'progress' => $project->progreso,
            'progreso' => $project->progreso,
            'overdue_activities' => $project->activities()
                ->where('status', '!=', 'completada')
                ->where('fecha_limite', '<', now())
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
