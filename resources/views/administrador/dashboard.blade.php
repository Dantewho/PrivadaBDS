@extends('loyauts.main')
@section('title', 'Dashboard')
@section('content')
<div class="page-heading"><div><p class="eyebrow">RESUMEN GENERAL</p><h1>Panel de administración</h1><p>Bahía de Santiago · {{ now()->locale('es')->translatedFormat('l, j \\d\\e F \\d\\e Y') }}</p></div><span class="date-chip">Actualizado {{ now()->format('H:i') }}</span></div>
<div class="stats-grid admin-dashboard-stats">
@foreach([['Viviendas',$stats['viviendas'],'icon-blue'],['Usuarios activos',$stats['usuarios'],'icon-violet'],['Tarjetas activas',$stats['tarjetas'],'icon-green'],['Adeudos pendientes','$'.number_format($stats['adeudos'],0).' MXN','icon-orange'],['Multas pendientes',$stats['multas'],'icon-red'],['Apelaciones abiertas',$stats['apelaciones'],'icon-yellow'],['Quejas abiertas',$stats['quejas'],'icon-orange'],['Anuncios publicados',$stats['anuncios'],'icon-blue']] as [$label,$value,$color])
<article class="stat-card"><div class="stat-top"><span>{{ $label }}</span><span class="stat-icon {{ $color }}">•</span></div><strong>{{ $value }}</strong></article>
@endforeach
</div>
<div class="dashboard-columns">
<section class="panel activity-panel"><div class="panel-heading"><div><h2>Actividad reciente</h2><p>Últimos movimientos registrados</p></div></div><div class="activity-list">
@forelse($activity as $item)<article class="activity-item"><span class="activity-icon icon-blue">{{ $item['icon'] }}</span><div><strong>{{ $item['title'] }}</strong><p>{{ $item['detail'] }}</p></div><time>{{ $item['date']?->diffForHumans() }}</time></article>@empty<div class="admin-empty-state">Aún no hay movimientos registrados.</div>@endforelse
</div></section>
<div class="dashboard-side"><section class="panel quick-panel"><div class="panel-heading"><div><h2>Acciones rápidas</h2><p>Acceso a tareas frecuentes</p></div></div>
<a class="quick-link" href="{{ route('admin.viviendas') }}">Registrar vivienda <b>›</b></a><a class="quick-link" href="{{ route('admin.usuarios') }}">Invitar usuario <b>›</b></a><a class="quick-link" href="{{ route('admin.tarjetas') }}">Administrar tarjetas <b>›</b></a><a class="quick-link" href="{{ route('admin.multas') }}">Registrar multa <b>›</b></a>
</section><section class="panel pending-panel"><div class="panel-heading"><div><h2>Atención pendiente</h2><p>Solicitudes sin resolver</p></div></div><a href="{{ route('admin.quejas') }}"><span>Quejas abiertas</span><b class="count count-orange">{{ $pendingComplaints }}</b></a><a href="{{ route('admin.apelaciones') }}"><span>Apelaciones abiertas</span><b class="count count-yellow">{{ $pendingAppeals }}</b></a><a href="{{ route('admin.adeudos') }}"><span>Adeudos pendientes</span><b class="count count-red">{{ $stats['adeudos'] > 0 ? 'Ver' : '0' }}</b></a></section>
@if($latestAnnouncement)<section class="panel communication-panel"><div class="panel-heading"><div><h2>Último anuncio</h2><p>Comunicación con residentes</p></div></div><div class="announcement-preview"><strong>{{ $latestAnnouncement->titulo }}</strong><p>{{ \Illuminate\Support\Str::limit($latestAnnouncement->contenido, 160) }}</p><small>{{ $latestAnnouncement->fecha_publicacion?->format('d/m/Y H:i') }}</small></div></section>@endif
</div></div>
@endsection
