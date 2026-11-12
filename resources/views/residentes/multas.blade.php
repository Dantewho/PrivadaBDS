@extends('residentes.layout')

@section('title', 'Mis multas')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">REGLAMENTO Y CONVIVENCIA</p>
        <h1>Mis multas</h1>
        <p>Consulta el motivo, el importe y la evidencia. Si consideras que una multa es incorrecta, presenta una apelación.</p>
    </div>
    <a class="button button-outline" href="{{ route('residentes.reglamento') }}">Consultar reglamento</a>
</div>

<div class="resident-case-list">
    @forelse($fines as $fine)
        <article class="panel resident-fine-card">
            <div class="resident-fine-top">
                <div>
                    <span class="eyebrow">{{ $fine->fecha?->format('d/m/Y') }} · REINCIDENCIA #{{ $fine->numero_reincidencia }}</span>
                    <h2>{{ $fine->motivo?->nombre ?? 'Multa' }}</h2>
                </div>
                <span class="status {{ $fine->estado === 'pendiente' ? 'status-pending' : 'status-active' }}">{{ ucfirst(str_replace('_', ' ', $fine->estado)) }}</span>
            </div>

            <div class="resident-fine-details">
                <div><small>Importe actual</small><strong>${{ number_format((float) $fine->monto_final, 2) }} MXN</strong></div>
                <div><small>Motivo y descripción</small><p>{{ $fine->descripcion ?: ($fine->motivo?->descripcion ?: 'Consulta el reglamento para conocer este motivo.') }}</p></div>
                @if($fine->evidencia_path)
                    <a class="text-action" href="{{ route('multas.evidencia', $fine) }}">Descargar evidencia de la multa</a>
                @endif
            </div>

            @php($appeal = $fine->apelaciones->first())

            @if($appeal)
                <div class="resident-appeal-status">
                    <span>Apelación: <strong>{{ ucfirst(str_replace('_', ' ', $appeal->estado)) }}</strong></span>
                    @if($appeal->resolucion)
                        <p>{{ $appeal->resolucion }}</p>
                    @endif
                    @if($appeal->monto_resuelto !== null)
                        <small>Importe acordado: ${{ number_format((float) $appeal->monto_resuelto, 2) }} MXN</small>
                    @endif
                    <a class="button button-outline" href="{{ route('residentes.apelaciones') }}">Abrir conversación</a>
                </div>
            @endif

            @if(!$appeal && $fine->estado === 'pendiente')
                <details class="resident-appeal-form">
                    <summary>Apelar esta multa</summary>
                    <form method="post" enctype="multipart/form-data" action="{{ route('residentes.multas.apelar', $fine) }}" data-validate>
                        @csrf
                        <label>Explica por qué consideras injustificada la multa
                            <textarea name="motivo" rows="4" minlength="10" maxlength="3000" required placeholder="Describe los hechos y solicita la revisión..."></textarea>
                        </label>
                        <label>Evidencia opcional (imagen, PDF o Word; máximo 8 MB)
                            <input type="file" name="adjunto" accept="image/jpeg,image/png,image/webp,.pdf,.doc,.docx">
                        </label>
                        <button class="button button-primary" type="submit">Enviar apelación</button>
                    </form>
                </details>
            @endif

            @if($fine->estado === 'pendiente' && $fine->adeudo)
                <details class="resident-payment-details">
                    <summary>Reportar pago</summary>
                    <form class="resident-payment-form" method="post" action="{{ route('residentes.adeudos.pago', $fine->adeudo) }}">
                        @csrf
                        <input type="hidden" name="metodo" value="transferencia">
                        <input name="referencia" required maxlength="150" placeholder="Referencia / folio">
                        <button class="button button-outline" type="submit">Enviar comprobante</button>
                    </form>
                </details>
            @endif
        </article>
    @empty
        <div class="panel admin-empty-state">No tienes multas registradas.</div>
    @endforelse
</div>

<div class="table-footer">{{ $fines->links() }}</div>
@endsection
