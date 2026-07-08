<?php

namespace App\Http\Controllers\Api;

use App\Models\TeamMember;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        $members = $project->teamMembers()->get();

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:developer,designer,tester,tech_lead',
            'nivel_experiencia' => 'nullable|in:junior,middle,senior,lead',
            'avatar_url' => 'nullable|string|max:500',
            'estado' => 'nullable|in:disponible,en_tarea,ocupado,fuera',
            'joined_date' => 'nullable|date',
            'git_username' => 'nullable|string|max:100',
            'github_url' => 'nullable|url|max:500',
            'dev_id' => 'nullable|exists:users,id',
        ]);

        $data['project_id'] = $project->id;
        $data['estado'] = $data['estado'] ?? 'disponible';

        $member = $project->teamMembers()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Team member added',
            'data' => $member,
        ], 201);
    }

    public function show(Project $project, TeamMember $member): JsonResponse
    {
        if ($member->project_id !== $project->id) {
            return response()->json(['success' => false, 'message' => 'Member not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $member,
        ]);
    }

    public function update(Request $request, Project $project, TeamMember $member): JsonResponse
    {
        if ($member->project_id !== $project->id) {
            return response()->json(['success' => false, 'message' => 'Member not found'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'role' => 'sometimes|required|in:developer,designer,tester,tech_lead',
            'nivel_experiencia' => 'sometimes|in:junior,middle,senior,lead',
            'avatar_url' => 'nullable|string|max:500',
            'estado' => 'sometimes|in:disponible,en_tarea,ocupado,fuera',
            'joined_date' => 'nullable|date',
            'git_username' => 'nullable|string|max:100',
            'github_url' => 'nullable|url|max:500',
            'dev_id' => 'nullable|exists:users,id',
        ]);

        $member->update($data);

        return response()->json([
            'success' => true,
            'data' => $member->fresh(),
        ]);
    }

    public function destroy(Project $project, TeamMember $member): JsonResponse
    {
        if ($member->project_id !== $project->id) {
            return response()->json(['success' => false, 'message' => 'Member not found'], 404);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team member removed',
        ]);
    }
}
