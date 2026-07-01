<?php

namespace App\Mcp\Prompts;

use App\Mcp\Concerns\ResolvesUser;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Generates a polite payment-reminder message for an invoice that is past its due date.')]
class OverdueInvoiceReminderPrompt extends Prompt
{
    use ResolvesUser;

    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'invoice_id',
                description: 'ID of the overdue invoice.',
                required: true,
            ),
            new Argument(
                name: 'tone',
                description: 'Tone of the reminder, e.g. friendly, firm, formal.',
            ),
        ];
    }

    public function handle(Request $request): Response
    {
        $invoice = $this->resolveUser($request)
            ->invoices()
            ->with(['customer', 'items'])
            ->findOrFail($request->integer('invoice_id'));

        $tone = $request->string('tone') ?: 'polite';

        $items = $invoice->items->map(fn ($item) => "- {$item->item_name} (qty {$item->qty} @ {$item->price}) = {$item->total_price}")->implode("\n");

        $system = "You are a billing assistant. Write a {$tone} payment reminder email to {$invoice->customer?->name} for invoice {$invoice->invoice_number}. ".
            "The invoice is past due (opened {$invoice->open_date}".($invoice->due_date ? ", due {$invoice->due_date}" : '').
            ") with a total of {$invoice->total} {$invoice->currency}. Include the line items below and a clear call to action to settle the invoice.\n\nItems:\n{$items}";

        return Response::text($system)->asAssistant();
    }
}
