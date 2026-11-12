<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 42px 46px; }
        body { color: #18243a; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.45; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { border-bottom: 1px solid #d8e1ed; font-size: 13px; margin: 20px 0 8px; padding-bottom: 5px; }
        .muted { color: #63718a; }
        .summary { border: 1px solid #d8e1ed; border-radius: 7px; margin-top: 18px; padding: 12px; }
        .summary table, .records { border-collapse: collapse; width: 100%; }
        .summary td { padding: 5px 8px; width: 50%; }
        .records th, .records td { border-bottom: 1px solid #e5ebf2; padding: 7px 6px; text-align: left; vertical-align: top; }
        .records th { background: #f1f5fa; color: #394a63; font-size: 8px; }
        .footer { border-top: 1px solid #d8e1ed; color: #63718a; margin-top: 24px; padding-top: 8px; }
    </style>
</head>
<body>
    <p class="muted">PRIVADABDS · DOCUMENTO DE USO ADMINISTRATIVO</p>
    <h1>Hoja de evidencias del historial</h1>
    <div class="muted">Vivienda #{{ $history['vivienda']->numero }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>
    <div class="summary"><table>
        <tr><td><strong>Multas registradas:</strong> {{ $history['total_multas'] }}</td><td><strong>Pagos atrasados:</strong> {{ $history['pagos_atrasados'] }}</td></tr>
        <tr><td><strong>Quejas realizadas:</strong> {{ $history['quejas_realizadas'] }}</td><td><strong>Quejas recibidas:</strong> {{ $history['quejas_recibidas'] }}</td></tr>
        <tr><td><strong>Mensajes reportados:</strong> {{ $history['mensajes_reportados'] }}</td><td><strong>Dirección:</strong> {{ $history['vivienda']->direccion ?: 'No registrada' }}</td></tr>
    </table></div>

    <h2>Multas por tipo</h2>
    <table class="records"><thead><tr><th>MOTIVO</th><th>CANTIDAD</th></tr></thead><tbody>
        @forelse($history['multas_por_tipo'] as $tipo)<tr><td>{{ $tipo['tipo'] }}</td><td>{{ $tipo['cantidad'] }}</td></tr>@empty<tr><td colspan="2">Sin multas registradas.</td></tr>@endforelse
    </tbody></table>

    <h2>Residentes asignados</h2>
    <table class="records"><thead><tr><th>NOMBRE</th><th>CORREO</th><th>ESTADO</th></tr></thead><tbody>
        @forelse($history['vivienda']->usuarios as $usuario)<tr><td>{{ $usuario->name }}</td><td>{{ $usuario->email }}</td><td>{{ ucfirst($usuario->estado) }}</td></tr>@empty<tr><td colspan="3">Sin residentes asignados.</td></tr>@endforelse
    </tbody></table>

    <h2>Multas</h2>
    <table class="records"><thead><tr><th>FECHA</th><th>TIPO</th><th>REINCIDENCIA</th><th>MONTO</th><th>ESTADO</th><th>RESIDENTE</th></tr></thead><tbody>
        @forelse($history['vivienda']->multas->sortByDesc('fecha') as $multa)<tr><td>{{ $multa->fecha?->format('d/m/Y') }}</td><td>{{ $multa->motivo?->nombre ?? 'Sin tipo' }}</td><td>{{ $multa->numero_reincidencia }}.ª</td><td>${{ number_format($multa->monto_final, 2) }} MXN</td><td>{{ ucfirst($multa->estado) }}</td><td>{{ $multa->usuario?->name ?? 'Vivienda' }}</td></tr>@empty<tr><td colspan="6">Sin multas registradas.</td></tr>@endforelse
    </tbody></table>

    <h2>Quejas realizadas o recibidas</h2>
    <table class="records"><thead><tr><th>FECHA</th><th>TIPO</th><th>ASUNTO</th><th>ESTADO</th><th>MENSAJES REPORTADOS</th></tr></thead><tbody>
        @forelse($history['quejas'] as $queja)<tr><td>{{ $queja->created_at?->format('d/m/Y') }}</td><td>{{ (int)$queja->vivienda_id === (int)$history['vivienda']->id ? 'Realizada' : 'Recibida' }}</td><td>{{ $queja->asunto }}</td><td>{{ ucfirst(str_replace('_', ' ', $queja->estado)) }}</td><td>{{ $queja->mensajes->where('ofensivo', true)->count() }}</td></tr>@empty<tr><td colspan="5">Sin quejas relacionadas.</td></tr>@endforelse
    </tbody></table>

    <h2>Adeudos vencidos o pagados fuera de fecha</h2>
    <table class="records"><thead><tr><th>CONCEPTO</th><th>VENCIMIENTO</th><th>MONTO</th><th>ESTADO</th><th>ACTUALIZADO</th></tr></thead><tbody>
        @forelse($history['adeudos_atrasados'] as $adeudo)<tr><td>{{ $adeudo->concepto }}</td><td>{{ $adeudo->fecha_vencimiento->format('d/m/Y') }}</td><td>${{ number_format($adeudo->monto, 2) }} MXN</td><td>{{ ucfirst($adeudo->estado) }}</td><td>{{ $adeudo->updated_at?->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="5">Sin pagos atrasados registrados.</td></tr>@endforelse
    </tbody></table>

    <h2>Mensajes reportados como ofensivos</h2>
    <table class="records"><thead><tr><th>FECHA</th><th>QUEJA</th><th>AUTOR</th><th>MENSAJE REPORTADO</th><th>MODERADOR</th></tr></thead><tbody>
        @forelse($history['mensajes_reportados_detalle'] as $mensaje)<tr><td>{{ $mensaje->created_at?->format('d/m/Y H:i') }}</td><td>{{ $mensaje->queja?->asunto }}</td><td>{{ $mensaje->autorEtiqueta() }}</td><td>{{ $mensaje->mensaje }}</td><td>{{ $mensaje->moderador?->name ?? '—' }}</td></tr>@empty<tr><td colspan="5">Sin mensajes reportados.</td></tr>@endforelse
    </tbody></table>

    <div class="footer">Este reporte reúne los registros disponibles en el sistema al momento de su generación.</div>
</body>
</html>
