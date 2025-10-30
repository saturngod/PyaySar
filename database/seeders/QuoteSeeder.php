<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create quotes for each user
        User::all()->each(function (User $user) {
            $customers = $user->customers;
            $items = $user->items;

            if ($customers->isEmpty() || $items->isEmpty()) {
                return;
            }

            // Create 5-10 quotes per user
            for ($i = 0; $i < rand(5, 10); $i++) {
                $customer = $customers->random();

                $quote = Quote::factory()->create([
                    'user_id' => $user->id,
                    'customer_id' => $customer->id,
                    'date' => now()->subDays(rand(0, 90)),
                    'status' => rand(0, 2) === 0 ? 'Draft' : (rand(0, 1) === 0 ? 'Sent' : 'Seen'),
                ]);

                // Add 2-6 items to each quote
                $itemCount = rand(2, 6);
                $selectedItems = $items->random(min($itemCount, $items->count()));

                foreach ($selectedItems as $item) {
                    QuoteItem::factory()->create([
                        'quote_id' => $quote->id,
                        'item_id' => $item->id,
                        'price' => $item->price,
                        'qty' => rand(1, 5),
                    ]);
                }

                // Recalculate totals
                $quote->calculateTotals();
                $quote->saveQuietly();
            }
        });
    }
}