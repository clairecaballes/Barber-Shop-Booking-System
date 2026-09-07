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
        $this->call([
            ServiceSeeder::class,
            SettingsSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'owner@barbershop.test'],
            [
                'name' => 'Shop Owner',
                'password' => 'password',
            ]
        );
    }
}
