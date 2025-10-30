<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_items()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Item::class, $user->items->first());
        $this->assertEquals(1, $user->items()->count());
    }

    public function test_user_can_have_customers()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Customer::class, $user->customers->first());
        $this->assertEquals(1, $user->customers()->count());
    }

    public function test_user_can_have_quotes()
    {
        $user = User::factory()->create();
        $quote = Quote::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Quote::class, $user->quotes->first());
        $this->assertEquals(1, $user->quotes()->count());
    }

    public function test_user_can_have_invoices()
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Invoice::class, $user->invoices->first());
        $this->assertEquals(1, $user->invoices()->count());
    }

    public function test_user_can_have_settings()
    {
        $user = User::factory()->create();
        $setting = Setting::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Setting::class, $user->settings);
    }

    public function test_quote_can_have_items()
    {
        $quote = Quote::factory()->create();
        $item = Item::factory()->create(['user_id' => $quote->user_id]);
        $quoteItem = QuoteItem::factory()->create([
            'quote_id' => $quote->id,
            'item_id' => $item->id,
        ]);

        $this->assertInstanceOf(QuoteItem::class, $quote->quoteItems->first());
        $this->assertEquals(1, $quote->quoteItems()->count());
    }

    public function test_quote_can_generate_quote_number()
    {
        $quote = Quote::factory()->create(['quote_number' => null]);

        $this->assertNotNull($quote->quote_number);
        $this->assertStringStartsWith('Q-', $quote->quote_number);
    }

    public function test_quote_calculates_totals()
    {
        $user = User::factory()->create();
        $quote = Quote::create([
            'user_id' => $user->id,
            'customer_id' => Customer::factory()->create(['user_id' => $user->id])->id,
            'title' => 'Test Quote',
            'date' => now(),
            'currency' => 'USD',
            'status' => 'Draft',
            'sub_total' => 0,
            'discount_amount' => 0,
            'total' => 0,
        ]);

        $item1 = Item::factory()->create(['user_id' => $user->id, 'price' => 100]);
        $item2 = Item::factory()->create(['user_id' => $user->id, 'price' => 200]);

        QuoteItem::create([
            'quote_id' => $quote->id,
            'item_id' => $item1->id,
            'price' => 100,
            'qty' => 2,
        ]);

        QuoteItem::create([
            'quote_id' => $quote->id,
            'item_id' => $item2->id,
            'price' => 200,
            'qty' => 1,
        ]);

        // Refresh quote with its items
        $quote = $quote->fresh(['quoteItems']);
        $quote->calculateTotals();

        // Save the updated totals
        $quote->saveQuietly();

        $this->assertEquals(400, $quote->sub_total); // (100 * 2) + (200 * 1)
        $this->assertEquals(400, $quote->total);
    }

    public function test_invoice_can_be_marked_as_paid()
    {
        $invoice = Invoice::factory()->create(['status' => 'Sent']);

        $invoice->markAsPaid();

        $this->assertEquals('Paid', $invoice->status);
    }

    public function test_invoice_can_check_if_overdue()
    {
        $overdueInvoice = Invoice::factory()->create([
            'due_date' => now()->subDays(10),
            'status' => 'Sent'
        ]);

        $notOverdueInvoice = Invoice::factory()->create([
            'due_date' => now()->addDays(10),
            'status' => 'Sent'
        ]);

        $this->assertTrue($overdueInvoice->isOverdue());
        $this->assertFalse($notOverdueInvoice->isOverdue());
    }

    public function test_customer_calculates_totals()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $quote1 = Quote::factory()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'total' => 1000
        ]);

        $quote2 = Quote::factory()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'total' => 500
        ]);

        $this->assertEquals(1500, $customer->total_quotes_value);
    }

    public function test_item_has_formatted_price()
    {
        $item = Item::factory()->create(['price' => 1234.56]);

        $this->assertEquals('1,234.56', $item->formatted_price);
    }
}