<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create items for each user
        User::all()->each(function (User $user) {
            // Create some services
            Item::factory(5)->service()->create(['user_id' => $user->id]);

            // Create some products
            Item::factory(3)->product()->create(['user_id' => $user->id]);

            // Create some mixed items
            Item::factory(7)->create(['user_id' => $user->id]);
        });
    }
}