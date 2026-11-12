@extends('residentes.layout')
@section('title', 'Mis quejas')
@section('content')
<div class="page-heading"><div><p class="eyebrow">ATENCIÓN A RESIDENTES</p><h1>Mis quejas</h1><p>Envía reportes y conversa con administración desde aquí.</p></div></div>
<section class="panel resident-form-panel">
    <h2>Crear queja</h2>
    <form method="post" action="{{ route('residentes.quejas.store') }}" data-validate-form>@csrf
        <label>Asunto<input name="asunto" maxlength="150" data-validation="text" required></label>
        <label>Vivienda reportada (opcional)<select name="vivienda_reportada_id"><option value="">No corresponde a otra vivienda</option>@foreach($homes as $home)<option value="{{ $home->id }}">Casa #{{ $home->numero }}</option>@endforeach</select></label>
        <label>Descripción<textarea name="descripcion" rows="3" minlength="10" maxlength="5000" required></textarea></label>
        <button class="button button-primary">Enviar queja</button>
    </form>
</section>
<div class="resident-case-list">
    @forelse($complaints as $complaint)
        <article class="panel resident-case">
            <header><div><strong>{{ $complaint->asunto }}</strong><small>Casa #{{ $complaint->vivienda?->numero }} · {{ $complaint->created_at->format('d/m/Y') }}@if($complaint->viviendaReportada) · Reporte sobre casa #{{ $complaint->viviendaReportada->numero }}@endif</small></div><span class="status status-pending">{{ ucfirst(str_replace('_', ' ', $complaint->estado)) }}</span></header>
            <div class="message-thread">
                @foreach($complaint->mensajes as $message)
                    <div class="thread-message {{ $message->usuario->rol === 'administrador' ? 'admin-message' : 'resident-message' }} {{ $message->ofensivo ? 'message-offensive' : '' }}">
                        <small>{{ $message->autorEtiqueta() }} · {{ $message->created_at?->format('d/m H:i') }} @if($message->ofensivo)<strong>Texto marcado como ofensivo</strong>@endif</small><p>{{ $message->mensaje }}</p>
                    </div>
                @endforeach
            </div>
            @if(in_array($complaint->estado, ['abierta', 'en_proceso']))<form method="post" action="{{ route('residentes.quejas.mensaje', $complaint) }}" class="resident-reply-form" data-validate-form>@csrf<textarea name="mensaje" rows="2" maxlength="3000" placeholder="Escribe un mensaje" required></textarea><button class="button button-primary">Enviar</button></form>@endif
        </article>
    @empty
        <div class="panel admin-empty-state">Aún no has enviado quejas.</div>
    @endforelse
</div>
<div class="table-footer">{{ $complaints->links() }}</div>
@endsection
