<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    private const ROLES_PERMITIDOS = [
        'director_general',
        'jefe_logistica',
        'ventas',
        'operador',
    ];

    public function listar(Request $request): JsonResponse
    {
        if (! $this->puedeConsultarUsuarios($request->user())) {
            return $this->respuestaNoAutorizada();
        }

        $usuarios = User::query()
            ->with('roles:id,name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'mensaje' => 'Usuarios consultados correctamente.',
            'datos' => $usuarios,
        ]);
    }

    public function registrar(Request $request): JsonResponse
    {
        if (! $this->esDirectorGeneral($request->user())) {
            return $this->respuestaNoAutorizada('Solo el director general puede registrar usuarios.');
        }

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'rol' => ['required', Rule::in(self::ROLES_PERMITIDOS)],
        ]);

        $usuario = User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
        ]);

        Role::findOrCreate($datos['rol']);
        $usuario->assignRole($datos['rol']);
        $usuario->load('roles:id,name');

        return response()->json([
            'mensaje' => 'Usuario registrado correctamente.',
            'datos' => $usuario,
        ], 201);
    }

    public function mostrar(Request $request, User $usuario): JsonResponse
    {
        if (! $this->puedeConsultarUsuarios($request->user())) {
            return $this->respuestaNoAutorizada();
        }

        return response()->json([
            'mensaje' => 'Usuario consultado correctamente.',
            'datos' => $usuario->load('roles:id,name'),
        ]);
    }

    public function actualizar(Request $request, User $usuario): JsonResponse
    {
        if (! $this->puedeConsultarUsuarios($request->user())) {
            return $this->respuestaNoAutorizada();
        }

        $reglas = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
        ];

        if ($this->esDirectorGeneral($request->user())) {
            $reglas['rol'] = ['sometimes', 'required', Rule::in(self::ROLES_PERMITIDOS)];
        }

        $datos = $request->validate($reglas);

        $usuario->fill([
            'name' => $datos['name'] ?? $usuario->name,
            'email' => $datos['email'] ?? $usuario->email,
        ]);

        if (! empty($datos['password'])) {
            $usuario->password = Hash::make($datos['password']);
        }

        $usuario->save();

        if ($this->esDirectorGeneral($request->user()) && isset($datos['rol'])) {
            $usuario->syncRoles([$datos['rol']]);
        }

        return response()->json([
            'mensaje' => 'Usuario actualizado correctamente.',
            'datos' => $usuario->load('roles:id,name'),
        ]);
    }

    public function eliminar(Request $request, User $usuario): JsonResponse
    {
        if (! $this->esDirectorGeneral($request->user())) {
            return $this->respuestaNoAutorizada('Solo el director general puede eliminar usuarios.');
        }

        if ($request->user()->is($usuario)) {
            return response()->json([
                'mensaje' => 'No puedes eliminar tu propio usuario autenticado.',
            ], 422);
        }

        $usuario->delete();

        return response()->json([
            'mensaje' => 'Usuario eliminado correctamente.',
        ]);
    }

    private function puedeConsultarUsuarios(User $usuario): bool
    {
        return $usuario->hasAnyRole(['director_general', 'jefe_logistica']);
    }

    private function esDirectorGeneral(User $usuario): bool
    {
        return $usuario->hasRole('director_general');
    }

    private function respuestaNoAutorizada(string $mensaje = 'No tienes permisos para realizar esta accion.'): JsonResponse
    {
        return response()->json([
            'mensaje' => $mensaje,
        ], 403);
    }
}
