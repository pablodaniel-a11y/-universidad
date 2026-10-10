<?php

namespace Database\Seeders;

use App\Models\Profesor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfesorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Profesor::create([
            'dni' => '12345678',
            'apellido' => 'Pérez',
            'nombre' => 'Juan',
            'email' => 'juan.perez@example.com',
            'estado' => true,
        ]);

        Profesor::create([
            'dni' => '87654321',
            'apellido' => 'Gómez',
            'nombre' => 'María',
            'email' => 'maria.gomez@example.com',
            'estado' => true,
        ]);
        
        Profesor::factory()->count(8)->create();
    }
}
