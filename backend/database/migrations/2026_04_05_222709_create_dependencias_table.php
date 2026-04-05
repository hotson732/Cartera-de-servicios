<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dependencias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 180)->unique();
            $table->string('siglas', 20)->nullable();
            // Ejemplo Secretaría de Salud Contraloría General etc.
            $table->string('tipo', 80)->nullable();
            $table->string('titular', 180)->nullable();
            $table->string('correo_oficial', 180)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dependencias');
    }
};