<?php

namespace Tests\Feature;

use App\Models\BlockedSlot;
use App\Models\BusinessSetting;
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

    protected function openEveryDay(): void
    {
        BusinessSetting::create([
            'key' => 'operating_hours',
            'value' => collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
                ->mapWithKeys(fn (string $day) => [$day => ['open' => '09:00', 'close' => '18:00']])
                ->all(),
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

        $this->actingAs($user);

        BlockedSlot::create(['date' => $date]);

        $this->actingAs($user)
            ->postJson(route('calendar.blocks.store'), ['date' => $date])
            ->assertStatus(422);
    }

    public function test_calendar_events_include_blocked_day(): void
    {
        $user = $this->owner();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($user);

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

        $this->actingAs($user);

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

        $this->actingAs($user);

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

    public function test_block_with_window_stores_leave_times(): void
    {
        $user = $this->owner();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($user)
            ->postJson(route('calendar.blocks.store'), [
                'date' => $date,
                'start_time' => '13:00',
                'end_time' => '15:30',
            ])
            ->assertOk();

        $this->assertDatabaseHas('blocked_slots', [
            'date' => $date,
            'start_time' => '13:00',
            'end_time' => '15:30',
        ]);
    }

    public function test_block_defaults_to_operating_hours_when_times_omitted(): void
    {
        $user = $this->owner();

        $this->actingAs($user);

        $this->openEveryDay();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($user)
            ->postJson(route('calendar.blocks.store'), ['date' => $date])
            ->assertOk();

        $this->assertDatabaseHas('blocked_slots', [
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);
    }

    public function test_booking_outside_leave_window_is_still_allowed(): void
    {
        $user = $this->owner();

        $this->actingAs($user);

        $this->openEveryDay();
        $service = Service::factory()->create(['active' => true, 'duration' => 60]);
        $date = now()->addDays(3)->toDateString();

        // Barber on leave 09:00–12:00 only.
        BlockedSlot::create([
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);

        $slots = $this->actingAs($user)
            ->getJson(route('api.availability.index').'?date='.$date.'&service_id='.$service->id)
            ->assertOk()
            ->json('slots');

        $this->assertNotEmpty($slots);
        $this->assertNotContains('09:00:00', $slots);
        $this->assertNotContains('11:00:00', $slots);
        $this->assertContains('12:00:00', $slots);
    }

    public function test_booking_inside_leave_window_is_rejected(): void
    {
        $user = $this->owner();

        $this->actingAs($user);

        $this->openEveryDay();
        $customer = Customer::factory()->create();
        $service = Service::factory()->create(['active' => true, 'duration' => 60]);
        $date = now()->addDays(3)->toDateString();

        BlockedSlot::create([
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);

        $this->actingAs($user)
            ->postJson(route('quick-bookings.store'), [
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'appointment_date' => $date,
                'appointment_time' => '10:00',
            ])
            ->assertStatus(422);
    }

    public function test_calendar_events_expose_leave_window(): void
    {
        $user = $this->owner();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($user);

        BlockedSlot::create([
            'date' => $date,
            'reason' => 'Barber on leave',
            'start_time' => '13:00:00',
            'end_time' => '15:30:00',
        ]);

        $events = $this->actingAs($user)
            ->getJson(route('api.calendar.events').'?start='.now()->toDateString().'&end='.now()->addDays(7)->toDateString())
            ->assertOk()
            ->json();

        $blocked = collect($events)->firstWhere('extendedProps.blocked', true);
        $this->assertNotNull($blocked);
        $this->assertFalse($blocked['allDay']);
        $this->assertStringContainsString('13:00', $blocked['start']);
        $this->assertSame('13:00', $blocked['extendedProps']['startTime']);
        $this->assertSame('15:30', $blocked['extendedProps']['endTime']);
        $this->assertNotNull($blocked['extendedProps']['blockId']);
    }

    public function test_cancel_leave_deletes_the_block(): void
    {
        $user = $this->owner();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($user);

        $block = BlockedSlot::create(['date' => $date]);

        $this->actingAs($user)
            ->deleteJson(route('calendar.blocks.destroy', $block))
            ->assertOk()
            ->assertJson(['message' => 'Leave cancelled.']);

        $this->assertDatabaseMissing('blocked_slots', ['id' => $block->id]);
    }

    public function test_leave_window_can_be_moved_to_new_times(): void
    {
        $user = $this->owner();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($user);

        $block = BlockedSlot::create([
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);

        $this->actingAs($user)
            ->patchJson(route('calendar.blocks.update', $block), [
                'start_time' => '14:00',
                'end_time' => '17:30',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Leave updated.']);

        $this->assertDatabaseHas('blocked_slots', [
            'id' => $block->id,
            'start_time' => '14:00',
            'end_time' => '17:30',
        ]);
    }

    public function test_leave_window_rejects_end_before_start(): void
    {
        $user = $this->owner();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($user);

        $block = BlockedSlot::create([
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);

        $this->actingAs($user)
            ->patchJson(route('calendar.blocks.update', $block), [
                'start_time' => '16:00',
                'end_time' => '15:30',
            ])
            ->assertStatus(422);

        // The stored window is untouched.
        $this->assertDatabaseHas('blocked_slots', [
            'id' => $block->id,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);
    }
}
