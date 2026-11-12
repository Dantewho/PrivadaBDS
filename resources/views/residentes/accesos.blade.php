@extends('residentes.layout')
@section('title','Mis accesos')
@section('content')
<div class="page-heading"><div>
    <p class="eyebrow">HISTORIAL</p>
    <h1>Mis accesos</h1>
    <p>Movimientos registrados con tus tarjetas activas.</p>
</div>
</div>
<section class="panel table-panel module-table">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>TARJETA</th>
                    <th>PUERTA</th>
                    <th>MOVIMIENTO</th>
                    <th>VIVIENDA</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accesses as $access)
                    <tr>
                        <td>{{ $access->fecha_hora->format('d/m/Y H:i') }}</td>
                        <td>{{ $access->tarjeta?->numero_tarjeta }}</td>
                        <td>{{ ucfirst($access->puerta) }}</td>
                        <td>{{ ucfirst($access->movimiento) }}</td>
                        <td>#{{ $access->vivienda_id }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Aún no hay movimientos de acceso.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <span>{{ $accesses->firstItem()??0 }}–{{ $accesses->lastItem()??0 }} de {{ $accesses->total() }}</span>
        {{ $accesses->links() }}
    </div>
</section>
@endsection
