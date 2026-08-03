<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function makeInvoiceForInvoiceTests(User $user, array $overrides = []): Invoice
{
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    return Invoice::create(array_merge([
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-TEST-'.fake()->unique()->numerify('###'),
        'currency' => 'MMK',
        'status' => 'Draft',
        'open_date' => Carbon::today()->toDateString(),
        'sub_total' => 100.00,
        'discount' => 0,
        'total' => 100.00,
    ], $overrides));
}

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
        $invoice = makeInvoiceForInvoiceTests($this->user);
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
        $invoice = makeInvoiceForInvoiceTests($otherUser);

        get(route('invoices.json', $invoice))->assertForbidden();
    });
});

// Kept outside the describe() above so its beforeEach (which authenticates)
// does not run — this test must execute as a guest.
test('invoices.json requires authentication', function () {
    $user = User::factory()->create();
    $invoice = makeInvoiceForInvoiceTests($user);

    get(route('invoices.json', $invoice))->assertRedirect(route('login'));
});

describe('invoice PDF images', function () {
    beforeEach(function () {
        Storage::fake(config('filesystems.default'));
        $this->user = User::factory()->create();
        $this->invoice = makeInvoiceForInvoiceTests($this->user);
        actingAs($this->user);
    });

    test('streams the customer avatar from a same-origin authenticated route', function () {
        $path = 'avatars/customer.png';
        Storage::put($path, 'avatar-bytes');
        $this->invoice->customer()->update(['avatar' => $path]);

        get(route('invoices.pdf-image', [$this->invoice, 'avatar']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, private')
            ->assertStreamedContent('avatar-bytes');
    });

    test('streams the authenticated user company logo', function () {
        $path = 'logos/company.png';
        Storage::put($path, 'logo-bytes');
        UserPreference::create([
            'user_id' => $this->user->id,
            'company_logo' => $path,
        ]);

        get(route('invoices.pdf-image', [$this->invoice, 'logo']))
            ->assertOk()
            ->assertStreamedContent('logo-bytes');
    });

    test('does not expose images from another user invoice', function () {
        $otherInvoice = makeInvoiceForInvoiceTests(User::factory()->create());

        get(route('invoices.pdf-image', [$otherInvoice, 'avatar']))->assertForbidden();
    });
});
