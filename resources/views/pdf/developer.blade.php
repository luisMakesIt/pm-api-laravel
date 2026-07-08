<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Desarrollador - {{ $user->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 15px; }
        h1 { font-size: 18px; color: #1a365d; border-bottom: 3px solid #1a365d; padding-bottom: 8px; }
        h2 { font-size: 14px; color: #2b6cb0; margin-top: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        table.data { width: 100%; border-collapse: collapse; margin: 10px 0 20px; font-size: 10px; }
        table.data th { background: #2d3748; color: white; padding: 6px 8px; text-align: left; font-weight: 600; }
        table.data td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        table.data tr:nth-child(even) { background: #f7fafc; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: 600; text-transform: uppercase; }
        .badge-commit { background: #bee3f8; color: #2a4365; }
        .badge-fix { background: #fed7d7; color: #c53030; }
        .badge-feature { background: #c6f6d5; color: #276749; }
        .badge-review { background: #fefcbf; color: #975a16; }
        .badge-deploy { background: #e9d8fd; color: #553c9a; }
        .meta { background: #f7fafc; padding: 12px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #4299e1; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td, .meta th { padding: 4px 8px; text-align: left; }
        .meta th { width: 160px; font-weight: 600; color: #4a5568; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 10px 0 20px; }
        .stat-card { background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; text-align: center; }
        .stat-card .number { font-size: 24px; font-weight: 700; color: #2b6cb0; }
        .stat-card .label { font-size: 10px; color: #718096; text-transform: uppercase; margin-top: 4px; }
        .page-break { page-break-before: always; }
        .footer { text-align: center; color: #a0aec0; font-size: 9px; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <h1>👨‍💻 Reporte de Desarrollador</h1>
    <p style="color:#718096; font-size:10px;">Generado: {{ date('d/m/Y H:i:s') }}</p>

    <div class="meta">
        <table>
            <tr><th>Nombre</th><td>{{ $user->name }}</td></tr>
            <tr><th>Email</th><td>{{ $user->email }}</td></tr>
            <tr><th>Rol</th><td>{{ $user->role ?? '—' }}</td></tr>
            <tr><th>GitHub</th><td>{{ $user->github_username ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="number">{{ $stats['total_logs'] ?? 0 }}</div><div class="label">Registros Total</div></div>
        <div class="stat-card"><div class="number">{{ $stats['total_hours'] ?? 0 }}h</div><div class="label">Horas Gastadas</div></div>
        <div class="stat-card"><div class="number">{{ $stats['total_minutes'] ?? 0 }}</div><div class="label">Minutos Gastados</div></div>
        <div class="stat-card"><div class="number">{{ $stats['projects'] ?? 0 }}</div><div class="label">Proyectos</div></div>
    </div>

    <h2>⏱️ Resumen por Tipo de Acción</h2>
    <table class="data">
        <tr><th>Tipo</th><th>Cantidad</th><th>Total Minutos</th><th>Promedio Min.</th></tr>
        @foreach(($stats['byType'] ?? []) as $type => $info)
        <tr>
            <td><span class="badge badge-{{ $type }}">{{ $type }}</span></td>
            <td>{{ $info['count'] }}</td>
            <td>{{ number_format($info['minutes'], 0, ',', '.') }} min</td>
            <td>{{ $info['count'] > 0 ? number_format($info['minutes'] / $info['count'], 1, '.', ',') : 0 }} min</td>
        </tr>
        @endforeach
    </table>

    <div class="page-break"></div>
    <h2>📋 Historial de Desarrollo</h2>
    @if($devLogs->count())
    <table class="data">
        <tr><th>Fecha</th><th>Acción</th><th>Descripción</th><th>Min.</th><th>Ref.</th></tr>
        @foreach($devLogs as $log)
        <tr>
            <td>{{ date('d/m/Y H:i', strtotime($log->fecha_registro)) }}</td>
            <td><span class="badge badge-{{ $log->tipo_accion }}">{{ $log->tipo_accion }}</span></td>
            <td>{{ Str::limit($log->descripcion, 60) }}</td>
            <td>{{ floor($log->tiempo_gastado_minutos) }} min</td>
            <td>{{ $log->link_o_ref ? '<a href="'.$log->link_o_ref.'">link ↗</a>' : '—' }}</td>
        </tr>
        @endforeach
    </table>
    @else
    <p style="color:#a0aec0; font-style:italic; padding:20px; text-align:center;">Sin registros de desarrollo.</p>
    @endif

    @if(isset($stats['byProject']) && count($stats['byProject']) > 0)
    <div class="page-break"></div>
    <h2>📊 Por Proyecto</h2>
    <table class="data">
        <tr><th>Proyecto</th><th>Registros</th><th>Total Minutos</th><th>Tipo más frecuente</th></tr>
        @foreach($stats['byProject'] as $projName => $info)
        <tr>
            <td>{{ $projName }}</td>
            <td>{{ $info['count'] }}</td>
            <td>{{ number_format($info['minutes'], 0, ',', '.') }} min</td>
            <td>{{ $info['top_type'] ?? '—' }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    <div class="footer">PM API Laravel — Reporte de Desarrollador — {{ date('Y-m-d H:i:s') }}</div>
</body>
</html>
