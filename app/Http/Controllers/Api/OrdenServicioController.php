<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdenServicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class OrdenServicioController extends Controller
{
    private const ESTATUS = ['pendiente', 'asignado', 'en_ruta', 'en_proceso', 'completado', 'cancelado_por_incidencia'];

    public function listar(Request $request): JsonResponse
    {
        if (! $this->puedeVer($request)) {
            return $this->respuestaNoAutorizada();
        }

        $ordenes = OrdenServicio::with(['cliente', 'operador:id,name,email', 'creadoPor:id,name,email'])
            ->when($request->user()->hasRole('operador'), fn ($query) => $query->where('operador_id', $request->user()->id))
            ->orderByDesc('fecha_programada')
            ->get();

        return response()->json(['mensaje' => 'Ordenes de servicio consultadas correctamente.', 'datos' => $ordenes]);
    }

    public function registrar(Request $request): JsonResponse
    {
        if (! $this->puedeGestionar($request)) {
            return $this->respuestaNoAutorizada();
        }

        $datos = $request->validate($this->reglas());
        $datos['creado_por_id'] = $request->user()->id;

        $ordenServicio = OrdenServicio::create($datos);

        return response()->json([
            'mensaje' => 'Orden de servicio registrada correctamente.',
            'datos' => $ordenServicio->load(['cliente', 'operador:id,name,email', 'creadoPor:id,name,email']),
        ], 201);
    }

    public function mostrar(Request $request, OrdenServicio $ordenServicio): JsonResponse
    {
        if (! $this->puedeVerOrden($request, $ordenServicio)) {
            return $this->respuestaNoAutorizada();
        }

        return response()->json([
            'mensaje' => 'Orden de servicio consultada correctamente.',
            'datos' => $ordenServicio->load(['cliente', 'operador:id,name,email', 'creadoPor:id,name,email', 'rutas.unidad', 'incidencias']),
        ]);
    }

    public function actualizar(Request $request, OrdenServicio $ordenServicio): JsonResponse
    {
        if ($request->user()->hasRole('operador')) {
            return $this->actualizarComoOperador($request, $ordenServicio);
        }

        if (! $this->puedeGestionar($request)) {
            return $this->respuestaNoAutorizada();
        }

        $datos = $request->validate($this->reglas(true, $ordenServicio));
        $this->aplicarTiemposPorEstatus($datos);
        $ordenServicio->update($datos);

        return response()->json([
            'mensaje' => 'Orden de servicio actualizada correctamente.',
            'datos' => $ordenServicio->load(['cliente', 'operador:id,name,email', 'creadoPor:id,name,email']),
        ]);
    }

    public function eliminar(Request $request, OrdenServicio $ordenServicio): JsonResponse
    {
        if (! $request->user()->hasRole('director_general')) {
            return $this->respuestaNoAutorizada('Solo el director general puede eliminar ordenes de servicio.');
        }

        $ordenServicio->delete();

        return response()->json(['mensaje' => 'Orden de servicio eliminada correctamente.']);
    }

    private function actualizarComoOperador(Request $request, OrdenServicio $ordenServicio): JsonResponse
    {
        if ($ordenServicio->operador_id !== $request->user()->id) {
            return $this->respuestaNoAutorizada('Solo puedes actualizar servicios asignados a tu usuario.');
        }

        $datos = $request->validate([
            'estatus' => ['required', Rule::in(['completado', 'cancelado_por_incidencia'])],
            'observaciones' => ['nullable', 'string'],
        ]);

        $this->aplicarTiemposPorEstatus($datos);
        $ordenServicio->update($datos);

        return response()->json(['mensaje' => 'Estatus de servicio actualizado correctamente.', 'datos' => $ordenServicio]);
    }

    private function reglas(bool $actualizar = false, ?OrdenServicio $ordenServicio = null): array
    {
        $requerido = $actualizar ? 'sometimes' : 'required';

        return [
            'cliente_id' => [$requerido, 'exists:clientes,id'],
            'operador_id' => ['nullable', 'exists:users,id'],
            'folio' => [$requerido, 'string', 'max:100', Rule::unique('ordenes_servicio', 'folio')->ignore($ordenServicio?->id)],
            'descripcion' => ['nullable', 'string'],
            'fecha_programada' => ['nullable', 'date'],
            'hora_programada' => ['nullable', 'date_format:H:i'],
            'estatus' => ['sometimes', Rule::in(self::ESTATUS)],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    private function aplicarTiemposPorEstatus(array &$datos): void
    {
        if (($datos['estatus'] ?? null) === 'completado') {
            $datos['completado_en'] = Carbon::now();
        }

        if (($datos['estatus'] ?? null) === 'cancelado_por_incidencia') {
            $datos['cancelado_en'] = Carbon::now();
        }
    }

    private function puedeVer(Request $request): bool
    {
        return $request->user()->hasAnyRole(['director_general', 'jefe_logistica', 'ventas', 'operador']);
    }

    private function puedeVerOrden(Request $request, OrdenServicio $ordenServicio): bool
    {
        if (! $this->puedeVer($request)) {
            return false;
        }

        return ! $request->user()->hasRole('operador') || $ordenServicio->operador_id === $request->user()->id;
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
