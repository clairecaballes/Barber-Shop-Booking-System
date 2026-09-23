<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // The owner account claims the seeded demo data so every other
        // (freshly registered) account starts at zero.
        $owner = User::updateOrCreate(
            ['email' => 'owner@barbershop.test'],
            [
                'name' => 'Shop Owner',
                'password' => 'password',
            ]
        );

        (new ServiceSeeder)->run($owner->id);
        (new SettingsSeeder)->run($owner->id);
    }
}
