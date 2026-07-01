<?php

namespace App\Mcp\Resources;

use App\Mcp\Concerns\ResolvesUser;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Description('A single invoice with its line items, customer, and status history for the authenticated user.')]
#[MimeType('application/json')]
class InvoiceResource extends Resource implements HasUriTemplate
{
    use ResolvesUser;

    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('invoice://invoices/{id}');
    }

    public function handle(Request $request): Response
    {
        $invoice = $this->resolveUser($request)
            ->invoices()
            ->with(['items', 'customer', 'statusHistories'])
            ->findOrFail($request->integer('id'));

        return Response::json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'currency' => $invoice->currency,
            'open_date' => (string) $invoice->open_date,
            'due_date' => $invoice->due_date ? (string) $invoice->due_date : null,
            'sub_total' => $invoice->sub_total,
            'discount' => $invoice->discount,
            'total' => $invoice->total,
            'notes' => $invoice->notes,
            'bank_account_info' => $invoice->bank_account_info,
            'customer' => $invoice->customer ? [
                'id' => $invoice->customer->id,
                'name' => $invoice->customer->name,
                'email' => $invoice->customer->email,
            ] : null,
            'items' => $invoice->items->map(fn ($item) => [
                'item_name' => $item->item_name,
                'description' => $item->description,
                'qty' => $item->qty,
                'price' => $item->price,
                'total_price' => $item->total_price,
            ]),
            'status_history' => $invoice->statusHistories->map(fn ($history) => [
                'from_status' => $history->from_status,
                'to_status' => $history->to_status,
                'changed_at' => (string) $history->changed_at,
            ]),
        ]);
    }
}
