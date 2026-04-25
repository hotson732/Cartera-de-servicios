<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('apellidos', 120);
            $table->string('email', 180)->unique();
            $table->string('password');                         // bcrypt — nunca texto plano pls

            // Relación con rol (un usuario tiene un rol principal)
            $table->foreignId('role_id')
                  ->constrained('roles')
                  ->restrictOnDelete();

            // Relación con dependencia (null para usuarios internos de GobDigital)
            $table->foreignId('dependencia_id')
                  ->nullable()
                  ->constrained('dependencias')
                  ->nullOnDelete();

            $table->string('cargo', 180)->nullable();         
            $table->string('telefono', 30)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Índices para búsquedas frecuentes
            $table->index('email');
            $table->index('role_id');
            $table->index('dependencia_id');
        });

        // Tokens de autentificacion sactun in laravel ojo me preguntan que pedo
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('users');
    }
};