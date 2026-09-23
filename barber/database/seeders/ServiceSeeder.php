<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * @param  int  $userId  the account that owns the seeded service
     */
    public function run(?int $userId = null): void
    {
        Service::updateOrCreate(
            ['user_id' => $userId, 'name' => 'Haircut'],
            [
                'price' => 15000,
                'duration' => 30,
                'active' => true,
            ]
        );
    }
}
