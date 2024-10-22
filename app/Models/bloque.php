<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bloque extends Model
{
    use HasFactory;
    public function departamentos(){
        return $this->hasMany(departamento::class);
    }
}
