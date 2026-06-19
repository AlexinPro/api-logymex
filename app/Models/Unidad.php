<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['operador_id', 'placas', 'numero_economico', 'modelo', 'autocargante', 'estatus'])]
class Unidad extends Model
{
    use HasFactory;

    protected $table = 'unidades';

    protected function casts(): array
    {
        return [
            'autocargante' => 'boolean',
        ];
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    public function rutas(): HasMany
    {
        return $this->hasMany(Ruta::class);
    }
}
