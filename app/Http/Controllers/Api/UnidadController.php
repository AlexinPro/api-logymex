<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnidadController extends Controller
{
    public function listar(Request $request): JsonResponse
    {
        if (! $this->puedeVer($request)) {
            return $this->respuestaNoAutorizada();
        }

        $unidades = Unidad::with('operador:id,name,email')->orderBy('numero_economico')->get();

        return response()->json(['mensaje' => 'Unidades consultadas correctamente.', 'datos' => $unidades]);
    }

    public function registrar(Request $request): JsonResponse
    {
        if (! $this->puedeGestionar($request)) {
            return $this->respuestaNoAutorizada();
        }

        $unidad = Unidad::create($request->validate($this->reglas()));

        return response()->json(['mensaje' => 'Unidad registrada correctamente.', 'datos' => $unidad->load('operador:id,name,email')], 201);
    }

    public function mostrar(Request $request, Unidad $unidad): JsonResponse
    {
        if (! $this->puedeVer($request)) {
            return $this->respuestaNoAutorizada();
        }

        return response()->json(['mensaje' => 'Unidad consultada correctamente.', 'datos' => $unidad->load('operador:id,name,email')]);
    }

    public function actualizar(Request $request, Unidad $unidad): JsonResponse
    {
        if (! $this->puedeGestionar($request)) {
            return $this->respuestaNoAutorizada();
        }

        $unidad->update($request->validate($this->reglas(true, $unidad)));

        return response()->json(['mensaje' => 'Unidad actualizada correctamente.', 'datos' => $unidad->load('operador:id,name,email')]);
    }

    public function eliminar(Request $request, Unidad $unidad): JsonResponse
    {
        if (! $request->user()->hasRole('director_general')) {
            return $this->respuestaNoAutorizada('Solo el director general puede eliminar unidades.');
        }

        $unidad->delete();

        return response()->json(['mensaje' => 'Unidad eliminada correctamente.']);
    }

    private function reglas(bool $actualizar = false, ?Unidad $unidad = null): array
    {
        $requerido = $actualizar ? 'sometimes' : 'required';

        return [
            'operador_id' => ['nullable', 'exists:users,id'],
            'placas' => [$requerido, 'string', 'max:20', Rule::unique('unidades', 'placas')->ignore($unidad?->id)],
            'numero_economico' => [$requerido, 'string', 'max:50', Rule::unique('unidades', 'numero_economico')->ignore($unidad?->id)],
            'modelo' => ['nullable', 'string', 'max:100'],
            'autocargante' => ['sometimes', 'boolean'],
            'estatus' => ['sometimes', Rule::in(['disponible', 'en_ruta', 'mantenimiento', 'inactiva'])],
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
