<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessSettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'settings' => [
                'shop_name' => BusinessSetting::get('shop_name', 'My Barber Shop'),
                'currency' => BusinessSetting::get('currency', '₱'),
                'operating_hours' => BusinessSetting::get('operating_hours', [
                    'monday' => ['open' => '09:00', 'close' => '18:00'],
                    'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                    'thursday' => ['open' => '09:00', 'close' => '18:00'],
                    'friday' => ['open' => '09:00', 'close' => '18:00'],
                    'saturday' => ['open' => '09:00', 'close' => '18:00'],
                    'sunday' => ['open' => null, 'close' => null],
                ]),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'max:10'],
            'operating_hours' => ['sometimes', 'array'],
            'operating_hours.*.open' => ['nullable', 'date_format:H:i'],
            'operating_hours.*.close' => ['nullable', 'date_format:H:i'],
        ]);

        BusinessSetting::updateOrCreate(['key' => 'shop_name'], ['value' => $data['shop_name']]);
        BusinessSetting::updateOrCreate(['key' => 'currency'], ['value' => $data['currency']]);

        if ($request->has('operating_hours')) {
            BusinessSetting::updateOrCreate(['key' => 'operating_hours'], ['value' => $data['operating_hours']]);
        }

        return back()->with('status', 'Business settings updated.');
    }
}
