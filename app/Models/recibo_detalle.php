<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class recibo_detalle extends Model
{
    use HasFactory;
    public function recibo(){
        return $this->belongsTo(recibo::class);
    }
}
