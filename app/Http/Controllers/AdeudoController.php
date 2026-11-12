<?php

namespace App\Http\Controllers;

use App\Models\Adeudo;
use App\Models\Pago;
use App\Models\Vivienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdeudoController extends Controller
{
    public function index(Request $request): View
    {
        $debts=Adeudo::with(['vivienda','tarjeta','multa','pagos'=>fn($q)=>$q->latest('fecha_pago')])->when($request->filled('q'),fn($q)=>$q->where(fn($q)=>$q->where('concepto','like','%'.$request->q.'%')->orWhereHas('vivienda',fn($h)=>$h->where('numero','like','%'.$request->q.'%'))))
            ->when($request->filled('estado'),fn($q)=>$q->where('estado',$request->estado))
            ->when($request->filled('periodo'),fn($q)=>$q->where('periodo',$request->periodo))
            ->orderByDesc('created_at')->paginate(15)->withQueryString();
        $pendingPayments=Pago::with(['adeudo.vivienda','adeudo.tarjeta','usuario'])->where('estado','pendiente')->latest('fecha_pago')->get();
        return view('administrador.adeudos',['debts'=>$debts,'homes'=>Vivienda::where('estado','activa')->orderBy('numero')->get(),'pendingPayments'=>$pendingPayments,'stats'=>['pendiente'=>Adeudo::whereIn('estado',['pendiente','vencido'])->sum('monto'),'pagado'=>Adeudo::where('estado','pagado')->count(),'por_pagar'=>Adeudo::where('estado','pendiente')->count(),'vencido'=>Adeudo::where('estado','pendiente')->whereDate('fecha_vencimiento','<',today())->count()]]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate(['concepto'=>['required','string','max:255'],'periodo'=>['required','string','max:20'],'monto'=>['required','numeric','min:0.01'],'fecha_vencimiento'=>['required','date'],'vivienda_id'=>['nullable','exists:viviendas,id']]);
        $created = DB::transaction(function () use ($data) {
            $homes = (isset($data['vivienda_id'])
                ? Vivienda::whereKey($data['vivienda_id'])
                : Vivienda::where('estado', 'activa'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $created = 0;

            foreach ($homes as $home) {
                $exists = Adeudo::where('vivienda_id', $home->id)
                    ->where('concepto', $data['concepto'])
                    ->where('periodo', $data['periodo'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                Adeudo::create([
                    'vivienda_id' => $home->id,
                    'concepto' => $data['concepto'],
                    'periodo' => $data['periodo'],
                    'monto' => $data['monto'],
                    'fecha_vencimiento' => $data['fecha_vencimiento'],
                    'estado' => 'pendiente',
                ]);
                $created++;
            }

            return $created;
        }, 3);

        return back()->with('status', "Se generaron {$created} adeudos nuevos; los cargos existentes se omitieron.");
    }

    public function residentIndex(): View
    {
        $user=request()->user();
        $debts=Adeudo::with(['tarjeta','pagos'=>fn($q)=>$q->latest()])->where('vivienda_id',$user->vivienda_id)->orderByDesc('created_at')->paginate(15);
        return view('residentes.pagos_y_adeudos',compact('debts'));
    }

    public function reportPayment(Request $request, Adeudo $adeudo): RedirectResponse
    {
        $user=$request->user();
        abort_unless($adeudo->vivienda_id===$user->vivienda_id,403);
        abort_unless($adeudo->estado==='pendiente',422,'Este adeudo no está pendiente.');
        $data=$request->validate(['metodo'=>['required',Rule::in(['transferencia','efectivo'])],'referencia'=>['nullable','string','max:150']]);
        DB::transaction(function()use($adeudo,$user,$data){
            $locked=Adeudo::whereKey($adeudo->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->estado==='pendiente',422,'Este adeudo ya fue pagado o actualizado.');
            abort_if(Pago::where('adeudo_id',$locked->id)->where('estado','pendiente')->exists(),422,'Ya hay un pago reportado que está en revisión.');
            Pago::create(['adeudo_id'=>$locked->id,'usuario_id'=>$user->id,'monto'=>$locked->monto,'metodo'=>$data['metodo'],'referencia'=>$data['referencia']??null,'estado'=>'pendiente','fecha_pago'=>now()]);
        });
        return back()->with('status','Pago reportado. Administración debe confirmarlo para saldar el adeudo.');
    }

    public function confirmPayment(Pago $pago): RedirectResponse
    {
        DB::transaction(function() use($pago){
            $payment=Pago::whereKey($pago->id)->lockForUpdate()->firstOrFail();
            abort_unless($payment->estado==='pendiente',422,'El pago ya fue revisado.');
            $debt=Adeudo::whereKey($payment->adeudo_id)->lockForUpdate()->firstOrFail();
            $payment->update(['estado'=>'pagado']);
            $debt->update(['estado'=>'pagado']);
            if($debt->tarjeta_id){$debt->tarjeta()->update(['estado'=>'pagada']);}
            if($debt->multa_id){$debt->multa()->update(['estado'=>'pagada']);}
        });
        return back()->with('status','Pago confirmado; el adeudo quedó saldado.');
    }
}
