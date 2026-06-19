<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['orden_servicio_id', 'cliente_id', 'unidad_id', 'operador_id', 'fecha', 'hora', 'estatus', 'color_calendario', 'observaciones'])]
class ServicioProgramado extends Model
{
    use HasFactory;

    protected $table = 'servicios_programados';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function ordenServicio(): BelongsTo
    {
        return $this->belongsTo(OrdenServicio::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operador_id');
    }
}
