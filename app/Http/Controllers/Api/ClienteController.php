<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function listar(Request $request): JsonResponse
    {
        if (! $this->puedeVer($request)) {
            return $this->respuestaNoAutorizada();
        }

        return response()->json([
            'mensaje' => 'Clientes consultados correctamente.',
            'datos' => Cliente::query()->orderBy('nombre')->get(),
        ]);
    }

    public function registrar(Request $request): JsonResponse
    {
        if (! $this->puedeGestionar($request)) {
            return $this->respuestaNoAutorizada();
        }

        $datos = $request->validate($this->reglas());
        $cliente = Cliente::create($datos);

        return response()->json(['mensaje' => 'Cliente registrado correctamente.', 'datos' => $cliente], 201);
    }

    public function mostrar(Request $request, Cliente $cliente): JsonResponse
    {
        if (! $this->puedeVer($request)) {
            return $this->respuestaNoAutorizada();
        }

        return response()->json(['mensaje' => 'Cliente consultado correctamente.', 'datos' => $cliente]);
    }

    public function actualizar(Request $request, Cliente $cliente): JsonResponse
    {
        if (! $this->puedeGestionar($request)) {
            return $this->respuestaNoAutorizada();
        }

        $cliente->update($request->validate($this->reglas(true)));

        return response()->json(['mensaje' => 'Cliente actualizado correctamente.', 'datos' => $cliente]);
    }

    public function eliminar(Request $request, Cliente $cliente): JsonResponse
    {
        if (! $request->user()->hasRole('director_general')) {
            return $this->respuestaNoAutorizada('Solo el director general puede eliminar clientes.');
        }

        $cliente->delete();

        return response()->json(['mensaje' => 'Cliente eliminado correctamente.']);
    }

    private function reglas(bool $actualizar = false): array
    {
        $requerido = $actualizar ? 'sometimes' : 'required';

        return [
            'nombre' => [$requerido, 'string', 'max:255'],
            'domicilio' => [$requerido, 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:255'],
            'contacto' => ['nullable', 'string', 'max:255'],
            'es_preferente' => ['sometimes', 'boolean'],
            'estatus' => ['sometimes', Rule::in(['activo', 'inactivo'])],
        ];
    }

    private function puedeVer(Request $request): bool
    {
        return $request->user()->hasAnyRole(['director_general', 'jefe_logistica', 'ventas']);
    }

    private function puedeGestionar(Request $request): bool
    {
        return $request->user()->hasAnyRole(['director_general', 'jefe_logistica']);
    }

    private function respuestaNoAutorizada(string $mensaje = 'No tienes permisos para realizar esta accion.'): JsonResponse
    {
        return response()->json(['mensaje' => $mensaje], 403);
    }
}
