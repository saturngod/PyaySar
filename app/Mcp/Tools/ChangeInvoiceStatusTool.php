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

#[Description('Transition an invoice to a new status (Draft, Sent, Received, Reject) and record the change in its status history.')]
class ChangeInvoiceStatusTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected InvoiceService $invoices) {}

    public function handle(Request $request): ResponseFactory
    {
        $invoice = $this->resolveUser($request)->invoices()->findOrFail($request->integer('invoice_id'));

        $invoice = $this->invoices->changeStatus($invoice, $request->string('status'));

        return Response::structured([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'invoice_id' => $schema->integer()->description('ID of the invoice.')->required(),
            'status' => $schema->string()->enum(['Draft', 'Sent', 'Received', 'Reject'])->description('New status.')->required(),
        ];
    }
}
