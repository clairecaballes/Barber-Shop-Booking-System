<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'price' => fake()->numberBetween(100, 2000) * 100,
            'duration' => fake()->randomElement([15, 20, 30, 45, 60]),
            'active' => true,
        ];
    }
}
