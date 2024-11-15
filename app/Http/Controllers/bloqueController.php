<?php

namespace App\Http\Controllers;
use App\Models\bloque;
use App\Models\departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class bloqueController extends Controller
{
    public function create(Request $request){
        $bloque= new bloque;
        $bloque->bloque=$request->bloque;
        $bloque->save();
         return response()->json($bloque);
    }
    public function bloques(){
        $bloques = bloque::orderBy('id', 'asc')->get();
        return response()->json($bloques);
    }
    public function departamentos($id){
        $departamentos = departamento::where('bloque_id',$id)->orderBy('id', 'asc')->get();
        $bloque = bloque::find($id);
        return response()->json([
            'departamentos' => $departamentos,
            'bloque' => $bloque
        ]);
    }
    
    public function recibos_mes(Request $request){
        $sql="SELECT d.departamento,d.bloque_id,r.total,r.saldo,r.pagado,r.recibo,r.fecha_recibo,r.mes_correspondiente,
                    CASE 
                        WHEN r.pagado IS NULL THEN 'sin recibo'
                        WHEN r.pagado = false THEN 'sin pagar'
                        WHEN r.pagado = true THEN 'pagado'
                    END AS estado
                from departamentos d 
                left join recibos r on d.id =r.departamento_id  and TO_CHAR(r.fecha_recibo, 'YYYY-MM') = '$request->fecha'
                where d.bloque_id =$request->bloque 
                order by d.id";
        $departamentos = DB::select($sql);
        return response()->json($departamentos);
    }
}
