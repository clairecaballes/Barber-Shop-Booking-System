<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_relationships(): void
    {
        $service = Service::factory()->create();

        $booking = Booking::factory()->for($service)->create();

        $this->assertTrue($booking->service->is($service));
        $this->assertTrue($service->bookings->contains($booking));
    }

    public function test_customer_relationships(): void
    {
        $customer = Customer::factory()->create();

        $booking = Booking::factory()->for($customer)->create();

        $this->assertTrue($booking->customer->is($customer));
        $this->assertTrue($customer->bookings->contains($booking));
    }

    public function test_booking_price_snapshots_service_price(): void
    {
        $service = Service::factory()->create(['price' => 25000]);

        $booking = Booking::factory()->for($service)->create();

        $this->assertEquals(25000, $booking->price);
    }

    public function test_duplicate_booking_is_rejected(): void
    {
        $service = Service::factory()->create();
        $customer = Customer::factory()->create();

        $attributes = [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-09-15',
            'appointment_time' => '10:00:00',
            'price' => $service->price,
            'status' => 'booked',
        ];

        Booking::create($attributes);

        $this->expectException(QueryException::class);

        Booking::create($attributes);
    }

    public function test_booking_requires_valid_customer(): void
    {
        $service = Service::factory()->create();

        $this->expectException(QueryException::class);

        Booking::create([
            'customer_id' => 999999,
            'service_id' => $service->id,
            'appointment_date' => '2026-09-15',
            'appointment_time' => '10:00:00',
            'price' => $service->price,
            'status' => 'booked',
        ]);
    }

    public function test_quick_booking_creates_new_customer_by_name_and_redirects_to_calendar(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        $service = Service::factory()->create(['active' => true]);

        $this->actingAs($user)->post(route('quick-bookings.store'), [
            'customer_name' => 'Juan Dela Cruz',
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '14:30',
        ])->assertRedirect(route('calendar.index'));

        $customer = Customer::where('name', 'Juan Dela Cruz')->first();
        $this->assertNotNull($customer);

        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_time' => '14:30:00',
        ]);
    }

    public function test_quick_booking_with_existing_customer_uses_that_customer(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        $customer = Customer::factory()->create();
        $service = Service::factory()->create(['active' => true]);

        $this->actingAs($user)->post(route('quick-bookings.store'), [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '09:00',
        ])->assertRedirect(route('calendar.index'));

        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_time' => '09:00:00',
        ]);
    }

    public function test_quick_booking_returns_json_for_ajax_with_new_customer(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        $service = Service::factory()->create(['active' => true]);

        $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->post(route('quick-bookings.store'), [
                'customer_name' => 'Maria Santos',
                'service_id' => $service->id,
                'appointment_date' => now()->addDay()->toDateString(),
                'appointment_time' => '15:15',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Booking added to calendar.']);

        $customer = Customer::where('name', 'Maria Santos')->first();
        $this->assertNotNull($customer);
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_time' => '15:15:00',
        ]);
    }

    public function test_dashboard_and_bookings_pages_render(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        $customer = Customer::factory()->create(['name' => 'Test Customer']);
        $service = Service::factory()->create(['name' => 'Haircut', 'active' => true]);

        Booking::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00:00',
            'price' => $service->price,
            'status' => 'booked',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Test Customer')
            ->assertSee('Haircut');

        $this->actingAs($user)
            ->get(route('bookings.index'))
            ->assertOk()
            ->assertSee('Test Customer');
    }

    public function test_calendar_events_returns_booking_with_time_and_name(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        $customer = Customer::factory()->create(['name' => 'Anna Cruz']);
        $service = Service::factory()->create(['name' => 'Beard Trim', 'active' => true]);

        Booking::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-09-15',
            'appointment_time' => '14:00:00',
            'price' => $service->price,
            'status' => 'booked',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('api.calendar.events').'?start=2026-09-01&end=2026-09-30')
            ->assertOk();

        $events = $response->json();
        $this->assertNotEmpty($events);
        $this->assertStringContainsString('Anna Cruz', $events[0]['title']);
        $this->assertStringContainsString('14:00', $events[0]['start']);
        $this->assertEquals('#60a5fa', $events[0]['color']);
    }
}
