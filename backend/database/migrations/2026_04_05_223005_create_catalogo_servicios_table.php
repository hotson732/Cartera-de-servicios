<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Categorías válidas del catálogo institucional
    const CATEGORIAS = [
        'sistema',
        'modulo',
        'integracion',
        'certificado',
        'dominio',
        'pago',
        'tablero',
    ];

    public function up(): void
    {
        Schema::create('catalogo_servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->text('descripcion');

            // Enumerado como check constraint en psqñ
            $table->string('categoria', 40);

            $table->string('imagen_path', 300)->nullable();     // storage/public/catalogo/
            $table->string('responsable', 180)->nullable();     // Área responsable dentro de GobDigital
            $table->boolean('activo')->default(true);
            $table->boolean('visible_catalogo')->default(true); // Puede ocultarse sin eliminarse

            // Solo superadmin puede crear/editar/eliminar se valida en middleware
            $table->foreignId('creado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();                              // Soft delete: las solicitudes históricas mantienen la referencia

            $table->index('categoria');
            $table->index('activo');
        });

        //solamente funciona en psql
        DB::statement("
            ALTER TABLE catalogo_servicios
            ADD CONSTRAINT chk_categoria
            CHECK (categoria IN ('sistema','modulo','integracion','certificado','dominio','pago','tablero'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_servicios');
    }
};