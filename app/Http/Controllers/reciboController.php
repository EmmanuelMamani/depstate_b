<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\recibo;
use App\Models\recibo_detalle;

class reciboController extends Controller
{
    public function create(Request $request){
        $recibo= new recibo;
        $recibo->recibo=$request->recibo;
        $recibo->pagado=$request->metodo_pago=='ninguno'?false:true;
        $recibo->metodo_pago=$request->metodo_pago;
        $recibo->total=$request->total;
        $recibo->departamento_id=$request->departamento_id;
        $recibo->user_id=$request->user_id;
        $recibo->nombre=$request->nombre;
        $recibo->save();
        $this->create_detalle($request->detalles, $recibo->id);
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
        $detalles=recibo_detalle::where('recibo_id',$id)->orderBy('id', 'desc')->get();
        return response()->json(['detalles'=>$detalles]);
    }
    public function pagar(Request $request, $id){
        $recibo= recibo::find($id);
        $recibo->pagado=true;
        $recibo->metodo_pago=$request->metodo_pago;
        $recibo->save();
        return response()->json($recibo);
    }
}
