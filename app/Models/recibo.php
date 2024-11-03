<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class recibo extends Model
{
    use HasFactory;
    public function departamento(){
        return $this->belongsTo(departamento::class);
    }
    public function user(){
        return $this->belongsTo(user::class);
    }
    public function detalles(){
        return $this->hasMany(recibo_detalle::class);
    }
    public function pagos(){
        return $this->hasMany(pago::class);
    }
}
