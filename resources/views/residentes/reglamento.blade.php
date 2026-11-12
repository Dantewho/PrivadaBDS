@extends('residentes.layout')
@section('title','Reglamento')
@section('content')
<div class="page-heading"><div><p class="eyebrow">DOCUMENTO OFICIAL</p><h1>Reglamento de la privada</h1><p>Consulta las reglas de convivencia y lineamientos de Bahía de Santiago.</p></div><a class="button button-primary" href="{{ $pdfUrl }}?descargar=1">Descargar PDF</a></div>
<section class="panel regulation-pdf"><div><span class="status status-active">VERSIÓN VIGENTE</span><h2>Reglamento interno · Bahía de Santiago</h2><p>El reglamento es la referencia para la convivencia, los accesos y las sanciones.</p></div><iframe title="Reglamento interno de Bahía de Santiago" src="{{ $pdfUrl }}#toolbar=1" loading="lazy"></iframe></section>
@endsection
