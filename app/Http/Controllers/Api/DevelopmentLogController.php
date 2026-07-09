<?php

namespace App\Http\Controllers\Api;

use App\Models\DevelopmentLog;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevelopmentLogController extends Controller
{
    public function index(Activity $activity): JsonResponse
    {
        $logs = $activity->developmentLogs()->with('developer')->orderBy('fecha_registro', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function store(Request $request, Activity $activity): JsonResponse
    {
        $data = $request->validate([
            'developer_name' => 'required|string|max:255',
            'developer_email' => 'required|email|max:255',
            'tipo_accion' => 'required|in:commit,fix,feature,review,deploy',
            'descripcion' => 'required|string',
            'tiempo_gastado_minutos' => 'required|numeric|min:0',
            'fecha_registro' => 'nullable|date',
            'link_o_ref' => 'nullable|string|max:500',
            'developer_id' => 'nullable|exists:users,id',
        ]);

        $data['activity_id'] = $activity->id;
        $user = auth()->user();
        if (empty($data['developer_id']) && $user) {
            $data['developer_id'] = $user->id;
            if (empty($data['developer_name'])) {
                $data['developer_name'] = $user->name;
            }
            if (empty($data['developer_email'])) {
                $data['developer_email'] = $user->email;
            }
        }
        if (empty($data['fecha_registro'])) {
            $data['fecha_registro'] = now()->format('Y-m-d');
        }

        $log = $activity->developmentLogs()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Dev log created',
            'data' => $log,
        ], 201);
    }

    public function show(Activity $activity, DevelopmentLog $log): JsonResponse
    {
        if ($log->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Log not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    public function update(Request $request, Activity $activity, DevelopmentLog $log): JsonResponse
    {
        if ($log->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Log not found'], 404);
        }

        $data = $request->validate([
            'developer_name' => 'sometimes|required|string|max:255',
            'developer_email' => 'sometimes|required|email|max:255',
            'tipo_accion' => 'sometimes|required|in:commit,fix,feature,review,deploy',
            'descripcion' => 'sometimes|required|string',
            'tiempo_gastado_minutos' => 'sometimes|numeric|min:0',
            'fecha_registro' => 'nullable|date',
            'link_o_ref' => 'nullable|string|max:500',
            'developer_id' => 'nullable|exists:users,id',
        ]);

        $log->update($data);

        return response()->json([
            'success' => true,
            'data' => $log->fresh(),
        ]);
    }

    public function destroy(Activity $activity, DevelopmentLog $log): JsonResponse
    {
        if ($log->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Log not found'], 404);
        }

        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dev log deleted',
        ]);
    }
}
