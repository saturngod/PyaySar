<?php

use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserPreference;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

// Note: the makeInvoice() helper is defined globally in tests/Feature/InvoiceReportTest.php
// and is available to all feature tests (Pest loads them in a single process).

// ──────────────────────────────────────────────
// invoices.json — full invoice + preference for client-side PDF
// ──────────────────────────────────────────────

describe('invoices.json', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        UserPreference::create([
            'user_id' => $this->user->id,
            'company_name' => 'Test Co',
        ]);
        actingAs($this->user);
    });

    test('returns the full invoice with items, customer and preference', function () {
        $invoice = makeInvoice($this->user);
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Widget',
            'description' => 'A widget',
            'price' => 50.00,
            'qty' => 2,
            'total_price' => 100.00,
        ]);

        get(route('invoices.json', $invoice))
            ->assertStatus(200)
            ->assertJsonPath('invoice.id', $invoice->id)
            ->assertJsonPath('invoice.customer.id', $invoice->customer_id)
            ->assertJsonStructure([
                'invoice' => [
                    'id', 'invoice_number', 'currency', 'items', 'customer',
                ],
                'userPreference' => ['company_name'],
            ])
            ->assertJsonCount(1, 'invoice.items');
    });

    test('is forbidden for another user invoice (ownership enforced)', function () {
        $otherUser = User::factory()->create();
        $invoice = makeInvoice($otherUser);

        get(route('invoices.json', $invoice))->assertForbidden();
    });
});

// Kept outside the describe() above so its beforeEach (which authenticates)
// does not run — this test must execute as a guest.
test('invoices.json requires authentication', function () {
    $user = User::factory()->create();
    $invoice = makeInvoice($user);

    get(route('invoices.json', $invoice))->assertRedirect(route('login'));
});
