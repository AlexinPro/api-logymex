<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('placas')->unique();
            $table->string('numero_economico')->unique();
            $table->string('modelo')->nullable();
            $table->boolean('autocargante')->default(false);
            $table->string('estatus')->default('disponible');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
