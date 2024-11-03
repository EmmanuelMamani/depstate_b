<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\recibo;
use App\Models\pago;
use App\Models\recibo_detalle;

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
}
