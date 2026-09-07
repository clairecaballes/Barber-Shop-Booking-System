<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsOwner(): User
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_store_converts_cost_to_centavos(): void
    {
        $this->actingAsOwner();

        $this->post(route('expenses.store'), [
            'item' => 'Clipper oil',
            'cost' => '149.75',
            'expense_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('expenses', [
            'item' => 'Clipper oil',
            'cost' => 14975,
        ]);
    }

    public function test_sales_page_shows_net_sales_after_expenses(): void
    {
        $this->actingAsOwner();

        Booking::create([
            'customer_id' => Customer::factory()->create()->id,
            'service_id' => Service::factory()->create()->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00:00',
            'price' => 15000,
            'status' => 'completed',
        ]);

        Expense::factory()->create([
            'item' => 'Rent share',
            'cost' => 5000,
            'expense_date' => now()->toDateString(),
        ]);

        $this->get(route('sales.index'))
            ->assertOk()
            ->assertSee('₱150.00') // overall sales
            ->assertSee('₱50.00') // total expenses
            ->assertSee('₱100.00'); // net sales
    }

    public function test_period_dropdown_filters_expense_items(): void
    {
        $this->actingAsOwner();

        Expense::factory()->create([
            'item' => 'Clipper oil',
            'cost' => 3000,
            'expense_date' => now()->toDateString(),
        ]);
        Expense::factory()->create([
            'item' => 'Last year rent',
            'cost' => 900000,
            'expense_date' => now()->subYear()->toDateString(),
        ]);

        $this->get(route('sales.index', ['expense_period' => 'weekly']))
            ->assertOk()
            ->assertSee('Clipper oil')
            ->assertDontSee('Last year rent');

        $this->get(route('sales.index', ['expense_period' => 'yearly']))
            ->assertOk()
            ->assertSee('Clipper oil')
            ->assertDontSee('Last year rent');
    }

    public function test_invalid_period_falls_back_to_monthly(): void
    {
        $this->actingAsOwner();

        Expense::factory()->create([
            'item' => 'Today supply',
            'expense_date' => now()->toDateString(),
        ]);

        $this->get(route('sales.index', ['expense_period' => 'bogus']))
            ->assertOk()
            ->assertSee('<option value="monthly" selected', false);
    }

    public function test_update_converts_cost_to_centavos(): void
    {
        $this->actingAsOwner();

        $expense = Expense::factory()->create(['cost' => 5000]);

        $this->patch(route('expenses.update', $expense), [
            'item' => 'Clipper oil',
            'cost' => '120.50',
            'expense_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'item' => 'Clipper oil',
            'cost' => 12050,
        ]);
    }

    public function test_destroy_removes_expense(): void
    {
        $this->actingAsOwner();

        $expense = Expense::factory()->create();

        $this->delete(route('expenses.destroy', $expense))->assertRedirect();

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_guests_cannot_manage_expenses(): void
    {
        $expense = Expense::factory()->create();

        $this->post(route('expenses.store'), [
            'item' => 'Test',
            'cost' => 100,
            'expense_date' => now()->toDateString(),
        ])->assertRedirect(route('login'));

        $this->delete(route('expenses.destroy', $expense))->assertRedirect(route('login'));
    }
}
