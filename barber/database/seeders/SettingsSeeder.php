<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * @param  int  $userId  the account that owns the seeded settings
     */
    public function run(?int $userId = null): void
    {
        $settings = [
            'shop_name' => 'My Barber Shop',
            'currency' => '₱',
            'default_service' => 'Haircut',
            'operating_hours' => [
                'monday' => ['open' => '09:00', 'close' => '18:00'],
                'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                'thursday' => ['open' => '09:00', 'close' => '18:00'],
                'friday' => ['open' => '09:00', 'close' => '18:00'],
                'saturday' => ['open' => '09:00', 'close' => '18:00'],
                'sunday' => ['open' => null, 'close' => null],
            ],
        ];

        foreach ($settings as $key => $value) {
            BusinessSetting::updateOrCreate(
                ['user_id' => $userId, 'key' => $key],
                ['value' => $value]
            );
        }
    }
}
