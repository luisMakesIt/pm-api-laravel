<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
    /**
     * Display a listing of requirements for a project.
     * GET /api/projects/{project}/requirements
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        $query = $project->requirements()->with(['actas', 'activities']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('per_page')) {
            $perPage = $request->per_page;
            $requirements = $query->paginate($perPage);
        } else {
            $requirements = $query->get();
        }

        return response()->json([
            'success' => true,
            'data' => $requirements,
            'meta' => isset($requirements->total) ? [
                'current_page' => $requirements->currentPage(),
                'last_page' => $requirements->lastPage(),
                'per_page' => $requirements->perPage(),
                'total' => $requirements->total(),
            ] : [],
        ]);
    }

    /**
     * Store a newly created requirement.
     * POST /api/projects/{project}/requirements
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:alta,media,baja',
            'status' => 'nullable|in:pendiente,en_progreso,completado,rechazado',
        ]);

        $data['project_id'] = $project->id;
        $data['status'] = $data['status'] ?? 'pendiente';
        $data['priority'] = $data['priority'] ?? 'media';

        $requirement = $project->requirements()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Requirement created successfully',
            'data' => $requirement->load(['actas', 'activities']),
        ], 201);
    }

    /**
     * Display a specific requirement.
     * GET /api/projects/{project}/requirements/{requirement}
     */
    public function show(Project $project, Requirement $requirement): JsonResponse
    {
        if ($requirement->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Requirement does not belong to this project',
            ], 404);
        }

        $requirement->load(['project', 'actas', 'activities']);

        return response()->json([
            'success' => true,
            'data' => $requirement,
        ]);
    }

    /**
     * Update the specified requirement.
     * PUT/PATCH /api/projects/{project}/requirements/{requirement}
     */
    public function update(Request $request, Project $project, Requirement $requirement): JsonResponse
    {
        if ($requirement->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Requirement does not belong to this project',
            ], 404);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:alta,media,baja',
            'status' => 'sometimes|in:pendiente,en_progreso,completado,rechazado',
        ]);

        $requirement->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Requirement updated successfully',
            'data' => $requirement->fresh(['actas', 'activities']),
        ]);
    }

    /**
     * Remove the specified requirement.
     * DELETE /api/projects/{project}/requirements/{requirement}
     */
    public function destroy(Project $project, Requirement $requirement): JsonResponse
    {
        if ($requirement->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Requirement does not belong to this project',
            ], 404);
        }

        $requirement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Requirement deleted successfully',
        ]);
    }

    /**
     * Update requirement status.
     * PATCH /api/requirements/{requirement}/status
     */
    public function updateStatus(Request $request, Requirement $requirement): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pendiente,en_progreso,completado,rechazado',
        ]);

        $requirement->update(['status' => $data['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Requirement status updated',
            'data' => $requirement->fresh(),
        ]);
    }
}
