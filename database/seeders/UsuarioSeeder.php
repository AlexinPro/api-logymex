<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuarioSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $usuarios = [
            [
                'nombre' => 'Director General',
                'email' => 'director@logymex.test',
                'password' => 'password123',
                'rol' => 'director_general',
            ],
            [
                'nombre' => 'Jefe de Logistica',
                'email' => 'logistica@logymex.test',
                'password' => 'password123',
                'rol' => 'jefe_logistica',
            ],
            [
                'nombre' => 'Ejecutivo de Ventas',
                'email' => 'ventas@logymex.test',
                'password' => 'password123',
                'rol' => 'ventas',
            ],
            [
                'nombre' => 'Operador Logymex',
                'email' => 'operador@logymex.test',
                'password' => 'password123',
                'rol' => 'operador',
            ],
        ];

        foreach ($usuarios as $datosUsuario) {
            $rol = Role::findOrCreate($datosUsuario['rol']);

            $usuario = User::updateOrCreate(
                ['email' => $datosUsuario['email']],
                [
                    'name' => $datosUsuario['nombre'],
                    'password' => Hash::make($datosUsuario['password']),
                ]
            );

            $usuario->syncRoles([$rol]);
        }
    }
}
