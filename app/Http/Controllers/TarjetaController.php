<?php

namespace App\Http\Controllers;

use App\Models\Adeudo;
use App\Models\Tarjeta;
use App\Models\User;
use App\Models\Vivienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TarjetaController extends Controller
{
    public function index(Request $request): View
    {
        $cards = Tarjeta::with(['vivienda', 'usuario'])->when($request->filled('q'), fn($q) => $q->where(fn($q)=>$q->where('numero_tarjeta','like','%'.$request->q.'%')->orWhereHas('usuario',fn($u)=>$u->where('name','like','%'.$request->q.'%'))->orWhereHas('vivienda',fn($h)=>$h->where('numero','like','%'.$request->q.'%'))))
            ->when($request->filled('tipo'), fn($q)=>$q->where('tipo',$request->tipo))
            ->when($request->filled('estado'), fn($q)=>$q->where('estado',$request->estado))
            ->latest()->paginate(12)->withQueryString();
        return view('administrador.tarjetas', [
            'cards'=>$cards,
            'stats'=>['total'=>Tarjeta::count(),'activas'=>Tarjeta::where('estado','activa')->count(),'pendientes'=>Tarjeta::whereIn('estado',['pendiente','pagada'])->count(),'bloqueadas'=>Tarjeta::where('estado','bloqueada')->count()],
            'homes'=>Vivienda::orderBy('numero')->get(),
            'residents'=>User::where('rol','residente')->where('estado','activo')->with('vivienda')->orderBy('name')->get(),
        ]);
    }

    public function residentIndex(): View
    {
        $user = request()->user();
        return view('residentes.tarjetas_de_acceso', ['cards'=>$user->tarjetas()->with('vivienda')->latest()->get()]);
    }

    public function requestCard(Request $request): RedirectResponse
    {
        $user=$request->user();
        abort_unless($user->rol==='residente' && $user->vivienda_id, 403, 'Tu cuenta debe tener una vivienda asignada.');
        $data=$request->validate(['tipo'=>['required',Rule::in(['peatonal','vehicular'])]]);
        $price=$data['tipo']==='peatonal' ? 250 : 500;
        DB::transaction(function () use ($user, $data, $price) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless(
                $lockedUser->rol === 'residente' && $lockedUser->estado === 'activo' && $lockedUser->vivienda_id,
                403,
                'Tu cuenta debe tener una vivienda asignada.'
            );

            $unpaidRequests = Tarjeta::where('usuario_id', $lockedUser->id)
                ->whereHas('adeudos', fn ($query) => $query->whereIn('estado', ['pendiente', 'vencido']))
                ->count();

            if ($unpaidRequests >= 2) {
                throw ValidationException::withMessages([
                    'tipo' => 'Ya tienes dos solicitudes de tarjeta con adeudos pendientes. Paga una antes de solicitar otra.',
                ]);
            }

            $card = Tarjeta::create([
                'vivienda_id' => $lockedUser->vivienda_id,
                'usuario_id' => $lockedUser->id,
                'numero_tarjeta' => 'SOL-'.Str::upper(Str::random(12)),
                'tipo' => $data['tipo'],
                'precio' => $price,
                'estado' => 'pendiente',
            ]);

            Adeudo::create([
                'vivienda_id' => $lockedUser->vivienda_id,
                'tarjeta_id' => $card->id,
                'concepto' => 'Tarjeta '.($data['tipo'] === 'peatonal' ? 'peatonal' : 'vehicular').' #'.$card->id,
                'periodo' => 'tarjeta-'.$card->id,
                'monto' => $price,
                'fecha_vencimiento' => now()->addDays(15)->toDateString(),
                'estado' => 'pendiente',
            ]);
        }, 3);
        return back()->with('status','Solicitud registrada. El adeudo de la tarjeta ya aparece en tus pagos.');
    }

    public function cancelRequest(Request $request, Tarjeta $tarjeta): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($tarjeta, $user) {
            $card = Tarjeta::whereKey($tarjeta->id)->lockForUpdate()->firstOrFail();
            abort_unless($card->usuario_id === $user->id, 403);
            abort_unless($card->estado === 'pendiente', 422, 'Solo puedes cancelar una solicitud que siga pendiente.');

            $debt = Adeudo::where('tarjeta_id', $card->id)->lockForUpdate()->first();
            abort_if($debt && ($debt->estado !== 'pendiente' || $debt->pagos()->exists()), 422, 'No se puede cancelar esta solicitud porque su pago ya fue reportado o procesado.');

            $debt?->delete();
            $card->delete();
        });

        return back()->with('status', 'Solicitud de tarjeta cancelada. El adeudo asociado también fue retirado.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate([
            'numero_tarjeta'=>['required','string','max:100','unique:tarjetas,numero_tarjeta'],
            'vivienda_id'=>['required','exists:viviendas,id'],
            'usuario_id'=>['nullable','exists:users,id'],
            'tipo'=>['required',Rule::in(['peatonal','vehicular'])],
        ]);
        $price=$data['tipo']==='peatonal'?250:500;
        if(!empty($data['usuario_id']))abort_unless(User::whereKey($data['usuario_id'])->where('rol','residente')->where('estado','activo')->where('vivienda_id',$data['vivienda_id'])->exists(),422,'El residente seleccionado debe pertenecer a esa vivienda.');
        DB::transaction(function() use($data,$price) {
            $card=Tarjeta::create($data+['precio'=>$price,'estado'=>'pendiente']);
            Adeudo::create(['vivienda_id'=>$card->vivienda_id,'tarjeta_id'=>$card->id,'concepto'=>'Tarjeta '.($card->tipo==='peatonal'?'peatonal':'vehicular').' #'.$card->id,'periodo'=>'tarjeta-'.$card->id,'monto'=>$price,'fecha_vencimiento'=>now()->addDays(15)->toDateString(),'estado'=>'pendiente']);
        });
        return back()->with('status','Tarjeta registrada como pendiente y se generó su adeudo.');
    }

    public function update(Request $request, Tarjeta $tarjeta): RedirectResponse
    {
        $data=$request->validate(['numero_tarjeta'=>['required','string','max:100',Rule::unique('tarjetas')->ignore($tarjeta->id)],'estado'=>['required',Rule::in(['pendiente','pagada','activa','bloqueada'])]]);
        if($data['estado']==='activa') abort_unless($tarjeta->adeudos()->where('estado','pagado')->exists(),422,'Confirma primero el pago de esta tarjeta.');
        $tarjeta->update($data+['fecha_activacion'=>$data['estado']==='activa'?now():$tarjeta->fecha_activacion]);
        return back()->with('status','Tarjeta actualizada.');
    }
}
