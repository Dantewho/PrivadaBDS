<?php

namespace App\Http\Controllers;

use App\Models\Vivienda;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HistorialController extends Controller
{
    public function index(): View
    {
        return view('administrador.historial', ['historiales' => $this->historiales()]);
    }

    public function pdf(Vivienda $vivienda): StreamedResponse
    {
        $history = $this->historiales()->firstWhere('vivienda.id', $vivienda->id);
        abort_unless($history, 404);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('administrador.pdf.historial', compact('history'))->render(), 'UTF-8');
        $pdf->setPaper('letter', 'portrait');
        $pdf->render();

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'historial-vivienda-'.$history['vivienda']->numero.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    /** @return Collection<int, array<string, mixed>> */
    private function historiales(): Collection
    {
        return Vivienda::with([
            'usuarios:id,name,email,vivienda_id,estado,rol',
            'multas.motivo',
            'multas.usuario',
            'adeudos.pagos',
            'quejas.mensajes.usuario',
            'quejas.mensajes.moderador',
            'quejasRecibidas.mensajes.usuario',
            'quejasRecibidas.mensajes.moderador',
        ])->orderBy('numero')->get()->map(function (Vivienda $vivienda) {
            $quejas = $vivienda->quejas->concat($vivienda->quejasRecibidas)->unique('id');
            $multasPorTipo = $vivienda->multas
                ->groupBy(fn ($multa) => $multa->motivo?->nombre ?? 'Sin tipo')
                ->map(fn ($multas, $tipo) => ['tipo' => $tipo, 'cantidad' => $multas->count()])
                ->sortBy('tipo')->values();
            $adeudosAtrasados = $vivienda->adeudos->filter(function ($adeudo) {
                $vencidoSinPagar = in_array($adeudo->estado, ['pendiente', 'vencido'], true)
                    && $adeudo->fecha_vencimiento->lt(today());
                $pagadoTarde = $adeudo->estado === 'pagado'
                    && $adeudo->updated_at
                    && $adeudo->updated_at->toDateString() > $adeudo->fecha_vencimiento->toDateString();

                return $vencidoSinPagar || $pagadoTarde;
            })->values();
            $mensajesReportados = $quejas->flatMap(fn ($queja) => $queja->mensajes)
                ->where('ofensivo', true)->sortByDesc('created_at')->values();

            return [
                'vivienda' => $vivienda,
                'total_multas' => $vivienda->multas->count(),
                'multas_por_tipo' => $multasPorTipo,
                'quejas_realizadas' => $vivienda->quejas->count(),
                'quejas_recibidas' => $vivienda->quejasRecibidas->count(),
                'pagos_atrasados' => $adeudosAtrasados->count(),
                'adeudos_atrasados' => $adeudosAtrasados,
                'mensajes_reportados' => $mensajesReportados->count(),
                'mensajes_reportados_detalle' => $mensajesReportados,
                'quejas' => $quejas->sortByDesc('created_at')->values(),
            ];
        });
    }
}
