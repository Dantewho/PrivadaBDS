@extends('residentes.layout')
@section('title','Mis tarjetas')
@section('content')
<div class="page-heading"><div>
    <p class="eyebrow">CONTROL DE ACCESO</p>
    <h1>Mis tarjetas</h1>
    <p>La tarjeta se activa cuando administración confirma el pago y registra su entrega.</p>
</div></div>
<section class="panel resident-form-panel">
    <h2>Solicitar tarjeta</h2>
    <form method="post" action="{{ route('residentes.tarjetas.solicitar') }}">
        @csrf
        <label>Tipo de tarjeta
            <select name="tipo" required>
                <option value="peatonal">Peatonal  $250 MXN</option>
                <option value="vehicular">Vehicular  $500 MXN</option>
            </select>
        </label>
        <button class="button button-primary" type="submit" data-processing-text="Enviando solicitud…" aria-live="polite">Solicitar tarjeta</button>
    </form>
</section>
<section class="panel table-panel module-table">
    <div class="panel-heading">
        <h2>Tarjetas registradas</h2>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>TARJETA</th>
                    <th>TIPO</th>
                    <th>PRECIO</th>
                    <th>ESTADO</th>
                    <th>ADEUDO</th>
                    <th>ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cards as $card)
                    <tr>
                        <td>{{ str_starts_with($card->numero_tarjeta,'SOL-')?'Pendiente de asignar':$card->numero_tarjeta }}</td>
                        <td>{{ ucfirst($card->tipo) }}</td>
                        <td>${{ number_format($card->precio,2) }}</td>
                        <td><span class="status {{ $card->estado==='activa'?'status-active':($card->estado==='bloqueada'?'status-inactive':'status-pending') }}">{{ ucfirst($card->estado) }}</span></td>
                        <td><a class="text-action" href="{{ route('residentes.adeudos') }}">Consultar pagos</a></td>
                        <td>@if($card->estado==='pendiente')
                            <form method="post" action="{{ route('residentes.tarjetas.cancelar', $card) }}" onsubmit="return confirm('¿Cancelar esta solicitud? El adeudo se retirará si todavía no reportaste el pago.');">
                                @csrf
                                @method('DELETE')
                                <button class="text-action" type="submit">Cancelar solicitud</button>
                            </form>
                        @else — @endif</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Aún no tienes tarjetas. Puedes solicitar una arriba.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

