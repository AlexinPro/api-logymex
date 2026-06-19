<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    public function unidades(): HasMany
    {
        return $this->hasMany(Unidad::class, 'operador_id');
    }

    public function ordenesCreadas(): HasMany
    {
        return $this->hasMany(OrdenServicio::class, 'creado_por_id');
    }

    public function ordenesAsignadas(): HasMany
    {
        return $this->hasMany(OrdenServicio::class, 'operador_id');
    }

    public function rutasAsignadas(): HasMany
    {
        return $this->hasMany(Ruta::class, 'operador_id');
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class, 'operador_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
