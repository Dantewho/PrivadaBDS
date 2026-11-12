@extends('loyauts.main')
@section('title', 'Anuncios')
@section('content')
<div class="page-heading"><div><p class="eyebrow">COMUNICACIÓN CON RESIDENTES</p><h1>Anuncios</h1><p>Publica avisos, define cuánto tiempo estarán visibles y consulta quién los leyó.</p></div><div class="heading-actions"><button class="button button-primary" type="button" data-modal-open="modalAnuncio">＋ Nuevo anuncio</button></div></div>
<div class="stats-grid module-stats announcement-stats">
    <article class="stat-card"><div class="stat-top">Anuncios publicados</div><strong>{{ $stats['publicados'] }}</strong><small>Disponibles para residentes</small></article>
    <article class="stat-card"><div class="stat-top">Avisos en bandejas</div><strong class="text-green">{{ $stats['notificaciones'] }}</strong><small>Notificaciones residentes creadas</small></article>
    <article class="stat-card"><div class="stat-top">Borradores</div><strong class="text-orange">{{ $stats['borradores'] }}</strong><small>Pendientes de publicación</small></article>
    <article class="stat-card"><div class="stat-top">WhatsApp por revisar</div><strong>{{ $stats['whatsappPendiente'] }}</strong><small>Envíos sin completar o configurar</small></article>
</div>
<section class="panel table-panel module-table announcement-table">
    <form class="table-toolbar" method="get" action="{{ route('admin.anuncios') }}"><div class="search-box"><input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por título o contenido"></div><select name="estado"><option value="">Todos los estados</option><option value="publicado" @selected(request('estado')==='publicado')>Publicado</option><option value="borrador" @selected(request('estado')==='borrador')>Borrador</option></select><button class="button button-outline" type="submit">Filtrar</button><a class="button button-outline" href="{{ route('admin.anuncios') }}">Limpiar</a></form>
    <div class="table-scroll"><table><thead><tr><th>ANUNCIO</th><th>PUBLICADO</th><th>VISIBLE</th><th>DESTINATARIOS / LECTURAS</th><th>WHATSAPP</th><th>ESTADO</th><th>ACCIONES</th></tr></thead><tbody>
    @forelse($announcements as $announcement)
        @php($whatsapp = $announcement->envios->firstWhere('canal', 'whatsapp'))
        @php($readNotifications = $announcement->notificaciones->whereNotNull('leida_at')->sortByDesc('leida_at'))
        <tr><td><strong>{{ $announcement->titulo }}</strong><small class="table-subline">{{ \Illuminate\Support\Str::limit($announcement->contenido, 100) }}</small></td>
            <td>{{ $announcement->fecha_publicacion?->format('d/m/Y H:i') ?? '—' }}</td>
            <td>{{ $announcement->visible_desde?->format('d/m/Y') ?? '—' }}<small class="table-subline">hasta {{ $announcement->visible_hasta?->format('d/m/Y') ?? '—' }}</small></td>
            <td>{{ $announcement->notificaciones_count }} destinatarios · {{ $readNotifications->count() }} leídos
                @if($readNotifications->isNotEmpty())<details class="notification-readers"><summary>Ver quién leyó</summary><ul>@foreach($readNotifications as $read)<li><strong>{{ $read->usuario?->name ?? 'Usuario eliminado' }}</strong><small>Casa #{{ $read->usuario?->vivienda?->numero ?? '—' }} · {{ $read->leida_at?->format('d/m/Y H:i') }}</small></li>@endforeach</ul></details>@endif
            </td>
            <td>@if($whatsapp)<span class="status {{ $whatsapp->estado==='enviado'?'status-active':($whatsapp->estado==='error'?'status-overdue':'status-review') }}">{{ str_replace('_',' ',ucfirst($whatsapp->estado)) }}</span>@else—@endif @if($announcement->estado==='publicado' && $whatsapp?->estado!=='enviado')<form method="post" action="{{ route('admin.anuncios.whatsapp',$announcement) }}">@csrf<button class="text-action" type="submit">{{ $whatsapp ? 'Reintentar' : 'Enviar' }}</button></form>@endif</td>
            <td><span class="status {{ $announcement->estado==='publicado'?'status-active':'status-inactive' }}">{{ $announcement->estado==='publicado'?'Publicado':'Borrador' }}</span></td>
            <td class="actions-cell"><button class="text-action" type="button" data-modal-open="modalAnuncioEdit{{ $announcement->id }}">Editar</button><form method="post" action="{{ route('admin.anuncios.destroy',$announcement) }}" onsubmit="return confirm('¿Eliminar este anuncio?')">@csrf @method('DELETE')<button class="text-action text-red" type="submit">Eliminar</button></form></td></tr>
    @empty
        <tr><td colspan="7">Aún no hay anuncios. Crea uno para publicarlo en el portal.</td></tr>
    @endforelse
    </tbody></table></div>
    <div class="table-footer"><span>{{ $announcements->firstItem()??0 }}–{{ $announcements->lastItem()??0 }} de {{ $announcements->total() }} anuncios</span>{{ $announcements->links('vendor.pagination.admin') }}<span></span></div>
</section>
@include('administrador.modales.anuncio')
@foreach($announcements as $announcement)
    @include('administrador.modales.anuncio-editar', ['announcement' => $announcement])
@endforeach
@endsection
