<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_page_loads_with_data(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        Booking::factory()->count(3)->completed()->create();
        Expense::factory()->count(2)->create();

        $this->actingAs($user)->get(route('sales.index'))->assertOk();
    }
}
