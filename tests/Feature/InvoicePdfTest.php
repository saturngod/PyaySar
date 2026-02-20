<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;

function createTestInvoiceForPdf(User $user)
{
    $customer = Customer::create([
        'user_id' => $user->id,
        'name' => 'Test Customer',
    ]);

    return Invoice::create([
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-TEST',
        'open_date' => now(),
        'status' => 'Draft',
        'currency' => 'USD',
        'sub_total' => 100,
        'total' => 100,
    ]);
}

it('requires authentication to download pdf', function () {
    $user = User::factory()->create();
    $invoice = createTestInvoiceForPdf($user);

    $response = $this->get(route('invoices.pdf', $invoice));

    $response->assertRedirect('/login');
});

it('forbids downloading another users invoice', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $invoice = createTestInvoiceForPdf($otherUser);

    $response = $this->actingAs($user)->get(route('invoices.pdf', $invoice));

    $response->assertForbidden();
});

it('allows owner to download their invoice pdf', function () {
    $user = User::factory()->create();

    $invoice = createTestInvoiceForPdf($user);

    $response = $this->actingAs($user)->get(route('invoices.pdf', $invoice));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});
