<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use App\Models\RequirementActa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequirementActaController extends Controller
{
    /**
     * Display actas for a requirement.
     * GET /api/requirements/{requirement}/actas
     */
    public function index(Requirement $requirement): JsonResponse
    {
        $actas = $requirement->actas()->with('requirement')->get();

        return response()->json([
            'success' => true,
            'data' => $actas,
        ]);
    }

    /**
     * Store a new acta for a requirement.
     * POST /api/requirements/{requirement}/actas
     */
    public function store(Request $request, Requirement $requirement): JsonResponse
    {
        $data = $request->validate([
            'fecha_sesion' => 'nullable|date',
            'cliente_nombre' => 'required|string|max:255',
            'cliente_email' => 'required|email|max:255',
            'cliente_empresa' => 'required|string|max:255',
            'participantes' => 'nullable|array',
            'notas' => 'nullable|string',
            'firmas' => 'nullable|string',
            'acuerdos' => 'nullable|array',
            'fecha_firma_acta' => 'nullable|date',
            'estado_firma' => 'nullable|in:sin_firmar,esperando_firma,firmado,archivado',
        ]);

        $data['requirement_id'] = $requirement->id;

        if (!isset($data['estado_firma'])) {
            $data['estado_firma'] = 'sin_firmar';
        }

        $acta = RequirementActa::create($data);
        $acta->load('requirement');

        return response()->json([
            'success' => true,
            'message' => 'Acta created successfully',
            'data' => $acta,
        ], 201);
    }

    /**
     * Display a specific acta.
     * GET /api/requirements/{requirement}/actas/{acta}
     */
    public function show(Requirement $requirement, RequirementActa $acta): JsonResponse
    {
        $acta->load('requirement');

        return response()->json([
            'success' => true,
            'data' => $acta,
        ]);
    }

    /**
     * Update the specified acta.
     * PUT/PATCH /api/requirements/{requirement}/actas/{acta}
     */
    public function update(Request $request, Requirement $requirement, RequirementActa $acta): JsonResponse
    {
        $data = $request->validate([
            'fecha_sesion' => 'nullable|date',
            'cliente_nombre' => 'sometimes|required|string|max:255',
            'cliente_email' => 'sometimes|required|email|max:255',
            'cliente_empresa' => 'sometimes|required|string|max:255',
            'participantes' => 'nullable|array',
            'notas' => 'nullable|string',
            'firmas' => 'nullable|string',
            'acuerdos' => 'nullable|array',
            'fecha_firma_acta' => 'nullable|date',
            'estado_firma' => 'nullable|in:sin_firmar,esperando_firma,firmado,archivado',
        ]);

        $acta->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Acta updated successfully',
            'data' => $acta->fresh(),
        ]);
    }

    /**
     * Remove the specified acta.
     * DELETE /api/requirements/{requirement}/actas/{acta}
     */
    public function destroy(Requirement $requirement, RequirementActa $acta): JsonResponse
    {
        $acta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Acta deleted successfully',
        ]);
    }

    /**
     * Update firma status.
     * POST /api/actas/{acta}/update-firmas
     */
    public function updateFirmas(Request $request, RequirementActa $acta): JsonResponse
    {
        $data = $request->validate([
            'firmas' => 'nullable|string',
            'fecha_firma_acta' => 'nullable|date',
            'estado_firma' => 'required|in:sin_firmar,esperando_firma,firmado,archivado',
        ]);

        $acta->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Firma status updated',
            'data' => $acta->fresh(),
        ]);
    }
}
