<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'contact_phone' => $this->faker->optional()->phoneNumber(),
            'contact_email' => $this->faker->optional()->companyEmail(),
            'address' => $this->faker->optional()->streetAddress() . "\n" .
                         $this->faker->optional()->city() . ", " .
                         $this->faker->optional()->state() . " " .
                         $this->faker->optional()->postcode(),
        ];
    }

    /**
     * Create an individual customer.
     */
    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->name(),
            'contact_person' => $this->faker->name(),
            'contact_email' => $this->faker->email(),
        ]);
    }

    /**
     * Create a business customer.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'contact_email' => $this->faker->companyEmail(),
        ]);
    }

    /**
     * Create a customer with complete contact information.
     */
    public function withFullContact(): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->email(),
            'address' => $this->faker->streetAddress() . "\n" .
                         $this->faker->city() . ", " .
                         $this->faker->state() . " " .
                         $this->faker->postcode(),
        ]);
    }
}