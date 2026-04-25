<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $dependenciaId = DB::table('dependencias')->insertGetId([
            'nombre' => 'Secretaría de Innovación Digital',
            'siglas' => 'SID',
            'tipo' => 'Secretaría',
            'correo_oficial' => 'contacto@sid.michoacan.gob.mx',
            'telefono' => '4430000000',
            'activa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleId = DB::table('roles')->where('slug', 'dependencia')->value('id');

        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'nombre' => 'Usuario',
                'apellidos' => 'Prueba',
                'password' => 'password123',
                'role_id' => $roleId,
                'dependencia_id' => $dependenciaId,
                'cargo' => 'Enlace Institucional',
                'telefono' => '4430000001',
                'activo' => true,
            ]
        );
    }
}
