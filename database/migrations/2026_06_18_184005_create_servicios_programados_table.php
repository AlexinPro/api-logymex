<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios_programados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_servicio_id')->nullable()->constrained('ordenes_servicio')->nullOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('unidad_id')->nullable()->constrained('unidades')->nullOnDelete();
            $table->foreignId('operador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('estatus')->default('pendiente');
            $table->string('color_calendario')->default('amarillo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios_programados');
    }
};
