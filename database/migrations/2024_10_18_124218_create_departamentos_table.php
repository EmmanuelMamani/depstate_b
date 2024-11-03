<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('departamento');
            $table->decimal('mt2', 8, 2)->nullable();
            $table->decimal('expensa', 8, 2)->nullable();
            $table->string('propietario')->nullable();
            $table->enum('estado',['libre','ocupado']);
            $table->unsignedBigInteger('bloque_id');
            $table->foreign('bloque_id')->references('id')->on('bloques')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
