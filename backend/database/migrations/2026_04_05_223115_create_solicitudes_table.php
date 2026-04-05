<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Ciclo de vida de una solicitud (estatus)
    |--------------------------------------------------------------------------
    | borrador          → guardado sin enviar
    | en_revision       → recibida, en triage
    | en_aclaracion     → GobDigital pidió info adicional
    | dictaminada       → revisión técnica completada
    | aceptada          → aprobada formalmente
    | rechazada         → fuera de catálogo o incompleta (requiere motivo)
    | suspendida        → pausada con motivo explícito
    | en_planeacion     → calendarizada, pendiente de inicio
    | en_implementacion → trabajo en curso
    | en_validacion     → entregables publicados, dependencia debe aceptar
    | en_ajuste         → dependencia rechazó entrega, se retrabaja
    | cerrada           → aceptada formalmente por la dependencia
    | cancelada         → cancelada con causa explícita
    |--------------------------------------------------------------------------
    */

    const ESTATUS = [
        'borrador',
        'en_revision',
        'en_aclaracion',
        'dictaminada',
        'aceptada',
        'rechazada',
        'suspendida',
        'en_planeacion',
        'en_implementacion',
        'en_validacion',
        'en_ajuste',
        'cerrada',
        'cancelada',
    ];

    const PRIORIDADES = ['baja', 'media', 'alta', 'critica'];

    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();

            // ── Folio único ────────────────────────────────────────────────
            // Formato: GD-{AÑO}-{SECUENCIAL 4 dígitos}  Ej: GD-2025-0042
            $table->string('folio', 20)->unique()->nullable(); // null mientras es borrador

            // ── Relaciones principales ─────────────────────────────────────
            $table->foreignId('catalogo_servicio_id')
                  ->constrained('catalogo_servicios')
                  ->restrictOnDelete();

            $table->foreignId('dependencia_id')
                  ->constrained('dependencias')
                  ->restrictOnDelete();

            $table->foreignId('solicitante_id')             // Usuario que crea la solicitud
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('asignado_a')                 // Trabajador GobDigital responsable
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // ── Ciclo de vida ──────────────────────────────────────────────
            $table->string('estatus', 30)->default('borrador');
            $table->string('prioridad', 20)->default('media');
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamp('fecha_dictamen')->nullable();
            $table->timestamp('fecha_inicio_estimada')->nullable();
            $table->timestamp('fecha_cierre')->nullable();

            // ── Información funcional ──────────────────────────────────────
            $table->text('objetivo')->nullable();
            $table->text('alcance')->nullable();
            $table->unsignedInteger('poblacion_usuaria')->nullable();   // estimado mensual
            $table->text('descripcion_proceso')->nullable();
            $table->text('reglas_negocio')->nullable();

            // ── Información técnica ────────────────────────────────────────
            $table->string('ambiente', 30)->nullable();                 // produccion | pruebas | ambos
            $table->jsonb('apis_involucradas')->nullable();             // ["CURP-API", "INE-WS"]
            $table->string('dominios', 300)->nullable();
            $table->jsonb('certificados')->nullable();                  // ["SSL/TLS", "firma_electronica"]
            $table->text('restricciones_infraestructura')->nullable();

            // ── Seguridad y continuidad ────────────────────────────────────
            $table->string('criticidad', 10)->nullable();               // alta | media | baja
            $table->boolean('maneja_datos_personales')->default(false);
            $table->string('ventana_mantenimiento', 120)->nullable();
            $table->string('nivel_disponibilidad', 80)->nullable();     // Ej: "99.5%"
            $table->string('frecuencia_respaldo', 30)->nullable();      // diario | semanal | no_aplica

            // ── Dictamen ──────────────────────────────────────────────────
            $table->text('justificacion_dictamen')->nullable();         // Texto de aceptación o rechazo
            $table->text('motivo_rechazo')->nullable();
            $table->text('motivo_suspension')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Índices ────────────────────────────────────────────────────
            $table->index('folio');
            $table->index('estatus');
            $table->index('dependencia_id');
            $table->index('solicitante_id');
            $table->index('asignado_a');
            $table->index('prioridad');
            $table->index('fecha_envio');
        });

        // CHECK constraints en PostgreSQL
        DB::statement("
            ALTER TABLE solicitudes
            ADD CONSTRAINT chk_estatus_solicitud
            CHECK (estatus IN (
                'borrador','en_revision','en_aclaracion','dictaminada',
                'aceptada','rechazada','suspendida','en_planeacion',
                'en_implementacion','en_validacion','en_ajuste','cerrada','cancelada'
            ))
        ");

        DB::statement("
            ALTER TABLE solicitudes
            ADD CONSTRAINT chk_prioridad_solicitud
            CHECK (prioridad IN ('baja','media','alta','critica'))
        ");

        DB::statement("
            ALTER TABLE solicitudes
            ADD CONSTRAINT chk_criticidad_solicitud
            CHECK (criticidad IN ('alta','media','baja') OR criticidad IS NULL)
        ");

        DB::statement("
            ALTER TABLE solicitudes
            ADD CONSTRAINT chk_ambiente_solicitud
            CHECK (ambiente IN ('produccion','pruebas','ambos') OR ambiente IS NULL)
        ");

        // Secuencia para el folio: GD-{AÑO}-{NNNN}
        // Uso en el modelo: $folio = 'GD-' . date('Y') . '-' . str_pad(nextval('solicitudes_folio_seq'), 4, '0', STR_PAD_LEFT);
        DB::statement("CREATE SEQUENCE IF NOT EXISTS solicitudes_folio_seq START 1 INCREMENT 1");
    }

    public function down(): void
    {
        DB::statement("DROP SEQUENCE IF EXISTS solicitudes_folio_seq");
        Schema::dropIfExists('solicitudes');
    }
};