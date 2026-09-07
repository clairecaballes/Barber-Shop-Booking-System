<?php

namespace Database\Factories;

use App\Models\BusinessSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessSetting>
 */
class BusinessSettingFactory extends Factory
{
    protected $model = BusinessSetting::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(),
            'value' => ['value' => fake()->sentence()],
        ];
    }
}
