<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Carrera; 
use App\Models\Materia;
use App\Models\Profesor;
use Illuminate\Database\Seeder;

class RelacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $las = Carrera::where('codigo', 'LAS')->firstOrFail(); 
        $tup = Carrera::where('codigo', 'TUP')->firstOrFail(); 
 
        $ayed = Materia::where('codigo', 'AYED')->firstOrFail(); 
        $so   = Materia::where('codigo', 'SO')->firstOrFail(); 
        $bd   = Materia::where('codigo', 'BD')->firstOrFail(); 
        $paw  = Materia::where('codigo', 'PAW')->firstOrFail(); 
 
        $las->materias()->attach([
            $ayed->id, 
            $so->id, 
            $bd->id, 
        ]); 
 
        $tup->materias()->attach([ 
            $ayed->id, 
            $paw->id, 
        ]);

        $profesor1 = Profesor::where('dni', '12345678')->firstOrFail();
        $profesor2 = Profesor::where('dni', '87654321')->firstOrFail();

        $ayed->profesores()->syncWithoutDetaching([
            $profesor1->id,
            $profesor2->id,
        ]);

        $so->profesores()->syncWithoutDetaching([
            $profesor1->id,
        ]);

        $paw->profesores()->syncWithoutDetaching([
            $profesor2->id,
        ]);

    }
}
