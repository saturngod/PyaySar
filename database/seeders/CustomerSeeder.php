<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create customers for each user
        User::all()->each(function (User $user) {
            // Create some business customers
            Customer::factory(6)->business()->withFullContact()->create(['user_id' => $user->id]);

            // Create some individual customers
            Customer::factory(4)->individual()->withFullContact()->create(['user_id' => $user->id]);
        });
    }
}