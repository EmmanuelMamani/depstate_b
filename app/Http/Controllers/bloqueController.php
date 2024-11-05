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
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $mes = $meses[$request->mes];
        $sql="SELECT d.departamento,d.bloque_id,re.total,re.saldo,re.pagado,re.recibo,
                    CASE 
                        WHEN re.pagado IS NULL THEN 'sin recibo'
                        WHEN re.pagado = false THEN 'sin pagar'
                        WHEN re.pagado = true THEN 'pagado'
                    END AS estado
                FROM 
                    departamentos d
                LEFT JOIN (
                    SELECT r.* 
                    FROM recibo_detalles rd 
                    INNER JOIN recibos r ON r.id = rd.recibo_id AND rd.detalle ILIKE '%$mes%'
                    where r.gestion=$request->gestion
                ) AS re ON re.departamento_id = d.id 
                WHERE d.bloque_id = $request->bloque
                ORDER BY d.id;";
        $departamentos = DB::select($sql);
        return response()->json($departamentos);
    }
}
