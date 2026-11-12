<?php

namespace App\Http\Controllers;

use App\Models\Acceso;
use App\Models\Tarjeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccesoController extends Controller
{
    public function index(Request $request): View
    {
        $accesses=Acceso::with(['vivienda','tarjeta.usuario'])->when($request->filled('q'),fn($q)=>$q->where(fn($q)=>$q->whereHas('vivienda',fn($h)=>$h->where('numero','like','%'.$request->q.'%'))->orWhereHas('tarjeta',fn($c)=>$c->where('numero_tarjeta','like','%'.$request->q.'%')->orWhereHas('usuario',fn($u)=>$u->where('name','like','%'.$request->q.'%')))))
            ->when($request->filled('puerta'),fn($q)=>$q->where('puerta',$request->puerta))
            ->when($request->filled('movimiento'),fn($q)=>$q->where('movimiento',$request->movimiento))
            ->when($request->filled('fecha'),fn($q)=>$q->whereDate('fecha_hora',$request->fecha))
            ->orderByDesc('fecha_hora')->paginate(20)->withQueryString();
        return view('administrador.accesos',['accesses'=>$accesses,'stats'=>['hoy'=>Acceso::whereDate('fecha_hora',today())->count(),'entradas'=>Acceso::whereDate('fecha_hora',today())->where('movimiento','entrada')->count(),'salidas'=>Acceso::whereDate('fecha_hora',today())->where('movimiento','salida')->count()],'cards'=>Tarjeta::with('vivienda')->where('estado','activa')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate(['tarjeta_id'=>['required','exists:tarjetas,id'],'puerta'=>['required',Rule::in(['vehicular','peatonal'])],'movimiento'=>['required',Rule::in(['entrada','salida'])]]);
        $card=Tarjeta::findOrFail($data['tarjeta_id']);
        abort_unless($card->estado==='activa' && $card->tipo===$data['puerta'],422,'La tarjeta debe estar activa y corresponder a esa puerta.');
        Acceso::create($data+['vivienda_id'=>$card->vivienda_id,'fecha_hora'=>now()]);
        return back()->with('status','Movimiento de acceso registrado.');
    }

    public function residentIndex(): View
    {
        $user=request()->user();
        $accesses=Acceso::with('tarjeta')->where('vivienda_id',$user->vivienda_id)->whereHas('tarjeta',fn($q)=>$q->where('usuario_id',$user->id))->orderByDesc('fecha_hora')->paginate(20);
        return view('residentes.accesos',compact('accesses'));
    }
}
