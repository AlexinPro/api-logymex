<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AutenticacionController extends Controller
{
    public function iniciarSesion(Request $request): JsonResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = \App\Models\User::where('email', $credenciales['email'])->first();

        if (! $usuario || ! Hash::check($credenciales['password'], $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        $token = $usuario->createToken('app-movil-logymex')->plainTextToken;

        return response()->json([
            'mensaje' => 'Sesion iniciada correctamente.',
            'token' => $token,
            'tipo_token' => 'Bearer',
            'usuario' => $usuario->load('roles:id,name'),
        ]);
    }

    public function perfil(Request $request): JsonResponse
    {
        return response()->json([
            'mensaje' => 'Perfil consultado correctamente.',
            'usuario' => $request->user()->load('roles:id,name'),
        ]);
    }

    public function cerrarSesion(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'mensaje' => 'Sesion cerrada correctamente.',
        ]);
    }
}
