<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte - {{ $project->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 15px; }
        h1 { font-size: 18px; color: #1a365d; border-bottom: 3px solid #1a365d; padding-bottom: 8px; }
        h2 { font-size: 14px; color: #2b6cb0; margin-top: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        h3 { font-size: 12px; color: #4a5568; margin-top: 12px; }
        .meta { background: #f7fafc; padding: 12px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #4299e1; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td, .meta th { padding: 4px 8px; text-align: left; }
        .meta th { width: 160px; font-weight: 600; color: #4a5568; }
        table.data { width: 100%; border-collapse: collapse; margin: 10px 0 20px; font-size: 10px; }
        table.data th { background: #2d3748; color: white; padding: 6px 8px; text-align: left; font-weight: 600; }
        table.data td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        table.data tr:nth-child(even) { background: #f7fafc; }
        table.data tr:hover { background: #edf2f7; }
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
        .badge-disponible { background: #c6f6d5; color: #276749; }
        .badge-en_tarea { background: #bee3f8; color: #2a4365; }
        .badge-ocupado { background: #fefcbf; color: #975a16; }
        .badge-fuera { background: #e2e8f0; color: #4a5568; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 10px 0 20px; }
        .stat-card { background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; text-align: center; }
        .stat-card .number { font-size: 24px; font-weight: 700; color: #2b6cb0; }
        .stat-card .label { font-size: 10px; color: #718096; text-transform: uppercase; margin-top: 4px; }
        .page-break { page-break-before: always; }
        .footer { text-align: center; color: #a0aec0; font-size: 9px; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .no-data { color: #a0aec0; font-style: italic; padding: 20px; text-align: center; }
    </style>
</head>
<body>
    <h1>📋 {{ $project->name }}</h1>
    <p style="color:#718096; font-size:10px;">Generado: {{ date('d/m/Y H:i:s') }}</p>

    <div class="meta">
        <table>
            <tr><th>Descripción</th><td>{{ $project->description ?? '—' }}</td></tr>
            <tr><th>Estado</th><td><span class="badge badge-{{ $project->status }}">{{ $project->status }}</span></td></tr>
            <tr><th>Fecha Inicio</th><td>{{ $project->start_date ? date('d/m/Y', strtotime($project->start_date)) : '—' }}</td></tr>
            <tr><th>Fecha Fin</th><td>{{ $project->end_date ? date('d/m/Y', strtotime($project->end_date)) : '—' }}</td></tr>
            <tr><th>URL Repo Git</th><td>{{ $project->git_repo_url ?? '—' }}</td></tr>
            <tr><th>Progreso</th><td>{{ $project->progreso ?? 0 }}%</td></tr>
        </table>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="number">{{ $project->requirements->count() }}</div><div class="label">Requerimientos</div></div>
        <div class="stat-card"><div class="number">{{ $project->activities->count() }}</div><div class="label">Actividades</div></div>
        <div class="stat-card"><div class="number">{{ $project->teamMembers->count() }}</div><div class="label">Equipo</div></div>
    </div>

    <h2>📝 Requerimientos ({{ $project->requirements->count() }})</h2>
    @if($project->requirements->count())
    <table class="data">
        <tr><th>#</th><th>Título</th><th>Prioridad</th><th>Estado</th><th>Actas</th><th>Actividades</th></tr>
        @foreach($project->requirements as $req)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ Str::limit($req->title, 50) }}</td>
            <td><span class="badge badge-{{ $req->priority }}">{{ $req->priority }}</span></td>
            <td><span class="badge badge-{{ $req->status }}">{{ $req->status }}</span></td>
            <td>{{ $req->requirementActas->count() }}</td>
            <td>{{ $req->activities->count() }}</td>
        </tr>
        @endforeach
    </table>
    @else
    <p class="no-data">Sin requerimientos registrados.</p>
    @endif

    @if($project->activities->count())
    <div class="page-break"></div>
    <h2>⚡ Actividades ({{ $project->activities->count() }})</h2>
    <table class="data">
        <tr><th>Título</th><th>Requerimiento</th><th>Estado</th><th>Asignado</th><th>Inicio</th><th>Limite</th><th>Tiempo Est.</th><th>Tiempo Real</th></tr>
        @foreach($project->activities as $act)
        <tr>
            <td>{{ Str::limit($act->title, 40) }}</td>
            <td>{{ $act->requirement ? $act->requirement->title : '—' }}</td>
            <td><span class="badge badge-{{ $act->status }}">{{ $act->status }}</span></td>
            <td>{{ $act->asignado_a }}</td>
            <td>{{ $act->fecha_inicio_planificada ? date('d/m/Y', strtotime($act->fecha_inicio_planificada)) : '—' }}</td>
            <td>{{ $act->fecha_limite ? date('d/m/Y', strtotime($act->fecha_limite)) : '—' }}</td>
            <td>{{ $act->tiempo_estimado_horas }}h</td>
            <td>{{ $act->tiempo_real_horas }}h</td>
        </tr>
        @endforeach
    </table>
    @endif

    @if($project->teamMembers->count())
    <div class="page-break"></div>
    <h2>👥 Equipo ({{ $project->teamMembers->count() }})</h2>
    <table class="data">
        <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Experiencia</th><th>Estado</th><th>GitHub</th></tr>
        @foreach($project->teamMembers as $member)
        <tr>
            <td>{{ $member->name }}</td>
            <td>{{ $member->email }}</td>
            <td>{{ $member->role }}</td>
            <td>{{ $member->nivel_experiencia }}</td>
            <td><span class="badge badge-{{ $member->estado }}">{{ $member->estado }}</span></td>
            <td>{{ $member->github_url ? '<a href="'.$member->github_url.'">'.Str::limit($member->github_url, 35).'</a>' : '—' }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    <div class="footer">PM API Laravel — Reporte generado automáticamente — {{ date('Y-m-d H:i:s') }}</div>
</body>
</html>
