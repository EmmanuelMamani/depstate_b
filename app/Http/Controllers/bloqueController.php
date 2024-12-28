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
        $departamentos = DB::select("
                        select d.*, coalesce(r.saldo, 0) as saldo
                        from departamentos d
                        left join (
                            select r.departamento_id, sum(r.saldo) as saldo
                            from recibos r
                            group by r.departamento_id
                        ) as r on r.departamento_id = d.id
                        where d.bloque_id = $id
                        order by d.id asc
                    ");
        $bloque = bloque::find($id);
        return response()->json([
            'departamentos' => $departamentos,
            'bloque' => $bloque
        ]);
    }
    
    public function recibos_mes(Request $request){
        $sql="SELECT d.departamento,d.estado as estado_dep,d.bloque_id,r.total,r.saldo,r.pagado,r.recibo,r.fecha_recibo,r.mes_correspondiente,
                    CASE 
                        WHEN r.pagado IS NULL THEN 'sin recibo'
                        WHEN r.pagado = false THEN 'sin pagar'
                        WHEN r.pagado = true THEN 'pagado'
                    END AS estado
                from departamentos d 
                left join recibos r on d.id =r.departamento_id  and TO_CHAR(r.fecha_recibo, 'YYYY-MM') = '$request->fecha' and r.pagado=true
                where d.bloque_id =$request->bloque 
                order by d.id";
        $departamentos = DB::select($sql);
        return response()->json($departamentos);
    }

    public function recibos_mes_correspondiente(Request $request){
        $sql="SELECT d.departamento,d.estado as estado_dep,d.bloque_id,r.total,r.saldo,r.pagado,r.recibo,r.fecha_recibo,r.mes_correspondiente,
                    CASE 
                        WHEN r.pagado IS NULL THEN 'sin recibo'
                        WHEN r.pagado = false THEN 'sin pagar'
                        WHEN r.pagado = true THEN 'pagado'
                    END AS estado
                from departamentos d 
                left join recibos r on d.id =r.departamento_id  and r.mes_correspondiente = '$request->fecha'
                where d.bloque_id =$request->bloque 
                order by d.id";
        $departamentos = DB::select($sql);
        return response()->json($departamentos);
    }
    public function estadisticas($id){
        $sql="SELECT 
                r.mes_correspondiente as mes, 
                SUM(r.total) AS total, 
                SUM(CASE WHEN r.pagado = true THEN r.total ELSE 0 END) AS pagado,
                SUM(CASE WHEN r.pagado = false THEN r.saldo ELSE 0 END) AS saldo
            FROM recibos r
            inner join departamentos d on d.id = r.departamento_id 
            where d.bloque_id =$id
            GROUP BY 
                r.mes_correspondiente 
            order by r.mes_correspondiente;";
        $meses = DB::select($sql);
        return response()->json($meses);
    }
}
