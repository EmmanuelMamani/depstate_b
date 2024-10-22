<?php

namespace App\Http\Controllers;
use App\Models\bloque;
use App\Models\departamento;
use Illuminate\Http\Request;

class bloqueController extends Controller
{
    public function create(Request $request){
        $bloque= new bloque;
        $bloque->bloque=$request->bloque;
        $bloque->save();
         return response()->json($bloque);
    }
    public function bloques(){
        $bloques = bloque::all();
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
}
