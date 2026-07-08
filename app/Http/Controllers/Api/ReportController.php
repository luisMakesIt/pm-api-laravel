<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Activity;
use App\Models\Requirement;
use App\Models\DevelopmentLog;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.url', 'http://localhost');
    }

    // ============================================================
    // REPORT: Project Summary
    // ============================================================
    public function summary(Request $request): JsonResponse
    {
        $query = Project::query();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $totalProjects = Project::count();
        $projectsByStatus = Project::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalRequirements = Requirement::count();
        $reqByStatus = Requirement::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalDevLogs = DevelopmentLog::count();
        $devLogByType = DevelopmentLog::selectRaw('tipo_accion, COUNT(*) as count, SUM(tiempo_gastado_minutos) as total_minutes')
            ->groupBy('tipo_accion')
            ->get()
            ->toArray();

        $totalActivities = Activity::count();
        $actByStatus = Activity::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return response()->json([
            'success' => true,
            'summary' => [
                'projects' => [
                    'total' => $totalProjects,
                    'by_status' => $projectsByStatus,
                ],
                'requirements' => [
                    'total' => $totalRequirements,
                    'by_status' => $reqByStatus,
                ],
                'activities' => [
                    'total' => $totalActivities,
                    'by_status' => $actByStatus,
                ],
                'development_logs' => [
                    'total' => $totalDevLogs,
                    'by_type' => $devLogByType,
                ],
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    // ============================================================
    // REPORT: Individual Project Full Report
    // ============================================================
    public function report(Project $project): JsonResponse
    {
        $project->load([
            'requirements.activities',
            'requirements.actas',
            'requirements.activities.products',
            'requirements.activities.developmentLogs',
            'teamMembers',
        ]);

        $totalDevTime = $project->activities()->with('developmentLogs')->get()
            ->sum(fn($a) => $a->tiempo_real_horas
                + ($a->developmentLogs->sum('tiempo_gastado_minutos') / 60));

        return response()->json([
            'success' => true,
            'report' => [
                'project' => $project,
                'total_activities' => $project->activities()->count(),
                'total_requirements' => $project->requirements()->count(),
                'team_size' => $project->teamMembers()->count(),
                'total_dev_time_hours' => round($totalDevTime, 2),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    // ============================================================
    // EXPORT: Project CSV
    // ============================================================
    public function exportCsv(Project $project): JsonResponse
    {
        $project->load([
            'requirements.activities.products',
            'requirements.actas',
            'requirements.activities.developmentLogs',
            'teamMembers',
        ]);

        $csvData = [];
        $csvData[] = [
            'Project', 'Requirement', 'Activity', 'Product', 'Type',
            'Dev Log', 'Dev Name', 'Action', 'Time Min', 'Dev Member', 'Role'
        ];

        foreach ($project->requirements as $req) {
            if ($req->activities->isEmpty()) {
                $csvData[] = [
                    $project->name, $req->title, '', '', '',
                    '', '', '', '', '', ''
                ];
            }

            foreach ($req->activities as $act) {
                $products = $act->products->map(fn($p) => $p->name)->join(', ');
                $devLogs = $act->developmentLogs;
                $devLogStr = $devLogs->isEmpty() ? '-' : '';

                foreach ($devLogs as $log) {
                    $csvData[] = [
                        $project->name,
                        $req->title,
                        $act->title,
                        $products,
                        '',
                        $log->tipo_accion . ': ' . substr($log->descripcion, 0, 100),
                        $log->developer_name,
                        $log->tipo_accion,
                        $log->tiempo_gastado_minutos,
                        '',
                        '',
                    ];
                }

                if ($devLogs->isEmpty()) {
                    $csvData[] = [
                        $project->name,
                        $req->title,
                        $act->title,
                        $products,
                        '',
                        'N/A',
                        'N/A',
                        'N/A',
                        0,
                        '',
                        '',
                    ];
                }
            }
        }

        $filename = "project_{$project->id}_report.csv";
        $path = 'reports/' . $filename;

        Storage::disk('public')->put($path, $this->arrayToCsv($csvData));

        return response()->json([
            'success' => true,
            'message' => 'CSV generated',
            'download_url' => $this->baseUrl . '/storage/reports/' . $filename,
            'filename' => $filename,
        ]);
    }

    // ============================================================
    // EXPORT: Project PDF (via DomPDF)
    // ============================================================
    public function exportPdf(Project $project): JsonResponse
    {
        // Since DomPDF may not be fully loaded in container, return JSON
        // In production, return PDF like: return PDF::loadView('pdf.project', [...])->download(...)

        $project->load([
            'requirements.activities',
            'requirements.actas',
            'teamMembers',
        ]);

        $html = $this->generateProjectPdfContent($project);

        // Try to use DomPDF, fall back to JSON with HTML
        try {
            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.project', compact('project'));
                $pdf->setOptions(['isRemoteEnabled' => true]);
                return response($pdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="project_' . $project->id . '_report.pdf"',
                ]);
            }
        } catch (\Exception $e) {
            // Fall through to JSON with HTML preview
        }

        return response()->json([
            'success' => true,
            'message' => 'PDF generated (rendered as HTML fallback)',
            'content_type' => 'html',
            'html_preview' => $html,
        ]);
    }

    // ============================================================
    // REPORT: Developer Performance Report
    // ============================================================
    public function devReport(Request $request, $developerId): JsonResponse
    {
        $user = User::find($developerId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Developer not found',
            ], 404);
        }

        $devLogs = DevelopmentLog::where('developer_id', $developerId)
            ->with(['activity.requirement.project', 'activity'])
            ->orderBy('fecha_registro', 'desc')
            ->get();

        $stats = [
            'total_logs' => $devLogs->count(),
            'total_minutes' => $devLogs->sum('tiempo_gastado_minutos'),
            'total_hours' => round($devLogs->sum('tiempo_gastado_minutos') / 60, 2),
            'by_action' => $devLogs->groupBy('tipo_accion')->map(fn($logs) => [
                'count' => $logs->count(),
                'total_minutes' => $logs->sum('tiempo_gastado_minutos'),
            ])->toArray(),
            'by_project' => $devLogs->map(fn($log) => $log->activity->requirement->project)
                ->filter()
                ->groupBy('name')
                ->map(fn($projLogs) => [
                    'activities' => $projLogs->groupBy(fn($l) => $l->activity->id)
                        ->keys()
                        ->count(),
                    'total_minutes' => $projLogs->sum('tiempo_gastado_minutos'),
                ])->toArray(),
            'recent_logs' => $devLogs->take(20),
            'dev' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];

        return response()->json([
            'success' => true,
            'report' => $stats,
        ]);
    }

    public function devExportPdf($developerId): JsonResponse
    {
        $user = User::find($developerId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Developer not found',
            ], 404);
        }

        $devLogs = DevelopmentLog::where('developer_id', $developerId)
            ->with(['activity.requirement.project'])
            ->orderBy('fecha_registro', 'desc')
            ->get();

        $stats = $this->computeDevStats($devLogs);

        $html = $this->generateDevPdfContent($user, $devLogs, $stats);

        try {
            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.developer', compact('user', 'devLogs', 'stats'));
                $pdf->setOptions(['isRemoteEnabled' => true]);
                return response($pdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="developer_' . $developerId . '_report.pdf"',
                ]);
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return response()->json([
            'success' => true,
            'content_type' => 'html',
            'html_preview' => $html,
        ]);
    }

    // ============================================================
    // REPORT: Team / Overview
    // ============================================================
    public function teamReport(): JsonResponse
    {
        $teamMembers = TeamMember::with('project')->get();

        $projects = Project::with(['requirements', 'activities', 'teamMembers'])->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'status' => $project->status,
                    'progreso' => $project->progreso,
                    'total_requirements' => $project->requirements()->count(),
                    'completed_requirements' => $project->requirements()->where('status', 'completado')->count(),
                    'pending_activities' => $project->activities()->where('status', 'pendiente')->count(),
                    'completed_activities' => $project->activities()->where('status', 'completada')->count(),
                    'in_progress_activities' => $project->activities()->where('status', 'en_progreso')->count(),
                    'blocked_activities' => $project->activities()->where('status', 'bloqueada')->count(),
                    'overdue_activities' => $project->activities()
                        ->where('status', '!=', 'completada')
                        ->where('fecha_limite', '<', now())
                        ->count(),
                    'team_size' => $project->teamMembers()->count(),
                    'total_dev_minutes' => $project->activities()->with('developmentLogs')
                        ->get()->sum(fn($a) => $a->developmentLogs->sum('tiempo_gastado_minutos')),
                ];
            });

        $teamByRole = $teamMembers->groupBy('role')
            ->map(fn($members) => [
                'count' => $members->count(),
                'by_status' => $members->groupBy('estado')
                    ->map(fn($m) => $m->count())
                    ->toArray(),
            ])
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'projects' => $projects,
                'team_by_role' => $teamByRole,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function teamExportPdf(): JsonResponse
    {
        $projects = Project::with(['requirements', 'activities', 'teamMembers'])->get();

        $html = $this->generateTeamPdfContent($projects);

        try {
            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.team-summary', compact('projects'));
                $pdf->setOptions(['isRemoteEnabled' => true]);
                return response($pdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="team_report.pdf"',
                ]);
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return response()->json([
            'success' => true,
            'content_type' => 'html',
            'html_preview' => $html,
        ]);
    }

    // ============================================================
    // HELPER: Generate PDF HTML content
    // ============================================================
    protected function generateProjectPdfContent(Project $project): string
    {
        $html = '<html><head><meta charset="utf-8"><style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        h2 { color: #2c3e50; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #3498db; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .status-planificacion { color: #95a5a6; }
        .status-en_desarrollo { color: #3498db; }
        .status-en_pruebas { color: #f39c12; }
        .status-completado { color: #27ae60; }
        .status-cancelado { color: #e74c3c; }
        .meta { margin: 10px 0; color: #555; }
        </style></head><body>';
        $html .= '<h1>Project Report: ' . e($project->name) . '</h1>';
        $html .= '<p class="meta">Generated: ' . now()->format('Y-m-d H:i:s') . '</p>';
        $html .= '<p><strong>Status:</strong> <span class="status-' . $project->status . '">' . e($project->StatusLabel) . '</span></p>';
        $html .= '<p><strong>Progress:</strong> ' . $project->progreso . '%</p>';

        if ($project->description) {
            $html .= '<p><strong>Description:</strong> ' . e($project->description) . '</p>';
        }
        if ($project->git_repo_url) {
            $html .= '<p><strong>Git Repo:</strong> <a href="' . $project->git_repo_url . '">' . $project->git_repo_url . '</a></p>';
        }

        // Requirements
        $html .= '<h2>Requirements (' . $project->requirements->count() . ')</h2>';
        if ($project->requirements->isEmpty()) {
            $html .= '<p>No requirements.</p>';
        } else {
            $html .= '<table><tr><th>ID</th><th>Title</th><th>Priority</th><th>Status</th><th>Completion</th></tr>';
            foreach ($project->requirements as $req) {
                $html .= '<tr>';
                $html .= '<td>' . $req->id . '</td>';
                $html .= '<td>' . e($req->title) . '</td>';
                $html .= '<td>' . e($req->priority_label) . '</td>';
                $html .= '<td>' . e($req->status_label) . '</td>';
                $html .= '<td>' . $req->required_completion_rate . '%</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        // Activities
        $html .= '<h2>Activities (' . $project->activities->count() . ')</h2>';
        if ($project->activities->isEmpty()) {
            $html .= '<p>No activities.</p>';
        } else {
            $html .= '<table><tr><th>Req</th><th>Activity</th><th>Status</th><th>Assigned</th><th>Est. Hrs</th><th>Real Hrs</th><th>Deadline</th></tr>';
            foreach ($project->activities as $act) {
                $reqName = $act->requirement ? $act->requirement->title : '';
                $assigned = $act->assignedTo ? $act->assignedTo->name : '-';
                $html .= '<tr>';
                $html .= '<td>' . e($reqName) . '</td>';
                $html .= '<td>' . e($act->title) . '</td>';
                $html .= '<td>' . e($act->status_label) . '</td>';
                $html .= '<td>' . e($assigned) . '</td>';
                $html .= '<td>' . $act->tiempo_estimado_horas . '</td>';
                $html .= '<td>' . $act->tiempo_real_horas . '</td>';
                $html .= '<td>' . ($act->fecha_limite ? $act->fecha_limite->format('Y-m-d') : '-') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        // Team members
        $html .= '<h2>Team Members (' . $project->teamMembers->count() . ')</h2>';
        if ($project->teamMembers->isEmpty()) {
            $html .= '<p>No team members.</p>';
        } else {
            $html .= '<table><tr><th>Name</th><th>Email</th><th>Role</th><th>Level</th><th>Status</th></tr>';
            foreach ($project->teamMembers as $member) {
                $html .= '<tr>';
                $html .= '<td>' . e($member->name) . '</td>';
                $html .= '<td>' . e($member->email) . '</td>';
                $html .= '<td>' . e($member->role_label) . '</td>';
                $html .= '<td>' . e($member->nivel_experiencia) . '</td>';
                $html .= '<td>' . e($member->status_label) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        $html .= '<br><p style="color:#999;font-size:10px;page-break-before:always;">Report generated by PM API v1.0</p></body></html>';
        return $html;
    }

    protected function generateDevPdfContent($user, $devLogs, $stats): string
    {
        $html = '<html><head><meta charset="utf-8"><style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        h1 { color: #2c3e50; border-bottom: 2px solid #27ae60; padding-bottom: 10px; }
        h2 { color: #2c3e50; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #27ae60; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        </style></head><body>';
        $html .= '<h1>Developer Report: ' . e($user->name) . '</h1>';
        $html .= '<p><strong>Email:</strong> ' . e($user->email) . '</p>';
        $html .= '<p><strong>Total Hours:</strong> ' . $stats['total_hours'] . ' hrs</p>';
        $html .= '<p><strong>Total Logs:</strong> ' . $stats['total_logs'] . '</p>';

        if (!empty($stats['by_action'])) {
            $html .= '<h2>Time by Action</h2><table><tr><th>Action</th><th>Count</th><th>Minutes</th></tr>';
            foreach ($stats['by_action'] as $type => $data) {
                $html .= '<tr><td>' . e($type) . '</td><td>' . $data['count'] . '</td><td>' . $data['total_minutes'] . '</td></tr>';
            }
            $html .= '</table>';
        }

        if (!empty($stats['by_project'])) {
            $html .= '<h2>Time by Project</h2><table><tr><th>Project</th><th>Activities</th><th>Minutes</th></tr>';
            foreach ($stats['by_project'] as $name => $data) {
                $html .= '<tr><td>' . e($name) . '</td><td>' . $data['activities'] . '</td><td>' . $data['total_minutes'] . '</td></tr>';
            }
            $html .= '</table>';
        }

        $html .= '<h2>Recent Activity Logs</h2>';
        if ($devLogs->isEmpty()) {
            $html .= '<p>No logs found.</p>';
        } else {
            $html .= '<table><tr><th>Date</th><th>Action</th><th>Activity</th><th>Project</th><th>Time (min)</th><th>Link</th></tr>';
            foreach ($devLogs->take(50) as $log) {
                $actName = $log->activity ? $log->activity->title : '';
                $projName = $log->activity && $log->activity->requirement ? ($log->activity->requirement->project ? $log->activity->requirement->project->name : '') : '';
                $html .= '<tr>';
                $html .= '<td>' . ($log->fecha_registro ? $log->fecha_registro->format('Y-m-d') : '-') . '</td>';
                $html .= '<td>' . e($log->tipo_acc_label) . '</td>';
                $html .= '<td>' . e($actName) . '</td>';
                $html .= '<td>' . e($projName) . '</td>';
                $html .= '<td>' . $log->tiempo_gastado_minutos . '</td>';
                $html .= '<td>' . ($log->link_o_ref ? substr($log->link_o_ref, 0, 50) : '-') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        $html .= '<br><p style="color:#999;font-size:10px;page-break-before:always;">Report generated by PM API v1.0</p></body></html>';
        return $html;
    }

    protected function generateTeamPdfContent($projects): string
    {
        $html = '<html><head><meta charset="utf-8"><style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        h1 { color: #2c3e50; border-bottom: 2px solid #9b59b6; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #9b59b6; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        </style></head><body>';
        $html .= '<h1>Team Overview Report</h1>';
        $html .= '<p>Generated: ' . now()->format('Y-m-d H:i:s') . '</p>';
        $html .= '<p>Total Projects: ' . $projects->count() . '</p>';

        $html .= '<h2>Project Summary</h2><table><tr><th>ID</th><th>Name</th><th>Status</th><th>Progress</th><th>Requirements</th><th>Completed Req</th><th>Activities</th><th>Done</th><th>Blocked</th><th>Overdue</th><th>Team</th></tr>';
        foreach ($projects as $proj) {
            $actCount = $proj->activities()->count();
            $actDone = $proj->activities()->where('status', 'completada')->count();
            $actBlocked = $proj->activities()->where('status', 'bloqueada')->count();
            $actOverdue = $proj->activities()->where('status', '!=', 'completada')->where('fecha_limite', '<', now())->count();

            $html .= '<tr>';
            $html .= '<td>' . $proj->id . '</td>';
            $html .= '<td>' . e($proj->name) . '</td>';
            $html .= '<td>' . e($proj->status_label) . '</td>';
            $html .= '<td>' . $proj->progreso . '%</td>';
            $html .= '<td>' . $proj->requirements()->count() . '</td>';
            $html .= '<td>' . $proj->requirements()->where('status', 'completado')->count() . '</td>';
            $html .= '<td>' . $actCount . '</td>';
            $html .= '<td>' . $actDone . '</td>';
            $html .= '<td>' . $actBlocked . '</td>';
            $html .= '<td style="color:' . ($actOverdue > 0 ? '#e74c3c' : '#999') . '">' . $actOverdue . '</td>';
            $html .= '<td>' . $proj->teamMembers()->count() . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        $html .= '<br><p style="color:#999;font-size:10px;page-break-before:always;">Report generated by PM API v1.0</p></body></html>';
        return $html;
    }

    // ============================================================
    // UTILITIES
    // ============================================================
    protected function computeDevStats($devLogs)
    {
        $totalMinutes = $devLogs->sum('tiempo_gastado_minutos');

        return [
            'total_logs' => $devLogs->count(),
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalMinutes / 60, 2),
            'by_action' => $devLogs->groupBy('tipo_accion')
                ->map(fn($logs) => ['count' => $logs->count(), 'total_minutes' => $logs->sum('tiempo_gastado_minutos')])
                ->toArray(),
            'by_project' => $devLogs->map(fn($log) => $log->activity->requirement->project)
                ->filter()
                ->groupBy('name')
                ->map(fn($projLogs) => [
                    'activities' => $projLogs->groupBy(fn($l) => $l->activity->id)->keys()->count(),
                    'total_minutes' => $projLogs->sum('tiempo_gastado_minutos'),
                ])->toArray(),
        ];
    }

    protected function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }
}
