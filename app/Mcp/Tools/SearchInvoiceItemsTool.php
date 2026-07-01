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

#[Description('Search previously-invoiced line-item names by keyword for the authenticated user. Useful for suggesting item names while drafting invoices.')]
#[IsReadOnly]
class SearchInvoiceItemsTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected InvoiceService $invoices) {}

    public function handle(Request $request): ResponseFactory
    {
        $names = $this->invoices->searchItems($this->resolveUser($request), $request->string('query'));

        return Response::structured(['items' => $names]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Keyword to search item names by.')->required(),
        ];
    }
}
