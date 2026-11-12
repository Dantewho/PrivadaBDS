@extends('residentes.layout')
@section('title', 'Mi vivienda')
@section('content')
@if(!$home)
    <div class="page-heading">
        <div>
            <p class="eyebrow">INFORMACIÓN DE RESIDENCIA</p>
            <h1>Mi vivienda</h1>
            <p>Consulta aquí la información vinculada a tu hogar.</p>
        </div>
    </div>

    <section class="panel resident-layout-card">
        <h2>No tienes una vivienda asignada</h2>
        <p>Tu cuenta todavía no está vinculada a una vivienda. Contacta a administración para que complete tu asignación. Cuando esté lista, aquí podrás consultar a los residentes, las tarjetas y la situación financiera del hogar.</p>
    </section>
@else
    <div class="page-heading">
        <div>
            <p class="eyebrow">INFORMACIÓN DE RESIDENCIA</p>
            <h1>Mi vivienda · #{{ $home->numero }}</h1>
            <p>Resumen de las personas, tarjetas y estado financiero vinculados al hogar.</p>
        </div>
    </div>

    <section class="panel resident-layout-card">
        <div class="resident-home-facts">
            <div><small>Estado de vivienda</small><strong>{{ ucfirst($home->estado) }}</strong></div>
            <div><small>Residentes</small><strong>{{ $home->usuarios->where('rol', 'residente')->count() }} de 2</strong></div>
            <div><small>Tarjetas registradas</small><strong>{{ $home->tarjetas->count() }}</strong></div>
        </div>

        <h2>Personas registradas</h2>
        <div class="resident-data-list">
            @forelse($home->usuarios->where('rol', 'residente') as $resident)
                <div>
                    <span>
                        {{ $resident->name }}
                        @if($home->propietario_id === $resident->id)
                            <small class="status status-active">Propietario</small>
                        @endif
                    </span>
                    <strong>{{ $resident->email }}</strong>
                </div>
            @empty
                <div>No hay residentes asociados.</div>
            @endforelse
        </div>
    </section>

    <div class="resident-home-grid">
        <section class="panel resident-layout-card">
            <h2>Tarjetas de acceso</h2>
            <div class="resident-data-list">
                @forelse($home->tarjetas as $card)
                    <div>
                        <span>{{ ucfirst($card->tipo) }} · {{ $card->numero_tarjeta ?: 'Por asignar' }}</span>
                        <strong>{{ ucfirst($card->estado) }}</strong>
                    </div>
                @empty
                    <div>Aún no hay tarjetas registradas.</div>
                @endforelse
            </div>
            <a class="button button-outline" href="{{ route('residentes.tarjetas') }}">Ver tarjetas</a>
        </section>

        <section class="panel resident-layout-card">
            <h2>Situación financiera</h2>
            <div class="resident-data-list">
                <div>
                    <span>Adeudos sin pagar</span>
                    <strong>${{ number_format((float) $home->adeudos->whereIn('estado', ['pendiente', 'vencido'])->sum('monto'), 2) }} MXN</strong>
                </div>
                <div><span>Multas registradas</span><strong>{{ $home->multas->count() }}</strong></div>
                <div><span>Multas pendientes</span><strong>{{ $home->multas->where('estado', 'pendiente')->count() }}</strong></div>
            </div>
            <a class="button button-outline" href="{{ route('residentes.adeudos') }}">Consultar pagos</a>
        </section>
    </div>
@endif
@endsection
