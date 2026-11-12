<?php

namespace App\Http\Controllers;

use App\Models\MensajeQueja;
use App\Models\Queja;
use App\Support\ValidacionesEntrada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuejaController extends Controller
{
    public function index(Request $request): View
    {
        $complaints=Queja::with(['usuario.vivienda','vivienda','viviendaReportada','mensajes.usuario.vivienda','mensajes.moderador'])
            ->when($request->filled('q'),fn($q)=>$q->where(fn($search)=>$search->where('asunto','like','%'.$request->q.'%')->orWhereHas('vivienda',fn($h)=>$h->where('numero','like','%'.$request->q.'%'))->orWhereHas('viviendaReportada',fn($h)=>$h->where('numero','like','%'.$request->q.'%'))->orWhereHas('usuario',fn($u)=>$u->where('name','like','%'.$request->q.'%'))))
            ->when($request->filled('estado'),fn($q)=>$q->where('estado',$request->estado))->latest()->paginate(10)->withQueryString();
        return view('administrador.quejas',['complaints'=>$complaints,'stats'=>['total'=>Queja::count(),'abiertas'=>Queja::where('estado','abierta')->count(),'proceso'=>Queja::where('estado','en_proceso')->count(),'resueltas'=>Queja::whereIn('estado',['resuelta','cerrada'])->count()]]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user=$request->user(); abort_unless($user->rol==='residente' && $user->vivienda_id,403);
        $data=$request->validate([
            'asunto'=>ValidacionesEntrada::texto(true,150),
            'descripcion'=>['required','string','min:10','max:5000'],
            'vivienda_reportada_id'=>['nullable','exists:viviendas,id',Rule::notIn([$user->vivienda_id])],
        ]);
        DB::transaction(function()use($user,$data){$complaint=Queja::create(['vivienda_id'=>$user->vivienda_id,'vivienda_reportada_id'=>$data['vivienda_reportada_id']??null,'usuario_id'=>$user->id,'asunto'=>$data['asunto'],'descripcion'=>$data['descripcion'],'estado'=>'abierta']);MensajeQueja::create(['queja_id'=>$complaint->id,'usuario_id'=>$user->id,'mensaje'=>$data['descripcion']]);});
        return back()->with('status','Queja enviada a administración.');
    }

    public function residentIndex(): View
    {
        $user=request()->user();
        $complaints=Queja::with(['mensajes.usuario.vivienda','mensajes.moderador','viviendaReportada'])->where('usuario_id',$user->id)->latest()->paginate(10);
        $homes=\App\Models\Vivienda::where('id','!=',$user->vivienda_id)->where('estado','activa')->orderBy('numero')->get();
        return view('residentes.quejas',compact('complaints','homes'));
    }

    public function update(Request $request, Queja $queja): RedirectResponse
    {
        $data=$request->validate(['estado'=>['required','in:abierta,en_proceso,resuelta,cerrada']]);
        $queja->update($data);
        return back()->with('status','Estado de queja actualizado.');
    }
}
