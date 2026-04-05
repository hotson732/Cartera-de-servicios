<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();
            // dependencia  → usuario externo de una dependencia gubernamental
            // trabajador_gd → personal interno de Gobierno Digital (triage/dictamen)
            // superadmin    → acceso total + CRUD del catálogo
            // estadisticas  → solo lectura de reportes y tableros
            $table->string('slug', 60)->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // Seed inicial de roles
        DB::table('roles')->insert([
            ['nombre' => 'Dependencia',         'slug' => 'dependencia',    'descripcion' => 'Usuario externo de una dependencia gubernamental.',           'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Trabajador GobDigital','slug' => 'trabajador_gd', 'descripcion' => 'Personal interno de Gobierno Digital: triage y dictamen.',    'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Superadmin',           'slug' => 'superadmin',    'descripcion' => 'Acceso total, gestión del catálogo y administración.',         'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Estadísticas',         'slug' => 'estadisticas',  'descripcion' => 'Solo lectura: reportes, tableros e indicadores.',              'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};