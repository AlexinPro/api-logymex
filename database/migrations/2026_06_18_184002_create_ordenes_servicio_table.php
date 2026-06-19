<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('creado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('operador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('folio')->unique();
            $table->text('descripcion')->nullable();
            $table->date('fecha_programada')->nullable();
            $table->time('hora_programada')->nullable();
            $table->string('estatus')->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamp('completado_en')->nullable();
            $table->timestamp('cancelado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_servicio');
    }
};
