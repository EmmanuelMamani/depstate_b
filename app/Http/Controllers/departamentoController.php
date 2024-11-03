<?php

namespace App\Http\Controllers;
use App\Models\departamento;
use App\Models\recibo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class departamentoController extends Controller
{
   public function recibos($id){
    $recibos = recibo::where('departamento_id',$id)->orderBy('id', 'desc')->get();
    $saldo = Recibo::where('departamento_id', $id)->sum('saldo');
    return response()->json([
        'recibos'=>$recibos,
        'saldo'=>$saldo
    ]);
   }
   public function existe_recibo_mes($id){
    $existe = Recibo::where('departamento_id', $id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->exists();
     return response()->json($existe);
   }
}
