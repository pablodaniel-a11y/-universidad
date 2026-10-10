<?php

namespace Database\Factories;

use App\Models\Profesor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profesor>
 */
class ProfesorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dni' => fake()->unique()->numerify('########'), 
            'apellido' => fake()->lastName(), 
            'nombre' => fake()->firstName(), 
            'email' => fake()->unique()->safeEmail(), 
            'estado' => true,
        ];
    }
}
