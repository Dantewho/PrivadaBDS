<?php

namespace App\Http\Controllers;

use App\Models\Reglamento;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReglamentoController extends Controller
{
    private const PDF_PATH = 'views/reglamento/ReglamentoInterno_BahiaSantiago2025conAnexo.pdf';

    public function resident(): View
    {
        return view('residentes.reglamento', ['pdfUrl' => route('residentes.reglamento.pdf')]);
    }

    public function pdf(Request $request): BinaryFileResponse
    {
        $path = resource_path(self::PDF_PATH);
        abort_unless(is_file($path), 404, 'No se encontró el PDF del reglamento.');

        return $request->boolean('descargar')
            ? response()->download($path, 'Reglamento-Bahia-de-Santiago.pdf', ['Content-Type' => 'application/pdf'])
            : response()->file($path, ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reglamento $reglamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reglamento $reglamento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reglamento $reglamento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reglamento $reglamento)
    {
        //
    }
}
