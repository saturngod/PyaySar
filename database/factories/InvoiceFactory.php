<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'quote_id' => null,
            'title' => $this->faker->randomElement([
                'Web Development Invoice',
                'SEO Services Invoice',
                'Design Services Invoice',
                'Marketing Campaign Invoice',
                'Consulting Services Invoice',
                'Software Development Invoice',
                'Content Creation Invoice',
                'Mobile App Development Invoice',
            ]) . ' - ' . $this->faker->monthName() . ' ' . $this->faker->year(),
            'po_number' => $this->faker->optional()->randomElement([null, 'PO-' . $this->faker->numberBetween(1000, 9999)]),
            'date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'status' => $this->faker->randomElement(['Draft', 'Sent', 'Paid', 'Cancel']),
            'sub_total' => $this->faker->randomFloat(2, 100, 5000),
            'discount_amount' => $this->faker->randomFloat(2, 0, 500),
            'total' => 0, // Will be calculated automatically
            'terms' => 'Payment is due within 30 days of receipt of this invoice.',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create a draft invoice.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Draft',
        ]);
    }

    /**
     * Create a sent invoice.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Sent',
            'date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Create a paid invoice.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Paid',
            'date' => $this->faker->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }

    /**
     * Create an overdue invoice.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Sent',
            'date' => $this->faker->dateTimeBetween('-60 days', '-30 days'),
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}