<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $allowedRoles = DB::table('roles')
            ->whereIn('slug', ['dependencia', 'trabajador_gd'])
            ->pluck('id', 'slug');

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'integer', Rule::in($allowedRoles->values()->all())],
            'dependencia_id' => ['nullable', 'integer', 'exists:dependencias,id'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        if ($allowedRoles->count() !== 2) {
            throw ValidationException::withMessages([
                'role_id' => 'No están configurados los roles permitidos para el registro.',
            ]);
        }

        $isDependenciaUser = (int) $validated['role_id'] === (int) $allowedRoles->get('dependencia');

        if ($isDependenciaUser && empty($validated['dependencia_id'])) {
            throw ValidationException::withMessages([
                'dependencia_id' => 'Selecciona una dependencia para registrar este usuario.',
            ]);
        }

        $user = User::create([
            ...$validated,
            'dependencia_id' => $isDependenciaUser ? $validated['dependencia_id'] : null,
            'activo' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'user' => $this->formatUser($request->user()),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->activo) {
            return response()->json([
                'message' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.',
            ], 403);
        }

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Las credenciales proporcionadas no son válidas.',
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Inicio de sesión correcto.',
            'user' => $this->formatUser($request->user()),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    public function dependencias(): JsonResponse
    {
        $dependencias = DB::table('dependencias')
            ->where('activa', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'siglas']);

        $roles = DB::table('roles')
            ->whereIn('slug', ['dependencia', 'trabajador_gd'])
            ->orderByRaw("case when slug = 'dependencia' then 0 else 1 end")
            ->get(['id', 'nombre', 'slug']);

        return response()->json([
            'dependencias' => $dependencias,
            'roles' => $roles,
        ]);
    }

    private function formatUser(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        $role = DB::table('roles')->where('id', $user->role_id)->value('nombre');
        $dependencia = $user->dependencia_id
            ? DB::table('dependencias')->where('id', $user->dependencia_id)->value('nombre')
            : null;

        return [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'apellidos' => $user->apellidos,
            'email' => $user->email,
            'telefono' => $user->telefono,
            'activo' => (bool) $user->activo,
            'role' => $role,
            'dependencia' => $dependencia,
        ];
    }
}