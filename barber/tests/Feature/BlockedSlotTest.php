<?php

namespace Tests\Feature;

use App\Models\BlockedSlot;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockedSlotTest extends TestCase
{
    use RefreshDatabase;

    protected function owner(): User
    {
        return User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);
    }

    public function test_block_day_via_ajax_returns_json(): void
    {
        $user = $this->owner();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($user)
            ->postJson(route('calendar.blocks.store'), ['date' => $date])
            ->assertOk()
            ->assertJson(['message' => 'Schedule blocked — Barber on leave.']);

        $this->assertDatabaseHas('blocked_slots', [
            'date' => $date,
            'reason' => 'Barber on leave',
        ]);
    }

    public function test_duplicate_block_is_rejected(): void
    {
        $user = $this->owner();
        $date = now()->addDays(3)->toDateString();

        BlockedSlot::create(['date' => $date]);

        $this->actingAs($user)
            ->postJson(route('calendar.blocks.store'), ['date' => $date])
            ->assertStatus(422);
    }

    public function test_calendar_events_include_blocked_day(): void
    {
        $user = $this->owner();
        $date = now()->addDays(3)->toDateString();

        BlockedSlot::create(['date' => $date, 'reason' => 'Barber on leave']);

        $events = $this->actingAs($user)
            ->getJson(route('api.calendar.events').'?start='.now()->toDateString().'&end='.now()->addDays(7)->toDateString())
            ->assertOk()
            ->json();

        $blocked = collect($events)->firstWhere('extendedProps.blocked', true);
        $this->assertNotNull($blocked);
        $this->assertStringContainsString('Barber on leave', $blocked['title']);
        $this->assertEquals($date, $blocked['start']);
    }

    public function test_no_slots_available_on_blocked_day(): void
    {
        $user = $this->owner();
        $service = Service::factory()->create(['active' => true]);
        $date = now()->addDays(3)->toDateString();

        BlockedSlot::create(['date' => $date]);

        $response = $this->actingAs($user)
            ->getJson(route('api.availability.index').'?date='.$date.'&service_id='.$service->id)
            ->assertOk();

        $this->assertEmpty($response->json('slots'));
    }

    public function test_booking_on_blocked_day_is_rejected(): void
    {
        $user = $this->owner();
        $customer = Customer::factory()->create();
        $service = Service::factory()->create(['active' => true]);
        $date = now()->addDays(3)->toDateString();

        BlockedSlot::create(['date' => $date]);

        $this->actingAs($user)
            ->postJson(route('quick-bookings.store'), [
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'appointment_date' => $date,
                'appointment_time' => '10:00',
            ])
            ->assertStatus(422);
    }
}
