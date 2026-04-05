<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ────────────────────────────────────────────────────────────────────
        // Tareas de implementación
        // Se crean cuando la solicitud pasa a estatus "en_implementacion".
        // El % de avance se calcula: tareas_completadas / total_tareas * 100
        // ────────────────────────────────────────────────────────────────────
        Schema::create('implementacion_tareas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')
                  ->constrained('solicitudes')
                  ->cascadeOnDelete();

            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();

            $table->foreignId('responsable_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->date('fecha_limite')->nullable();
            $table->boolean('completada')->default(false);
            $table->timestamp('completada_at')->nullable();

            $table->unsignedTinyInteger('orden')->default(0);  // Orden dentro de la solicitud

            $table->timestamps();

            $table->index('solicitud_id');
        });


        // ────────────────────────────────────────────────────────────────────
        // Entregables
        // Se publican cuando GobDigital termina la implementación.
        // La dependencia los revisa para aceptar o rechazar.
        // ────────────────────────────────────────────────────────────────────
        Schema::create('entregables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')
                  ->constrained('solicitudes')
                  ->cascadeOnDelete();

            $table->string('tipo', 40);                         // url | documento | nota_version | manual | configuracion | evidencia_prueba | aprobacion_interna
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->string('url', 500)->nullable();             // Si el entregable es un enlace
            $table->unsignedTinyInteger('version')->default(1); // Se incrementa en cada retrabajo

            $table->foreignId('publicado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('publicado_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('solicitud_id');
        });

        DB::statement("
            ALTER TABLE entregables
            ADD CONSTRAINT chk_tipo_entregable
            CHECK (tipo IN ('url','documento','nota_version','manual','configuracion','evidencia_prueba','aprobacion_interna'))
        ");


        // ────────────────────────────────────────────────────────────────────
        // Aceptación de entrega por parte de la dependencia
        // Una por cada ciclo de entrega (puede haber varios si hay retrabajo).
        // ────────────────────────────────────────────────────────────────────
        Schema::create('aceptaciones_entrega', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')
                  ->constrained('solicitudes')
                  ->cascadeOnDelete();

            $table->foreignId('revisado_por')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->string('resultado', 20);                    // aceptada | rechazada
            $table->text('observaciones')->nullable();          // Obligatorio si resultado = rechazada
            $table->unsignedTinyInteger('version_entrega');     // Corresponde a entregables.version
            $table->timestamp('created_at')->useCurrent();

            $table->index('solicitud_id');
        });

        DB::statement("
            ALTER TABLE aceptaciones_entrega
            ADD CONSTRAINT chk_resultado_entrega
            CHECK (resultado IN ('aceptada','rechazada'))
        ");


        // ────────────────────────────────────────────────────────────────────
        // Cambios de alcance
        // Registra redefiniciones durante la implementación.
        // Requiere justificación y aprobación explícita.
        // ────────────────────────────────────────────────────────────────────
        Schema::create('cambios_alcance', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')
                  ->constrained('solicitudes')
                  ->cascadeOnDelete();

            $table->text('descripcion_cambio');
            $table->text('justificacion');

            $table->foreignId('registrado_por')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('aprobado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('aprobado_at')->nullable();
            $table->boolean('aprobado')->default(false);

            $table->timestamps();

            $table->index('solicitud_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambios_alcance');
        Schema::dropIfExists('aceptaciones_entrega');
        Schema::dropIfExists('entregables');
        Schema::dropIfExists('implementacion_tareas');
    }
};