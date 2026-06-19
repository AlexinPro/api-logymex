<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'domicilio', 'telefono', 'correo', 'contacto', 'es_preferente', 'estatus'])]
class Cliente extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'es_preferente' => 'boolean',
        ];
    }

    public function ordenesServicio(): HasMany
    {
        return $this->hasMany(OrdenServicio::class);
    }

    public function serviciosProgramados(): HasMany
    {
        return $this->hasMany(ServicioProgramado::class);
    }
}
