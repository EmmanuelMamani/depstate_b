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
        $recibo->fecha_recibo=$request->fecha_recibo;
        $recibo->mes_correspondiente=$request->mes_correspondiente;
        $recibo->save();
        $this->create_detalle($request->detalles, $recibo->id,$request->fecha_recibo);
        if($request->metodo_pago!='ninguno'){
            $this->pagar($request,$recibo->id);
        }
        return response()->json($recibo);
    }

    public function recibos(Request $request){
        $recibos = DB::table('recibos')
            ->select('recibos.*', 'departamentos.departamento', 'bloques.bloque')
            ->join('departamentos', 'departamentos.id', '=', 'recibos.departamento_id')
            ->join('bloques', 'bloques.id', '=', 'departamentos.bloque_id')
            ->whereRaw("TO_CHAR(recibos.fecha_recibo, 'YYYY-MM') = ?", [$request->fecha])
            ->orderByRaw('recibos.recibo::NUMERIC DESC')
            ->get();
        
        return response()->json($recibos);
    }    

    private function create_detalle($detalles,$recibo_id,$fecha){
        foreach($detalles as $detalle){
            $new_detalle= new recibo_detalle;
            $new_detalle->detalle = $detalle['detalle'];
            $new_detalle->monto = $detalle['monto'];
            $new_detalle->recibo_id = $recibo_id;
            $new_detalle->created_at= $fecha;
            $new_detalle->updated_at= $fecha;
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
        if($request->fecha_recibo){
            $pago->created_at=$request->fecha_recibo;
            $pago->updated_at=$request->fecha_recibo;
        }
        $pago->save();
        return response()->json($recibo);
    }

    public function reporte_detalles(Request $request){
        $expensa= $this->detalles_fechas($request->inicio,$request->fin,'expensa',$request->bloque??null);
        $agua= $this->detalles_fechas($request->inicio,$request->fin,'agua',$request->bloque??null);
        $piscina= $this->detalles_fechas($request->inicio,$request->fin,'piscina',$request->bloque??null);
        $local= $this->detalles_fechas($request->inicio,$request->fin,'local',$request->bloque??null);
        $multa= $this->detalles_fechas($request->inicio,$request->fin,'multa',$request->bloque??null);
        $otros= $this->detalles_fechas($request->inicio,$request->fin,'otros',$request->bloque??null);
        $efectivo= $this->metodo_pago_fechas($request->inicio,$request->fin,'efectivo',$request->bloque??null);
        $tarjeta= $this->metodo_pago_fechas($request->inicio,$request->fin,'tarjeta',$request->bloque??null);
        return response()->json([
            ['detalle' => 'Expensa','monto' => $expensa,'icono' => 'material-symbols:apartment'],
            ['detalle' => 'Agua','monto' => $agua,'icono' => 'material-symbols:water-drop'],
            ['detalle' => 'Piscina','monto' => $piscina,'icono' => 'material-symbols:pool'],
            ['detalle' => 'Local','monto' => $local,'icono' => 'material-symbols:store'],
            ['detalle' => 'Multa','monto' => $multa,'icono' => 'material-symbols:document-scanner',],
            ['detalle' => 'Otros','monto' => $otros,'icono' => 'material-symbols:window-sharp'],
            ['detalle' => 'Efectivo','monto' => $efectivo,'icono' => 'material-symbols:attach-money',],
            ['detalle' => 'Tarjeta','monto' => $tarjeta,'icono' => 'material-symbols:credit-card']
        ]);
        
    }
    public function reporte_detalles_mes_correspondiente(Request $request){
        $expensa= $this->detalles_mes_correspondiente($request->fecha,'expensa',$request->bloque);
        $agua= $this->detalles_mes_correspondiente($request->fecha,'agua',$request->bloque);
        $piscina= $this->detalles_mes_correspondiente($request->fecha,'piscina',$request->bloque);
        $local= $this->detalles_mes_correspondiente($request->fecha,'local',$request->bloque);
        $multa= $this->detalles_mes_correspondiente($request->fecha,'multa',$request->bloque);
        $otros= $this->detalles_mes_correspondiente($request->fecha,'otros',$request->bloque);
        $efectivo= $this->metodo_pago_mes_correspondiente($request->fecha,'efectivo',$request->bloque);
        $tarjeta= $this->metodo_pago_mes_correspondiente($request->fecha,'tarjeta',$request->bloque);
        return response()->json([
            ['detalle' => 'Expensa','monto' => $expensa,'icono' => 'material-symbols:apartment'],
            ['detalle' => 'Agua','monto' => $agua,'icono' => 'material-symbols:water-drop'],
            ['detalle' => 'Piscina','monto' => $piscina,'icono' => 'material-symbols:pool'],
            ['detalle' => 'Local','monto' => $local,'icono' => 'material-symbols:store'],
            ['detalle' => 'Multa','monto' => $multa,'icono' => 'material-symbols:document-scanner',],
            ['detalle' => 'Otros','monto' => $otros,'icono' => 'material-symbols:window-sharp'],
            ['detalle' => 'Efectivo','monto' => $efectivo,'icono' => 'material-symbols:attach-money',],
            ['detalle' => 'Tarjeta','monto' => $tarjeta,'icono' => 'material-symbols:credit-card']
        ]);
        
    }

    private function detalles_fechas($inicio, $fin, $detalle, $bloqueId = null) {

        $query = recibo_detalle::where('detalle', 'ilike', "%$detalle%")
            ->whereBetween('created_at', [$inicio, $fin]);

        if ($bloqueId !== null) {
            $query->whereHas('recibo.departamento', function ($q) use ($bloqueId) {
                $q->where('bloque_id', $bloqueId);
            });
        }
    
        return $query->sum('monto') ?? 0;
    }
    
    private function metodo_pago_fechas($inicio, $fin, $metodo, $bloqueId = null) {
        $query = pago::where('metodo', $metodo)
            ->whereBetween('created_at', [$inicio, $fin]);
    
        if ($bloqueId !== null) {
            $query->whereHas('recibo.departamento', function ($q) use ($bloqueId) {
                $q->where('bloque_id', $bloqueId);
            });
        }

        return $query->sum('monto')??0;
  
    }
    public function delete($id){
        $recibo = recibo::find($id);
        $recibo->delete();
        return response()->json('Eliminado');
    }
    public function buscar($n_recibo){
        $recibo = recibo::where('recibo',$n_recibo)->get()->last();
        $detalles=recibo_detalle::where('recibo_id',$recibo->id)->orderBy('id', 'asc')->get();
        return response()->json(['recibo'=>$recibo,'detalles'=>$detalles]);
    }
    private function detalles_mes_correspondiente($fecha,$detalle,$bloque){
        $total = DB::select("SELECT SUM(rd.monto) as total
                            FROM recibo_detalles rd
                            WHERE rd.detalle ILIKE '%$detalle%'
                            AND rd.recibo_id IN 
                                (SELECT r.id
                                FROM recibos r
                                INNER JOIN departamentos d ON d.id = r.departamento_id
                                WHERE r.mes_correspondiente = '$fecha'
                                AND d.bloque_id = $bloque)");
        return $total[0]->total??0;
    }
    private function metodo_pago_mes_correspondiente($fecha,$metodo,$bloque){
        $total = DB::select("SELECT sum(p.monto) AS total
                            FROM pagos p
                            WHERE p.metodo ilike '%$metodo%'
                            AND p.recibo_id IN 
                            (SELECT r.id 
                            FROM recibos r 
                            INNER JOIN departamentos d ON d.id=r.departamento_id
                            WHERE r.mes_correspondiente ='$fecha' and d.bloque_id=$bloque)");
        return $total[0]->total??0;
    }
}
