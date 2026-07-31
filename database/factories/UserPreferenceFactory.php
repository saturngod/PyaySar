<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => $this->faker->company(),
            'company_email' => $this->faker->companyEmail(),
            'company_address' => $this->faker->address(),
            'company_logo' => null,
            'default_note' => $this->faker->optional()->sentence(),
            'default_bank_account_info' => $this->faker->optional()->text(),
        ];
    }
}
