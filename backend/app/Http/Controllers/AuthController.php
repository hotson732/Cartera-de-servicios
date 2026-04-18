<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash; 

class AuthController extends Controller
{
    public function login(Request $request)
    {
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        
        $user = User::where('email', $request->email)->first();

        
        if (!$user) {
            return response()->json([
                'message' => 'ERROR 1: El correo no existe en la base de datos'
            ], 404);
        }

        // 2. Revisar la contraseña
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'ERROR 2: El correo existe, pero la contraseña no hace match'
            ], 401);
        }

        // Crear token de acceso (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // Reenvio del token a React
        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
}