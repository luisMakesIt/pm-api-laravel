<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Equipo</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 15px; }
        h1 { font-size: 18px; color: #1a365d; border-bottom: 3px solid #1a365d; padding-bottom: 8px; }
        h2 { font-size: 14px; color: #2b6cb0; margin-top: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        table.data { width: 100%; border-collapse: collapse; margin: 10px 0 20px; font-size: 10px; }
        table.data th { background: #2d3748; color: white; padding: 6px 8px; text-align: left; font-weight: 600; }
        table.data td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        table.data tr:nth-child(even) { background: #f7fafc; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: 600; text-transform: uppercase; }
        .badge-alta { background: #fed7d7; color: #c53030; }
        .badge-media { background: #fefcbf; color: #975a16; }
        .badge-baja { background: #c6f6d5; color: #276749; }
        .badge-planificacion { background: #bee3f8; color: #2a4365; }
        .badge-en_desarrollo { background: #fefcbf; color: #975a16; }
        .badge-en_pruebas { background: #fed7d7; color: #c53030; }
        .badge-completado { background: #c6f6d5; color: #276749; }
        .badge-cancelado { background: #e2e8f0; color: #4a5568; }
        .badge-pendiente { background: #bee3f8; color: #2a4365; }
        .badge-en_progreso { background: #fefcbf; color: #975a16; }
        .badge-completada { background: #c6f6d5; color: #276749; }
        .badge-bloqueada { background: #fed7d7; color: #c53030; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; margin: 10px 0 20px; }
        .stat-card { background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; text-align: center; }
        .stat-card .number { font-size: 20px; font-weight: 700; color: #2b6cb0; }
        .stat-card .label { font-size: 9px; color: #718096; text-transform: uppercase; margin-top: 3px; }
        .page-break { page-break-before: always; }
        .footer { text-align: center; color: #a0aec0; font-size: 9px; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <h1>🏢 Reporte General del Equipo</h1>
    <p style="color:#718096; font-size:10px;">Generado: {{ date('d/m/Y H:i:s') }}</p>

    @if(isset($globalStats) && is_array($globalStats))
    <div class="stats-grid">
        <div class="stat-card"><div class="number">{{ $globalStats['projects'] ?? 0 }}</div><div class="label">Proyectos</div></div>
        <div class="stat-card"><div class="number">{{ $globalStats['requirements'] ?? 0 }}</div><div class="label">Requerimientos</div></div>
        <div class="stat-card"><div class="number">{{ $globalStats['activities'] ?? 0 }}</div><div class="label">Actividades</div></div>
        <div class="stat-card"><div class="number">{{ $globalStats['team_members'] ?? 0 }}</div><div class="label">Miembros Equipo</div></div>
        <div class="stat-card"><div class="number">{{ $globalStats['dev_logs'] ?? 0 }}</div><div class="label">Logs Desarrollo</div></div>
    </div>
    @endif

    @foreach($projects as $project)
    @if($loop->index > 0)<div class="page-break"></div>@endif
    <h2>📋 {{ $project->name }}</h2>

    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card"><div class="number">{{ $project->requirements->count() }}</div><div class="label">Requerimientos</div></div>
        <div class="stat-card"><div class="number">{{ $project->activities->count() }}</div><div class="label">Actividades</div></div>
        <div class="stat-card"><div class="number">{{ $project->teamMembers->count() }}</div><div class="label">Equipo</div></div>
        <div class="stat-card"><div class="number">{{ $project->progreso ?? 0 }}%</div><div class="label">Progreso</div></div>
    </div>

    @php
        $reqStatuses = [];
        $actStatuses = [];
        foreach ($project->requirements as $r) { $reqStatuses[$r->status] = ($reqStatuses[$r->status] ?? 0) + 1; }
        foreach ($project->activities as $a) { $actStatuses[$a->status] = ($actStatuses[$a->status] ?? 0) + 1; }
    @endphp

    <table class="data">
        <tr><th>Requerimiento</th><th>Prioridad</th><th>Estado</th><th>Actas</th><th>Actividades</th></tr>
        @foreach($project->requirements as $req)
        <tr>
            <td>{{ Str::limit($req->title, 45) }}</td>
            <td><span class="badge badge-{{ $req->priority }}">{{ $req->priority }}</span></td>
            <td><span class="badge badge-{{ $req->status }}">{{ $req->status }}</span></td>
            <td>{{ $req->requirementActas->count() }}</td>
            <td>{{ $req->activities->count() }}</td>
        </tr>
        @endforeach
    </table>

    <table class="data">
        <tr><th>Actividad</th><th>Estado</th><th>Asignado</th><th>Inicio</th><th>Limite</th><th>Tiempo</th></tr>
        @foreach($project->activities as $act)
        <tr>
            <td>{{ Str::limit($act->title, 40) }}</td>
            <td><span class="badge badge-{{ $act->status }}">{{ $act->status }}</span></td>
            <td>{{ $act->asignado_a ?? '—' }}</td>
            <td>{{ $act->fecha_inicio_planificada ? date('d/m/Y', strtotime($act->fecha_inicio_planificada)) : '—' }}</td>
            <td>{{ $act->fecha_limite ? date('d/m/Y', strtotime($act->fecha_limite)) : '—' }}</td>
            <td>{{ $act->tiempo_estimado_horas }}h est / {{ $act->tiempo_real_horas }}h real</td>
        </tr>
        @endforeach
    </table>

    @if($project->teamMembers->count())
    <table class="data">
        <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th></tr>
        @foreach($project->teamMembers as $m)
        <tr>
            <td>{{ $m->name }}</td>
            <td>{{ $m->email }}</td>
            <td>{{ $m->role }}</td>
            <td><span class="badge badge-{{ $m->estado }}">{{ $m->estado }}</span></td>
        </tr>
        @endforeach
    </table>
    @endif
    @endforeach

    <div class="footer">PM API Laravel — Reporte de Equipo — {{ date('Y-m-d H:i:s') }}</div>
</body>
</html>
