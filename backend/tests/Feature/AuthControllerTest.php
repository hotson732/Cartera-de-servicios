<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_from_the_api_endpoints(): void
    {
        $roleId = DB::table('roles')->where('slug', 'dependencia')->value('id');
        $dependenciaId = DB::table('dependencias')->insertGetId([
            'nombre' => 'Secretaria de Finanzas',
            'siglas' => 'SF',
            'activa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/register', [
            'nombre' => 'Ana',
            'apellidos' => 'Lopez',
            'email' => 'ana@example.com',
            'role_id' => $roleId,
            'dependencia_id' => $dependenciaId,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.email', 'ana@example.com');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ana@example.com',
            'nombre' => 'Ana',
            'apellidos' => 'Lopez',
            'role_id' => $roleId,
            'dependencia_id' => $dependenciaId,
        ]);
    }

    public function test_a_trabajador_gobdigital_can_register_without_dependencia(): void
    {
        $roleId = DB::table('roles')->where('slug', 'trabajador_gd')->value('id');

        $response = $this->postJson('/api/register', [
            'nombre' => 'Mario',
            'apellidos' => 'Vargas',
            'email' => 'mario@example.com',
            'role_id' => $roleId,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'mario@example.com',
            'role_id' => $roleId,
            'dependencia_id' => null,
        ]);
    }

    public function test_a_user_can_login_with_valid_credentials(): void
    {
        $roleId = DB::table('roles')->where('slug', 'dependencia')->value('id');

        User::create([
            'nombre' => 'Carlos',
            'apellidos' => 'Perez',
            'email' => 'carlos@example.com',
            'password' => 'password123',
            'role_id' => $roleId,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'carlos@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.email', 'carlos@example.com');

        $this->assertAuthenticated();
    }

    public function test_an_authenticated_user_can_fetch_their_profile(): void
    {
        $roleId = DB::table('roles')->where('slug', 'dependencia')->value('id');

        $user = User::create([
            'nombre' => 'Lucia',
            'apellidos' => 'Ramirez',
            'email' => 'lucia@example.com',
            'password' => 'password123',
            'role_id' => $roleId,
        ]);

        $response = $this->actingAs($user)->getJson('/api/me');

        $response
            ->assertOk()
            ->assertJsonPath('user.email', 'lucia@example.com');
    }
}