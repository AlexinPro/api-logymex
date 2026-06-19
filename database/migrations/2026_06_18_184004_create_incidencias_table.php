<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_servicio_id')->nullable()->constrained('ordenes_servicio')->nullOnDelete();
            $table->foreignId('ruta_id')->nullable()->constrained('rutas')->nullOnDelete();
            $table->foreignId('operador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ayudante_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha');
            $table->string('motivo');
            $table->text('descripcion')->nullable();
            $table->string('estatus')->default('registrada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
