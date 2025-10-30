<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => $this->faker->company(),
            'company_email' => $this->faker->companyEmail(),
            'company_address' => $this->faker->streetAddress() . "\n" .
                               $this->faker->city() . ", " .
                               $this->faker->state() . " " .
                               $this->faker->postcode(),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'default_terms' => 'Payment is due within 30 days of receipt of invoice. ' .
                              'Late payments will incur a 1.5% monthly fee.',
            'default_notes' => 'Thank you for your business!',
            'pdf_settings' => [
                'font_size' => $this->faker->randomElement([10, 11, 12]),
                'font_family' => $this->faker->randomElement(['Arial', 'Helvetica', 'Times New Roman']),
                'margin_top' => $this->faker->numberBetween(15, 25),
                'margin_right' => $this->faker->numberBetween(15, 25),
                'margin_bottom' => $this->faker->numberBetween(15, 25),
                'margin_left' => $this->faker->numberBetween(15, 25),
                'show_logo' => $this->faker->boolean(80),
                'show_company_details' => true,
                'show_item_description' => $this->faker->boolean(90),
            ],
        ];
    }

    /**
     * Create settings for a freelancer.
     */
    public function freelancer(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_name' => $this->faker->name(),
            'company_email' => $this->faker->email(),
            'default_terms' => 'Payment is due within 15 days of receipt of invoice.',
        ]);
    }

    /**
     * Create settings for a small business.
     */
    public function smallBusiness(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_name' => $this->faker->company(),
            'company_email' => $this->faker->companyEmail(),
            'default_terms' => 'Payment is due within 30 days of receipt of invoice. ' .
                              'A 2% discount is available for payments made within 10 days.',
        ]);
    }
}