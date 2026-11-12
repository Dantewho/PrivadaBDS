@extends('residentes.layout')
@section('title','Facturas')
@section('content')
<div class="page-heading"><div><p class="eyebrow">COMPROBANTES DE PAGO</p><h1>Facturas</h1><p>Facturas y comprobantes emitidos para los pagos de tu vivienda.</p></div></div>
<section class="panel table-panel module-table"><div class="table-scroll"><table><thead><tr><th>FOLIO</th><th>FECHA DE EMISIÓN</th><th>CONCEPTO</th><th>IMPORTE</th><th>ARCHIVO</th></tr></thead><tbody>@forelse($invoices as $invoice)<tr><td>{{ $invoice->folio }}</td><td>{{ $invoice->fecha_emision?->format('d/m/Y') }}</td><td>{{ $invoice->pago?->adeudo?->concepto ?? 'Pago registrado' }}</td><td>${{ number_format((float)$invoice->pago?->monto,2) }} MXN</td><td>@if($invoice->archivo)<a href="{{ $invoice->archivo }}" target="_blank" rel="noopener" class="text-action">Abrir comprobante</a>@else—@endif</td></tr>@empty<tr><td colspan="5">Aún no hay facturas emitidas para tu vivienda.</td></tr>@endforelse</tbody></table></div><div class="table-footer">{{ $invoices->links() }}</div></section>
@endsection
