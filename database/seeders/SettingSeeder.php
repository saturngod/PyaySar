<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create settings for each user (only if they don't exist)
        User::all()->each(function (User $user) {
            if (!$user->settings) {
                Setting::factory()->create(['user_id' => $user->id]);
            }
        });
    }
}