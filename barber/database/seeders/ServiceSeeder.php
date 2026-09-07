<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        Service::updateOrCreate(
            ['name' => 'Haircut'],
            [
                'price' => 15000,
                'duration' => 30,
                'active' => true,
            ]
        );
    }
}
