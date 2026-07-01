<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\ResolvesUser;
use App\Services\InvoiceService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Delete an invoice belonging to the authenticated user.')]
#[IsDestructive]
class DeleteInvoiceTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected InvoiceService $invoices) {}

    public function handle(Request $request): Response
    {
        $invoice = $this->resolveUser($request)->invoices()->findOrFail($request->integer('invoice_id'));

        $this->invoices->delete($invoice);

        return Response::text("Invoice {$invoice->invoice_number} deleted.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'invoice_id' => $schema->integer()->description('ID of the invoice to delete.')->required(),
        ];
    }
}
