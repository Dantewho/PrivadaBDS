@extends('loyauts.main')
@section('title', 'Historial de viviendas')
@section('content')
<div class="page-heading">
    <div><p class="eyebrow">ANTECEDENTES DE LA COMUNIDAD</p><h1>Historial</h1><p>Resumen de multas, quejas, pagos atrasados y mensajes reportados por vivienda.</p></div>
</div>
<section class="panel table-panel module-table">
    <div class="table-scroll"><table>
        <thead><tr><th>VIVIENDA</th><th>MULTAS</th><th>POR TIPO</th><th>QUEJAS REALIZADAS</th><th>QUEJAS RECIBIDAS</th><th>PAGOS ATRASADOS</th><th>MENSAJES REPORTADOS</th><th>HOJA DE EVIDENCIAS</th></tr></thead>
        <tbody>
        @forelse($historiales as $historial)
            <tr>
                <td><strong>Casa #{{ $historial['vivienda']->numero }}</strong><small class="table-subline">{{ $historial['vivienda']->direccion }}</small></td>
                <td>{{ $historial['total_multas'] }}</td>
                <td>@forelse($historial['multas_por_tipo'] as $tipo)<span class="history-type-count">{{ $tipo['tipo'] }}: <strong>{{ $tipo['cantidad'] }}</strong></span>@empty—@endforelse</td>
                <td>{{ $historial['quejas_realizadas'] }}</td><td>{{ $historial['quejas_recibidas'] }}</td><td>{{ $historial['pagos_atrasados'] }}</td><td>{{ $historial['mensajes_reportados'] }}</td>
                <td><a class="button button-outline" href="{{ route('admin.historial.pdf', $historial['vivienda']) }}">Descargar PDF</a></td>
            </tr>
        @empty
            <tr><td colspan="8">No hay viviendas registradas.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <p class="history-note">Un pago atrasado se cuenta cuando el adeudo continúa pendiente después de su vencimiento o se confirma después de esa fecha.</p>
</section>
@endsection
