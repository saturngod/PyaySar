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
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List invoices for the authenticated user, with optional filtering by status, date range, or customer.')]
#[IsReadOnly]
class ListInvoicesTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected InvoiceService $invoices) {}

    public function handle(Request $request): ResponseFactory
    {
        $invoices = $this->invoices->list($this->resolveUser($request), $request->only([
            'status', 'date_from', 'date_to', 'customer_id',
        ]));

        return Response::structured([
            'invoices' => collect($invoices)->map(fn ($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'currency' => $invoice->currency,
                'open_date' => (string) $invoice->open_date,
                'due_date' => $invoice->due_date ? (string) $invoice->due_date : null,
                'sub_total' => $invoice->sub_total,
                'total' => $invoice->total,
                'customer' => $invoice->customer ? ['id' => $invoice->customer->id, 'name' => $invoice->customer->name] : null,
            ]),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()
                ->enum(['Draft', 'Sent', 'Received', 'Reject'])
                ->description('Filter by invoice status.'),
            'date_from' => $schema->string()->description('Filter invoices opened on or after this date (YYYY-MM-DD).'),
            'date_to' => $schema->string()->description('Filter invoices opened on or before this date (YYYY-MM-DD).'),
            'customer_id' => $schema->integer()->description('Filter invoices for a specific customer ID.'),
        ];
    }
}
