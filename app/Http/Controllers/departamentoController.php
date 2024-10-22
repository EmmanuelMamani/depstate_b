<?php

namespace App\Http\Controllers;
use App\Models\departamento;
use App\Models\recibo;
use Illuminate\Http\Request;

class departamentoController extends Controller
{
   public function recibos($id){
    $recibos = recibo::where('departamento_id',$id)->orderBy('id', 'desc')->get();
    return response()->json([
        'recibos'=>$recibos
    ]);
   }
}
