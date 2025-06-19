<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Debug;

class DebugFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Debug::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'line_number' => fake()->numberBetween(-10000, 10000),
            'class_name' => fake()->word(),
            'variable_type' => fake()->word(),
            'variable_id' => fake()->numberBetween(-100000, 100000),
        ];
    }
}
