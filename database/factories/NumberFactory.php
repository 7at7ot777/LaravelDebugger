<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Number;

class NumberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Number::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'float' => fake()->randomFloat(8, 0, 9999999.99999999),
            'is_int' => fake()->boolean(),
        ];
    }
}
