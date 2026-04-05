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
        // Historial de cambios de estatus
        // Cada vez que una solicitud cambia de estatus se inserta una fila.
        // Nunca se elimina: es la bitácora oficial del ciclo de vida.
        // ────────────────────────────────────────────────────────────────────
        Schema::create('historial_estatus', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')
                  ->constrained('solicitudes')
                  ->cascadeOnDelete();

            $table->string('estatus_anterior', 30)->nullable();
            $table->string('estatus_nuevo', 30);

            $table->foreignId('cambiado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->text('comentario')->nullable();             // Justificación del cambio
            $table->timestamp('created_at')->useCurrent();

            $table->index('solicitud_id');
        });


        // ────────────────────────────────────────────────────────────────────
        // Adjuntos / evidencias
        // Polimórfico: pueden pertenecer a una solicitud o a una aclaración.
        // ────────────────────────────────────────────────────────────────────
        Schema::create('adjuntos', function (Blueprint $table) {
            $table->id();

            // morphs → adjuntable_type + adjuntable_id
            // Tipos válidos: App\Models\Solicitud | App\Models\Aclaracion | App\Models\Entregable
            $table->morphs('adjuntable');

            $table->string('nombre_original', 260);             // Nombre como lo subió el usuario
            $table->string('path', 400);                        // storage/solicitudes/{folio}/... IMPORTANTE ESPERAR INDICACIONES 
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('tamanio_bytes');
            $table->string('tipo_documento', 60)->nullable();   // oficio | diagrama | formato | anexo | autorizacion | evidencia_prueba | otro

            $table->foreignId('subido_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

        });

        DB::statement("
            ALTER TABLE adjuntos
            ADD CONSTRAINT chk_tipo_documento
            CHECK (tipo_documento IN ('oficio','diagrama','formato','anexo','autorizacion','evidencia_prueba','otro') OR tipo_documento IS NULL)
        ");


        // ────────────────────────────────────────────────────────────────────
        // Hilo de aclaraciones / comunicación bidireccional
        // Toda comunicación entre GobDigital y la dependencia vive aquí,
        // vinculada al expediente. Nunca por correo externo.
        // ────────────────────────────────────────────────────────────────────
        Schema::create('aclaraciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')
                  ->constrained('solicitudes')
                  ->cascadeOnDelete();

            $table->foreignId('autor_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->string('origen', 20);                       // interno (GobDigital) | externo (dependencia)
            $table->text('mensaje');
            $table->boolean('leido')->default(false);
            $table->timestamp('leido_at')->nullable();

            $table->timestamps();

            $table->index('solicitud_id');
        });

        DB::statement("
            ALTER TABLE aclaraciones
            ADD CONSTRAINT chk_origen_aclaracion
            CHECK (origen IN ('interno','externo'))
        ");


        // ──────────────────────────────────────────────────────────────────
        // Bitácora auditable de acciones (audit log)
        // Registra QUIEN hizo QUÉ y CUANDO en toda la plataforma.
        // ────────────────────────────────────────────────────────────────────
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('accion', 80);                       // Ej: solicitud.creada, dictamen.emitido, adjunto.subido
            $table->string('modelo', 80)->nullable();           // Ej: Solicitud, CatalogoServicio
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->jsonb('datos_anteriores')->nullable();      // Estado antes del cambio
            $table->jsonb('datos_nuevos')->nullable();          // Estado después del cambio
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('accion');
            $table->index(['modelo', 'modelo_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('aclaraciones');
        Schema::dropIfExists('adjuntos');
        Schema::dropIfExists('historial_estatus');
    }
};