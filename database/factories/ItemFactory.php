<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'JPY']),
        ];
    }

    /**
     * Create a service item.
     */
    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Web Design',
                'Web Development',
                'SEO Optimization',
                'Content Writing',
                'Graphic Design',
                'Marketing Consultation',
                'Project Management',
                'Technical Support',
                'Data Analysis',
                'Social Media Management',
            ]),
            'description' => $this->faker->sentence(),
        ]);
    }

    /**
     * Create a product item.
     */
    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Software License',
                'Hardware Component',
                'Subscription Plan',
                'Digital Product',
                'Physical Product',
                'Course Material',
                'Template Package',
                'Tool Kit',
            ]),
            'description' => $this->faker->optional()->paragraph(),
        ]);
    }

    /**
     * Create a low-priced item.
     */
    public function lowPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 5, 50),
        ]);
    }

    /**
     * Create a high-priced item.
     */
    public function highPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 500, 5000),
        ]);
    }
}