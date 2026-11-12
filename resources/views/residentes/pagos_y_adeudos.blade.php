@extends('residentes.layout')
@section('title', 'Adeudos y pagos')
@section('content')
<div class="page-heading">
    <div><p class="eyebrow">CUENTA DE LA VIVIENDA</p><h1>Adeudos y pagos</h1><p>Reporta tu pago para que administración lo valide.</p></div>
</div>
<section class="panel table-panel module-table">
    <div class="table-scroll"><table>
        <thead><tr><th>CONCEPTO</th><th>PERIODO</th><th>IMPORTE</th><th>VENCIMIENTO</th><th>ESTADO</th><th>PAGO</th></tr></thead>
        <tbody>
        @forelse($debts as $debt)
            @php($latestPayment = $debt->pagos->sortByDesc('fecha_pago')->first())
            <tr>
                <td>{{ $debt->concepto }}</td><td>{{ $debt->periodo }}</td><td>${{ number_format($debt->monto, 2) }} MXN</td><td>{{ $debt->fecha_vencimiento->format('d/m/Y') }}</td>
                <td><span class="status {{ in_array($debt->estado, ['pagado', 'condonado'], true) ? 'status-paid' : 'status-pending' }}">{{ ucfirst($debt->estado) }}</span></td>
                <td>
                    @if($debt->estado === 'pagado') Confirmado
                    @elseif($debt->estado === 'condonado') Adeudo condonado
                    @elseif($latestPayment?->estado === 'pendiente') En revisión · {{ $latestPayment->referencia }}
                    @elseif($latestPayment?->estado === 'rechazado') Pago rechazado. Puedes reportarlo de nuevo.
                    @else
                        <form class="resident-payment-form" method="post" action="{{ route('residentes.adeudos.pago', $debt) }}">@csrf
                            <select name="metodo"><option value="transferencia">Transferencia</option><option value="efectivo">Efectivo</option></select>
                            <input name="referencia" placeholder="Referencia / folio"><button class="text-action">Reportar pago</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6">No tienes adeudos registrados.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="table-footer"><span>{{ $debts->firstItem() ?? 0 }}–{{ $debts->lastItem() ?? 0 }} de {{ $debts->total() }}</span>{{ $debts->links() }}</div>
</section>
@endsection
