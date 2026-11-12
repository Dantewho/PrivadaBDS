<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vivienda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $homes = collect();
        for ($i = 1; $i <= 50; $i++) {
            $homes->push(Vivienda::updateOrCreate(['numero' => sprintf('%03d', $i)], ['direccion' => 'Circuito Bahía, sección '.(intdiv($i - 1, 10) + 1), 'estado' => 'activa']));
        }

        $admin = User::updateOrCreate(['email' => 'dante.gonzalez9351@alumnos.udg.mx'], [
            'name' => 'Dante Gonzalez Pavon', 'password' => Hash::make('12345678'), 'rol' => 'administrador', 'estado' => 'activo', 'email_verified_at' => now(),
        ]);
        $admin->update(['vivienda_id' => null]);

        $faker = fake('es_MX');
        for ($i = 1; $i <= 29; $i++) {
            $email = sprintf('residente%02d@privadabds.test', $i);
            $person = User::updateOrCreate(['email' => $email], [
                'name' => $faker->name(), 'password' => Hash::make('ResidenteDemo2026!'), 'rol' => 'residente', 'estado' => 'activo', 'email_verified_at' => now(), 'vivienda_id' => $homes[$i - 1]->id,
            ]);
            $homes[$i - 1]->update(['propietario_id' => $person->id]);
        }
    }
}
