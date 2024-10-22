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
        Schema::create('recibo_detalles', function (Blueprint $table) {
            $table->id();
            $table->string('detalle');
            $table->decimal('monto', 8, 2);
            $table->unsignedBigInteger('recibo_id');
            $table->foreign('recibo_id')->references('id')->on('recibos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibo_detalles');
    }
};
