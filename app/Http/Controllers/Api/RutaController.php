<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RutaController extends Controller
{
    private const ESTATUS = ['pendiente', 'asignada', 'en_ruta', 'completada', 'cancelada'];

    public function listar(Request $request): JsonResponse
    {
        if (! $this->puedeVer($request)) {
            return $this->respuestaNoAutorizada();
        }

        $rutas = Ruta::with(['ordenServicio.cliente', 'unidad', 'operador:id,name,email', 'ayudante:id,name,email'])
            ->when($request->user()->hasRole('operador'), fn ($query) => $query->where('operador_id', $request->user()->id))
            ->orderByDesc('fecha')
            ->get();

        return response()->json(['mensaje' => 'Rutas consultadas correctamente.', 'datos' => $rutas]);
    }

    public function registrar(Request $request): JsonResponse
    {
        if (! $this->puedeGestionar($request)) {
            return $this->respuestaNoAutorizada();
        }

        $ruta = Ruta::create($request->validate($this->reglas()));

        return response()->json(['mensaje' => 'Ruta registrada correctamente.', 'datos' => $ruta->load(['ordenServicio.cliente', 'unidad', 'operador:id,name,email', 'ayudante:id,name,email'])], 201);
    }

    public function mostrar(Request $request, Ruta $ruta): JsonResponse
    {
        if (! $this->puedeVerRuta($request, $ruta)) {
            return $this->respuestaNoAutorizada();
        }

        return response()->json(['mensaje' => 'Ruta consultada correctamente.', 'datos' => $ruta->load(['ordenServicio.cliente', 'unidad', 'operador:id,name,email', 'ayudante:id,name,email', 'incidencias'])]);
    }

    public function actualizar(Request $request, Ruta $ruta): JsonResponse
    {
        if ($request->user()->hasRole('operador')) {
            return $this->actualizarComoOperador($request, $ruta);
        }

        if (! $this->puedeGestionar($request)) {
            return $this->respuestaNoAutorizada();
        }

        $ruta->update($request->validate($this->reglas(true)));

        return response()->json(['mensaje' => 'Ruta actualizada correctamente.', 'datos' => $ruta->load(['ordenServicio.cliente', 'unidad', 'operador:id,name,email', 'ayudante:id,name,email'])]);
    }

    public function eliminar(Request $request, Ruta $ruta): JsonResponse
    {
        if (! $request->user()->hasRole('director_general')) {
            return $this->respuestaNoAutorizada('Solo el director general puede eliminar rutas.');
        }

        $ruta->delete();

        return response()->json(['mensaje' => 'Ruta eliminada correctamente.']);
    }

    private function actualizarComoOperador(Request $request, Ruta $ruta): JsonResponse
    {
        if ($ruta->operador_id !== $request->user()->id) {
            return $this->respuestaNoAutorizada('Solo puedes actualizar rutas asignadas a tu usuario.');
        }

        $datos = $request->validate([
            'estatus' => ['required', Rule::in(['completada', 'cancelada'])],
            'observaciones' => ['nullable', 'string'],
        ]);

        $ruta->update($datos);

        return response()->json(['mensaje' => 'Estatus de ruta actualizado correctamente.', 'datos' => $ruta]);
    }

    private function reglas(bool $actualizar = false): array
    {
        $requerido = $actualizar ? 'sometimes' : 'required';

        return [
            'orden_servicio_id' => [$requerido, 'exists:ordenes_servicio,id'],
            'unidad_id' => ['nullable', 'exists:unidades,id'],
            'operador_id' => ['nullable', 'exists:users,id'],
            'ayudante_id' => ['nullable', 'exists:users,id'],
            'domicilio' => [$requerido, 'string', 'max:255'],
            'fecha' => ['nullable', 'date'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'estatus' => ['sometimes', Rule::in(self::ESTATUS)],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    private function puedeVer(Request $request): bool
    {
        return $request->user()->hasAnyRole(['director_general', 'jefe_logistica', 'ventas', 'operador']);
    }

    private function puedeVerRuta(Request $request, Ruta $ruta): bool
    {
        if (! $this->puedeVer($request)) {
            return false;
        }

        return ! $request->user()->hasRole('operador') || $ruta->operador_id === $request->user()->id;
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
