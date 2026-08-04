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

#[Description('Update an existing invoice. Replaces all line items and recalculates totals. Records a status-history entry when the status changes.')]
class UpdateInvoiceTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected InvoiceService $invoices) {}

    public function handle(Request $request): ResponseFactory
    {
        $user = $this->resolveUser($request);

        $invoice = $user->invoices()->findOrFail($request->integer('invoice_id'));

        $validated = $request->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number,'.$invoice->id,
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

        $invoice = $this->invoices->update($invoice, $validated);

        return Response::structured([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'total' => $invoice->total,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'invoice_id' => $schema->integer()->description('ID of the invoice to update.')->required(),
            'invoice_number' => $schema->string()->required(),
            'customer_id' => $schema->integer()->required(),
            'open_date' => $schema->string()->description('Invoice open date as a full YYYY-MM-DD string. Today is '.now()->toDateString().' — use the current year when the user omits it.')->required(),
            'due_date' => $schema->string()->description('Optional due date as a full YYYY-MM-DD string (must be on or after open_date). Use the current year when the user omits it.'),
            'status' => $schema->string()->enum(['Draft', 'Sent', 'Received', 'Reject'])->required(),
            'currency' => $schema->string()->enum(['USD', 'MMK'])->required(),
            'items' => $schema->array()
                ->min(1)
                ->items($schema->object([
                    'item_name' => $schema->string()->required(),
                    'description' => $schema->string(),
                    'qty' => $schema->integer()->required(),
                    'price' => $schema->number()->required(),
                ]))
                ->required(),
            'notes' => $schema->string(),
            'bank_account_info' => $schema->string(),
        ];
    }
}
