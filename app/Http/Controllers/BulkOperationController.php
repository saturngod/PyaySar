<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class BulkOperationController extends Controller
{
    /**
     * Bulk delete quotes.
     */
    public function bulkDeleteQuotes(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quote_ids' => ['required', 'array', 'min:1'],
            'quote_ids.*' => ['required', 'integer', 'exists:quotes,id'],
        ]);

        $deletedCount = auth()->user()
            ->quotes()
            ->whereIn('id', $validated['quote_ids'])
            ->delete();

        return redirect()
            ->route('quotes.index')
            ->with('success', "Successfully deleted {$deletedCount} quote(s).");
    }

    /**
     * Bulk update quote status.
     */
    public function bulkUpdateQuoteStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quote_ids' => ['required', 'array', 'min:1'],
            'quote_ids.*' => ['required', 'integer', 'exists:quotes,id'],
            'status' => ['required', 'string', 'in:Draft,Sent,Seen,Converted'],
        ]);

        $updatedCount = auth()->user()
            ->quotes()
            ->whereIn('id', $validated['quote_ids'])
            ->update(['status' => $validated['status']]);

        return redirect()
            ->route('quotes.index')
            ->with('success', "Successfully updated status for {$updatedCount} quote(s).");
    }

    /**
     * Bulk delete invoices.
     */
    public function bulkDeleteInvoices(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['required', 'integer', 'exists:invoices,id'],
        ]);

        $deletedCount = auth()->user()
            ->invoices()
            ->whereIn('id', $validated['invoice_ids'])
            ->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', "Successfully deleted {$deletedCount} invoice(s).");
    }

    /**
     * Bulk update invoice status.
     */
    public function bulkUpdateInvoiceStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['required', 'integer', 'exists:invoices,id'],
            'status' => ['required', 'string', 'in:draft,sent,paid,overdue,cancelled'],
        ]);

        $updatedCount = auth()->user()
            ->invoices()
            ->whereIn('id', $validated['invoice_ids'])
            ->update(['status' => $validated['status']]);

        return redirect()
            ->route('invoices.index')
            ->with('success', "Successfully updated status for {$updatedCount} invoice(s).");
    }

    /**
     * Bulk mark invoices as paid.
     */
    public function bulkMarkInvoicesPaid(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['required', 'integer', 'exists:invoices,id'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_method' => ['nullable', 'string', 'in:bank_transfer,cash,check,credit_card,other'],
        ]);

        $invoices = auth()->user()
            ->invoices()
            ->whereIn('id', $validated['invoice_ids'])
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->update([
                'status' => 'paid',
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'] ?? null,
            ]);
        }

        return redirect()
            ->route('invoices.index')
            ->with('success', "Successfully marked {$invoices->count()} invoice(s) as paid.");
    }

    /**
     * Bulk delete items.
     */
    public function bulkDeleteItems(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'integer', 'exists:items,id'],
        ]);

        $deletedCount = auth()->user()
            ->items()
            ->whereIn('id', $validated['item_ids'])
            ->delete();

        return redirect()
            ->route('items.index')
            ->with('success', "Successfully deleted {$deletedCount} item(s).");
    }

    /**
     * Bulk delete customers.
     */
    public function bulkDeleteCustomers(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['required', 'integer', 'exists:customers,id'],
        ]);

        // Check if any customers have associated quotes or invoices
        $customersWithQuotes = auth()->user()
            ->customers()
            ->whereIn('id', $validated['customer_ids'])
            ->whereHas('quotes')
            ->count();

        $customersWithInvoices = auth()->user()
            ->customers()
            ->whereIn('id', $validated['customer_ids'])
            ->whereHas('invoices')
            ->count();

        if ($customersWithQuotes > 0 || $customersWithInvoices > 0) {
            return redirect()
                ->route('customers.index')
                ->with('error', 'Cannot delete customers with associated quotes or invoices. Please delete all quotes and invoices first.');
        }

        $deletedCount = auth()->user()
            ->customers()
            ->whereIn('id', $validated['customer_ids'])
            ->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', "Successfully deleted {$deletedCount} customer(s).");
    }
}
