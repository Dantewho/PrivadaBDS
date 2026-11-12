@extends('loyauts.main')
@section('title', 'Quejas')
@section('content')
<div class="page-heading"><div><p class="eyebrow">ATENCIÓN A RESIDENTES</p><h1>Administrador de quejas</h1><p>Responde reportes, actualiza su seguimiento y modera mensajes ofensivos.</p></div></div>
@if(session('status'))<div class="info-banner">{{ session('status') }}</div>@endif
@if($errors->any())<div class="info-banner">{{ $errors->first() }}</div>@endif
<div class="stats-grid module-stats"><article class="stat-card"><div class="stat-top">Quejas registradas</div><strong>{{ $stats['total'] }}</strong></article><article class="stat-card"><div class="stat-top">Abiertas</div><strong class="text-orange">{{ $stats['abiertas'] }}</strong></article><article class="stat-card"><div class="stat-top">En proceso</div><strong>{{ $stats['proceso'] }}</strong></article><article class="stat-card"><div class="stat-top">Resueltas / cerradas</div><strong class="text-green">{{ $stats['resueltas'] }}</strong></article></div>
<section class="panel table-panel module-table">
    <form class="table-toolbar" method="get" action="{{ route('admin.quejas') }}" data-admin-filter><div class="search-box"><input name="q" type="search" value="{{ request('q') }}" placeholder="Buscar asunto, residente o vivienda"></div><select name="estado"><option value="">Todos los estados</option>@foreach(['abierta','en_proceso','resuelta','cerrada'] as $state)<option value="{{ $state }}" @selected(request('estado') === $state)>{{ ucfirst(str_replace('_', ' ', $state)) }}</option>@endforeach</select><button class="button button-outline">Filtrar</button><span class="table-result">{{ $complaints->total() }} conversaciones</span></form>
    <div class="table-scroll"><table><thead><tr><th>ASUNTO</th><th>ORIGEN</th><th>VIVIENDA REPORTADA</th><th>RESIDENTE</th><th>FECHA</th><th>ESTADO</th><th>CHAT Y MODERACIÓN</th></tr></thead><tbody>
        @forelse($complaints as $complaint)
            <tr><td><strong>{{ $complaint->asunto }}</strong><small class="table-subline">{{ $complaint->descripcion }}</small></td><td>#{{ $complaint->vivienda->numero }}</td><td>{{ $complaint->viviendaReportada ? '#'.$complaint->viviendaReportada->numero : '—' }}</td><td>{{ $complaint->usuario->name }}</td><td>{{ $complaint->created_at->format('d/m/Y') }}</td><td>{{ ucfirst(str_replace('_', ' ', $complaint->estado)) }}</td>
            <td><details class="admin-conversation"><summary>Abrir chat ({{ $complaint->mensajes->count() }})</summary><div class="message-thread">
                @foreach($complaint->mensajes as $message)
                    <div class="thread-message {{ $message->usuario->rol === 'administrador' ? 'admin-message' : 'resident-message' }} {{ $message->ofensivo ? 'message-offensive' : '' }}"><small>{{ $message->autorEtiqueta() }} · {{ $message->created_at?->format('d/m/Y H:i') }} @if($message->ofensivo)<strong>· Texto ofensivo</strong>@endif</small><p>{{ $message->mensaje }}</p><form method="post" action="{{ route('admin.quejas.mensajes.moderar', $message) }}">@csrf @method('PATCH')<input type="hidden" name="ofensivo" value="{{ $message->ofensivo ? 0 : 1 }}"><button class="text-action" type="submit">{{ $message->ofensivo ? 'Quitar marca ofensiva' : 'Marcar texto ofensivo' }}</button></form></div>
                @endforeach
            </div>
            @if(in_array($complaint->estado, ['abierta', 'en_proceso']))<form method="post" action="{{ route('admin.quejas.mensaje', $complaint) }}" data-validate-form>@csrf<label>Responder<textarea name="mensaje" rows="2" maxlength="3000" required></textarea></label><button class="button button-primary">Enviar respuesta</button></form>@endif
            <form method="post" action="{{ route('admin.quejas.update', $complaint) }}">@csrf @method('PATCH')<label>Estado<select name="estado">@foreach(['abierta','en_proceso','resuelta','cerrada'] as $state)<option value="{{ $state }}" @selected($complaint->estado === $state)>{{ ucfirst(str_replace('_', ' ', $state)) }}</option>@endforeach</select></label><button class="button button-outline">Guardar estado</button></form></details></td></tr>
        @empty<tr><td colspan="7">No hay quejas registradas.</td></tr>@endforelse
    </tbody></table></div>
    <div class="table-footer"><span>{{ $complaints->firstItem() ?? 0 }}–{{ $complaints->lastItem() ?? 0 }} de {{ $complaints->total() }}</span>{{ $complaints->links('vendor.pagination.admin') }}<span></span></div>
</section>
@endsection
