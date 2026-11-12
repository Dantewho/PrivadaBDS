<?php

namespace App\Http\Controllers;

use App\Models\MensajeQueja;
use App\Models\Queja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MensajeQuejaController extends Controller
{
    public function store(Request $request, Queja $queja): RedirectResponse
    {
        $user=$request->user();
        abort_unless($user->rol==='administrador' || $queja->usuario_id===$user->id,403);
        abort_unless(in_array($queja->estado,['abierta','en_proceso'],true),422,'La conversación está cerrada.');
        $data=$request->validate(['mensaje'=>['required','string','max:3000']]);
        MensajeQueja::create(['queja_id'=>$queja->id,'usuario_id'=>$user->id,'mensaje'=>$data['mensaje']]);
        if($user->rol==='administrador' && $queja->estado==='abierta')$queja->update(['estado'=>'en_proceso']);
        return back()->with('status','Mensaje enviado.');
    }

    public function moderate(Request $request, MensajeQueja $mensajeQueja): RedirectResponse
    {
        $data=$request->validate(['ofensivo'=>['required','boolean']]);
        $flag=(bool)$data['ofensivo'];
        $mensajeQueja->update(['ofensivo'=>$flag,'moderado_por'=>$flag?$request->user()->id:null,'moderado_at'=>$flag?now():null]);
        return back()->with('status',$flag?'Mensaje marcado como texto ofensivo.':'Se retiró la marca de contenido ofensivo.');
    }
}
