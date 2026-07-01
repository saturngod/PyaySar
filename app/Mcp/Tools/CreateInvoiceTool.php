<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\ResolvesUser;
use App\Services\InvoiceService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new invoice with one or more line items for the authenticated user. Totals are computed from the line items.')]
class CreateInvoiceTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected InvoiceService $invoices) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number',
            'customer_id' => 'required|integer|exists:customers,id',
            'open_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:open_date',
            'status' => 'required|in:Draft,Sent,Received,Reject',
            'currency' => 'required|in:USD,MMK',
            'notes' => 'nullable|string',
            'bank_account_info' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $invoice = $this->invoices->create($this->resolveUser($request), $validated);

        return Response::structured([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total' => $invoice->total,
        ])->withMeta('text', "Invoice {$invoice->invoice_number} created.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'invoice_number' => $schema->string()->description('Unique invoice number, e.g. INV-101.')->required(),
            'customer_id' => $schema->integer()->description('ID of the customer to bill.')->required(),
            'open_date' => $schema->string()->description('Invoice open date (YYYY-MM-DD).')->required(),
            'due_date' => $schema->string()->description('Optional due date (YYYY-MM-DD).'),
            'status' => $schema->string()->enum(['Draft', 'Sent', 'Received', 'Reject'])->description('Initial status.')->required(),
            'currency' => $schema->string()->enum(['USD', 'MMK'])->description('Invoice currency.')->required(),
            'items' => $schema->array()
                ->min(1)
                ->items($schema->object([
                    'item_name' => $schema->string()->required(),
                    'description' => $schema->string(),
                    'qty' => $schema->integer()->required(),
                    'price' => $schema->number()->required(),
                ]))
                ->description('One or more line items.')
                ->required(),
            'notes' => $schema->string()->description('Optional notes shown on the invoice.'),
            'bank_account_info' => $schema->string()->description('Optional bank account details for payment.'),
        ];
    }
}
