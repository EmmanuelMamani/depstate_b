<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\recibo;
use App\Models\pago;
use App\Models\recibo_detalle;
use Illuminate\Support\Facades\DB;

class reciboController extends Controller
{

    public function create(Request $request){
        if (Recibo::where('recibo', $request->recibo)->exists()) {
            return response()->json(['error' => 'El recibo ya existe.'], 400);
        }
        $recibo= new recibo;
        $recibo->recibo=$request->recibo;
        $recibo->pagado=$request->metodo_pago=='ninguno'?false:true;
        $recibo->total=$request->total;
        $recibo->saldo=$request->total;
        $recibo->departamento_id=$request->departamento_id;
        $recibo->user_id=$request->user_id;
        $recibo->nombre=$request->nombre;
        $recibo->nota=$request->nota;
        $recibo->save();
        $this->create_detalle($request->detalles, $recibo->id);
        if($request->metodo_pago!='ninguno'){
            $this->pagar($request,$recibo->id);
        }
        return response()->json($recibo);
    }

    private function create_detalle($detalles,$recibo_id){
        foreach($detalles as $detalle){
            $new_detalle= new recibo_detalle;
            $new_detalle->detalle = $detalle['detalle'];
            $new_detalle->monto = $detalle['monto'];
            $new_detalle->recibo_id = $recibo_id;
            $new_detalle->save();
        }
    }

    public function detalles($id){
        $detalles=recibo_detalle::where('recibo_id',$id)->orderBy('id', 'asc')->get();
        return response()->json(['detalles'=>$detalles]);
    }

    public function pagar(Request $request, $id){
        $recibo= recibo::find($id);
        $recibo->saldo-=$request->total;
        $recibo->pagado=$recibo->saldo==0?true:false;
        $recibo->save();
        $pago= new pago;
        $pago->recibo_id=$id;
        $pago->metodo=$request->metodo_pago;
        $pago->monto=$request->total;
        $pago->save();
        return response()->json($recibo);
    }

    public function reporte_detalles(Request $request){
        $expensa= $this->detalles_fechas($request->inicio,$request->fin,'expensa');
        $agua= $this->detalles_fechas($request->inicio,$request->fin,'agua');
        $piscina= $this->detalles_fechas($request->inicio,$request->fin,'piscina');
        $local= $this->detalles_fechas($request->inicio,$request->fin,'local');
        $multa= $this->detalles_fechas($request->inicio,$request->fin,'multa');
        $otros= $this->detalles_fechas($request->inicio,$request->fin,'otros');
        $efectivo= $this->metodo_pago_fechas($request->inicio,$request->fin,'efectivo');
        $tarjeta= $this->metodo_pago_fechas($request->inicio,$request->fin,'tarjeta');
        return response()->json( [
            'expesa'=>$expensa,
            'agua'=>$agua,
            'piscina'=>$piscina,
            'local'=>$local,
            'multa'=>$multa,
            'otros'=>$otros,
            'efectivo'=>$efectivo,
            'tarjeta'=>$tarjeta
        ]);
    }

    private function detalles_fechas($inicio,$fin,$detalle){
        $detalle = DB::table('recibo_detalles as rd')
                    ->join('recibos as r', function ($join) {
                        $join->on('r.id', '=', 'rd.recibo_id')
                            ->where('r.pagado', true);
                    })
                    ->where('rd.detalle', 'ILIKE', "%$detalle%")
                    ->whereDate('rd.updated_at', '>=', "$inicio")
                    ->whereDate('rd.updated_at', '<=', "$fin")
                    ->sum('rd.monto');
        return $detalle ?? 0;
    }
    private function metodo_pago_fechas($inicio,$fin,$metodo){
        $detalle = pago::where('metodo',$metodo)
                    ->whereDate('updated_at', '>=', "$inicio")
                    ->whereDate('updated_at', '<=', "$fin")
                    ->sum('monto');
        return $detalle ?? 0;
    }
}
