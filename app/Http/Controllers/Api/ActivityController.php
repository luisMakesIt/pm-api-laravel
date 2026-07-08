<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\Requirement;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request, Requirement $requirement): JsonResponse
    {
        if ($request->filled('page')) {
            $perPage = $request->input('per_page', 15);
            $activities = $requirement->activities()->with(['assignedTo', 'products', 'developmentLogs'])->paginate($perPage);
        } else {
            $activities = $requirement->activities()->with(['assignedTo', 'products', 'developmentLogs'])->get();
        }

        return response()->json([
            'success' => true,
            'data' => $activities,
            'meta' => isset($activities->total) ? [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ] : [],
        ]);
    }

    public function store(Request $request, Requirement $requirement): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pendiente,en_progreso,completada,bloqueada',
            'fecha_inicio_planificada' => 'nullable|date',
            'fecha_limite' => 'nullable|date',
            'tiempo_estimado_horas' => 'nullable|numeric|min:0',
            'tiempo_real_horas' => 'nullable|numeric|min:0',
            'asignado_a' => 'nullable|exists:users,id',
        ]);

        $data['requirement_id'] = $requirement->id;
        $data['status'] = $data['status'] ?? 'pendiente';

        $activity = $requirement->activities()->create($data);
        $activity->load(['assignedTo', 'products', 'developmentLogs']);

        return response()->json([
            'success' => true,
            'message' => 'Activity created',
            'data' => $activity,
        ], 201);
    }

    public function show(Requirement $requirement, Activity $activity): JsonResponse
    {
        if ($activity->requirement_id !== $requirement->id) {
            return response()->json(['success' => false, 'message' => 'Activity not found'], 404);
        }

        $activity->load(['requirement.project', 'assignedTo', 'products', 'developmentLogs']);

        return response()->json([
            'success' => true,
            'data' => $activity,
        ]);
    }

    public function update(Request $request, Requirement $requirement, Activity $activity): JsonResponse
    {
        if ($activity->requirement_id !== $requirement->id) {
            return response()->json(['success' => false, 'message' => 'Activity not found'], 404);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pendiente,en_progreso,completada,bloqueada',
            'fecha_inicio_planificada' => 'nullable|date',
            'fecha_limite' => 'nullable|date',
            'tiempo_estimado_horas' => 'nullable|numeric|min:0',
            'tiempo_real_horas' => 'nullable|numeric|min:0',
            'asignado_a' => 'nullable|exists:users,id',
        ]);

        $activity->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Activity updated',
            'data' => $activity->fresh(['assignedTo', 'products', 'developmentLogs']),
        ]);
    }

    public function destroy(Requirement $requirement, Activity $activity): JsonResponse
    {
        if ($activity->requirement_id !== $requirement->id) {
            return response()->json(['success' => false, 'message' => 'Activity not found'], 404);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activity deleted',
        ]);
    }

    public function updateStatus(Request $request, Activity $activity): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pendiente,en_progreso,completada,bloqueada',
        ]);

        $activity->update(['status' => $data['status']]);

        return response()->json([
            'success' => true,
            'data' => $activity->fresh(),
        ]);
    }

    public function updateTime(Request $request, Activity $activity): JsonResponse
    {
        $data = $request->validate([
            'tiempo_estimado_horas' => 'nullable|numeric|min:0',
            'tiempo_real_horas' => 'nullable|numeric|min:0',
        ]);

        $activity->update($data);

        return response()->json([
            'success' => true,
            'data' => $activity->fresh(),
        ]);
    }
}
