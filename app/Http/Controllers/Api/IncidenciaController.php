<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incidencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncidenciaController extends Controller
{
    public function listar(Request $request): JsonResponse
    {
        if (! $this->puedeVer($request)) {
            return $this->respuestaNoAutorizada();
        }

        $incidencias = Incidencia::with(['ordenServicio.cliente', 'ruta', 'operador:id,name,email', 'ayudante:id,name,email'])
            ->when($request->user()->hasRole('operador'), fn ($query) => $query->where('operador_id', $request->user()->id))
            ->orderByDesc('fecha')
            ->get();

        return response()->json(['mensaje' => 'Incidencias consultadas correctamente.', 'datos' => $incidencias]);
    }

    public function registrar(Request $request): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['director_general', 'jefe_logistica', 'operador'])) {
            return $this->respuestaNoAutorizada();
        }

        $datos = $request->validate($this->reglas());

        if ($request->user()->hasRole('operador')) {
            $datos['operador_id'] = $request->user()->id;
        }

        $incidencia = Incidencia::create($datos);

        return response()->json(['mensaje' => 'Incidencia registrada correctamente.', 'datos' => $incidencia->load(['ordenServicio.cliente', 'ruta', 'operador:id,name,email', 'ayudante:id,name,email'])], 201);
    }

    public function mostrar(Request $request, Incidencia $incidencia): JsonResponse
    {
        if (! $this->puedeVerIncidencia($request, $incidencia)) {
            return $this->respuestaNoAutorizada();
        }

        return response()->json(['mensaje' => 'Incidencia consultada correctamente.', 'datos' => $incidencia->load(['ordenServicio.cliente', 'ruta', 'operador:id,name,email', 'ayudante:id,name,email'])]);
    }

    public function actualizar(Request $request, Incidencia $incidencia): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['director_general', 'jefe_logistica'])) {
            return $this->respuestaNoAutorizada();
        }

        $incidencia->update($request->validate($this->reglas(true)));

        return response()->json(['mensaje' => 'Incidencia actualizada correctamente.', 'datos' => $incidencia->load(['ordenServicio.cliente', 'ruta', 'operador:id,name,email', 'ayudante:id,name,email'])]);
    }

    public function eliminar(Request $request, Incidencia $incidencia): JsonResponse
    {
        if (! $request->user()->hasRole('director_general')) {
            return $this->respuestaNoAutorizada('Solo el director general puede eliminar incidencias.');
        }

        $incidencia->delete();

        return response()->json(['mensaje' => 'Incidencia eliminada correctamente.']);
    }

    private function reglas(bool $actualizar = false): array
    {
        $requerido = $actualizar ? 'sometimes' : 'required';

        return [
            'orden_servicio_id' => ['nullable', 'exists:ordenes_servicio,id'],
            'ruta_id' => ['nullable', 'exists:rutas,id'],
            'operador_id' => ['nullable', 'exists:users,id'],
            'ayudante_id' => ['nullable', 'exists:users,id'],
            'fecha' => [$requerido, 'date'],
            'motivo' => [$requerido, 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'estatus' => ['sometimes', Rule::in(['registrada', 'en_revision', 'resuelta'])],
        ];
    }

    private function puedeVer(Request $request): bool
    {
        return $request->user()->hasAnyRole(['director_general', 'jefe_logistica', 'ventas', 'operador']);
    }

    private function puedeVerIncidencia(Request $request, Incidencia $incidencia): bool
    {
        if (! $this->puedeVer($request)) {
            return false;
        }

        return ! $request->user()->hasRole('operador') || $incidencia->operador_id === $request->user()->id;
    }

    private function respuestaNoAutorizada(string $mensaje = 'No tienes permisos para realizar esta accion.'): JsonResponse
    {
        return response()->json(['mensaje' => $mensaje], 403);
    }
}
