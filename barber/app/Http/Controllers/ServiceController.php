<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('services.index', [
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'duration' => ['required', 'integer', 'min:15', 'max:240'],
            'active' => ['boolean'],
        ]);

        $data['price'] = $data['price'] * 100; // Convert pesos to centavos
        $data['active'] = $request->boolean('active', true);

        Service::create($data);

        return redirect()->route('services.index')->with('status', 'Service created.');
    }

    public function edit(Service $service): View
    {
        return view('services.edit', ['service' => $service]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'duration' => ['required', 'integer', 'min:15', 'max:240'],
            'active' => ['boolean'],
        ]);

        $data['price'] = $data['price'] * 100;
        $data['active'] = $request->boolean('active', true);

        $service->update($data);

        return redirect()->route('services.index')->with('status', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if ($service->bookings()->exists()) {
            return back()->withErrors(['service' => 'Cannot delete a service with existing bookings.']);
        }

        $service->delete();

        return redirect()->route('services.index')->with('status', 'Service deleted.');
    }
}
