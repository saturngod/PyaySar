<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

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
            'title' => $this->faker->randomElement([
                'Web Development Quote',
                'SEO Services Quote',
                'Design Services Quote',
                'Marketing Campaign Quote',
                'Consulting Services Quote',
                'Software Development Quote',
                'Content Creation Quote',
                'Mobile App Development Quote',
            ]) . ' - ' . $this->faker->monthName() . ' ' . $this->faker->year(),
            'po_number' => $this->faker->optional()->randomElement([null, 'PO-' . $this->faker->numberBetween(1000, 9999)]),
            'date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'status' => $this->faker->randomElement(['Draft', 'Sent', 'Seen']),
            'sub_total' => $this->faker->randomFloat(2, 100, 5000),
            'discount_amount' => $this->faker->randomFloat(2, 0, 500),
            'total' => 0, // Will be calculated automatically
            'terms' => 'Payment is due within 30 days of receipt of this quote.',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create a draft quote.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Draft',
        ]);
    }

    /**
     * Create a sent quote.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Sent',
            'date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Create a seen quote.
     */
    public function seen(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Seen',
            'date' => $this->faker->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }
}