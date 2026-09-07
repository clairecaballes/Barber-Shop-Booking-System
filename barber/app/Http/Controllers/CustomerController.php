<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('messenger_id', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('customers.index', ['customers' => $customers]);
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'messenger_id' => ['nullable', 'string', 'max:255', 'unique:customers,messenger_id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        Customer::create($data);

        return redirect()->route('customers.index')->with('status', 'Customer added.');
    }

    public function show(Customer $customer): View
    {
        $bookings = $customer->bookings()
            ->with('service')
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(10);

        $stats = [
            'total_bookings' => $customer->bookings()->count(),
            'completed_bookings' => $customer->bookings()->completed()->count(),
            'total_spent' => $customer->bookings()->completed()->sum('price'),
            'last_haircut' => $customer->bookings()
                ->completed()
                ->orderByDesc('appointment_date')
                ->first()?->appointment_date,
            'upcoming' => $customer->bookings()
                ->active()
                ->whereDate('appointment_date', '>=', now()->toDateString())
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->first(),
            'cancellation_count' => $customer->bookings()
                ->where('status', 'cancelled')
                ->count(),
        ];

        return view('customers.show', [
            'customer' => $customer,
            'bookings' => $bookings,
            'stats' => $stats,
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', ['customer' => $customer]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'messenger_id' => ['nullable', 'string', 'max:255', 'unique:customers,messenger_id,'.$customer->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($data);

        return redirect()->route('customers.index')->with('status', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->bookings()->exists()) {
            return back()->withErrors(['customer' => 'Cannot delete a customer with existing bookings.']);
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('status', 'Customer deleted.');
    }
}
