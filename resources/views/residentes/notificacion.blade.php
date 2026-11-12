@extends('residentes.layout')
@section('title', $notification->titulo)
@section('content')
<a class="resident-back-link" href="{{ route('residentes.notificaciones') }}">← Regresar a notificaciones</a>
<article class="panel resident-notification-detail"><span class="announcement-tag">{{ $notification->anuncio ? 'ANUNCIO DE ADMINISTRACIÓN' : 'NOTIFICACIÓN' }}</span><h1>{{ $notification->titulo }}</h1><small>{{ $notification->created_at?->format('d/m/Y · H:i') }}</small><div class="resident-notification-content">{{ $notification->anuncio?->contenido ?? $notification->mensaje }}</div>@if($notification->anuncio)<a class="button button-outline" href="{{ route('residentes.inicio') }}">Volver al inicio</a>@endif</article>
@endsection
