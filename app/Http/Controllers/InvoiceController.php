<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Quote;
use App\Services\PdfService;
use App\Services\EmailService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $invoices = auth()
            ->user()
            ->invoices()
            ->with("customer")
            ->when($request->status, function ($query, $status) {
                $query->byStatus($status);
            })
            ->when($request->customer_id, function ($query, $customerId) {
                $query->forCustomer($customerId);
            })
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->orderBy("date", "desc")
            ->paginate(15);

        $customers = auth()->user()->customers()->orderBy("name")->get();
        $statuses = ["Draft", "Sent", "Paid", "Cancel"];

        return view(
            "invoices.index",
            compact("invoices", "customers", "statuses"),
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $customers = auth()->user()->customers()->orderBy("name")->get();
        $items = auth()->user()->items()->orderBy("name")->get();

        return view("invoices.create", compact("customers", "items"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "customer_id" => "required|exists:customers,id",
            "title" => "required|string|max:255",
            "po_number" => "nullable|string|max:50",
            "date" => "required|date",
            "due_date" => "nullable|date|after_or_equal:date",
            "currency" => \App\Helpers\CurrencyHelper::getValidationRule(),
            "terms" => "nullable|string|max:2000",
            "notes" => "nullable|string|max:2000",
            "items" => "required|array|min:1",
            "items.*.item_id" => "required|exists:items,id",
            "items.*.price" => "required|numeric|min:0|max:999999.99",
            "items.*.qty" => "required|integer|min:1|max:9999",
        ]);

        // Create the invoice
        $invoice = auth()
            ->user()
            ->invoices()
            ->create([
                "customer_id" => $validated["customer_id"],
                "title" => $validated["title"],
                "po_number" => $validated["po_number"],
                "date" => $validated["date"],
                "due_date" => $validated["due_date"],
                "currency" => $validated["currency"],
                "terms" => $validated["terms"],
                "notes" => $validated["notes"],
                "status" => "Draft",
            ]);

        // Add invoice items
        foreach ($validated["items"] as $itemData) {
            InvoiceItem::create([
                "invoice_id" => $invoice->id,
                "item_id" => $itemData["item_id"],
                "price" => $itemData["price"],
                "qty" => $itemData["qty"],
            ]);
        }

        // Calculate totals
        $invoice->calculateTotals();
        $invoice->save();

        return redirect()
            ->route("invoices.show", $invoice)
            ->with("success", "Invoice created successfully.");
    }

    /**
     * Show the form for creating an invoice from a quote.
     */
    public function createFromQuote(Quote $quote): View
    {
        $this->authorize("view", $quote);

        $quote->load("quoteItems.item");
        $customers = auth()->user()->customers()->orderBy("name")->get();

        return view(
            "invoices.create-from-quote",
            compact("quote", "customers"),
        );
    }

    /**
     * Store an invoice created from a quote.
     */
    public function storeFromQuote(
        Request $request,
        Quote $quote,
    ): RedirectResponse {
        $this->authorize("view", $quote);

        $validated = $request->validate([
            "customer_id" => "required|exists:customers,id",
            "title" => "required|string|max:255",
            "po_number" => "nullable|string|max:50",
            "date" => "required|date",
            "due_date" => "nullable|date|after_or_equal:date",
            "terms" => "nullable|string|max:2000",
            "notes" => "nullable|string|max:2000",
        ]);

        // Create the invoice from quote
        $invoice = auth()
            ->user()
            ->invoices()
            ->create([
                "quote_id" => $quote->id,
                "customer_id" => $validated["customer_id"],
                "title" => $validated["title"],
                "po_number" => $validated["po_number"],
                "date" => $validated["date"],
                "due_date" => $validated["due_date"],
                "currency" => $quote->currency,
                "terms" => $validated["terms"] ?? $quote->terms,
                "notes" => $validated["notes"] ?? $quote->notes,
                "status" => "Draft",
            ]);

        // Copy quote items to invoice items
        foreach ($quote->quoteItems as $quoteItem) {
            InvoiceItem::create([
                "invoice_id" => $invoice->id,
                "item_id" => $quoteItem->item_id,
                "price" => $quoteItem->price,
                "qty" => $quoteItem->qty,
            ]);
        }

        // Calculate totals
        $invoice->calculateTotals();
        $invoice->save();

        return redirect()
            ->route("invoices.show", $invoice)
            ->with("success", "Invoice created from quote successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize("view", $invoice);

        $invoice->load(["customer", "invoiceItems.item", "quote"]);

        return view("invoices.show", compact("invoice"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice): View
    {
        $this->authorize("update", $invoice);

        $invoice->load("invoiceItems.item");
        $customers = auth()->user()->customers()->orderBy("name")->get();
        $items = auth()->user()->items()->orderBy("name")->get();

        return view("invoices.edit", compact("invoice", "customers", "items"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize("update", $invoice);

        // DEBUG: Log all incoming request data
        error_log(
            "DEBUG Invoice Update - All Request Data: " .
                json_encode($request->all()),
        );
        error_log(
            "DEBUG Invoice Update - Customer ID: " .
                json_encode($request->input("customer_id")),
        );
        error_log(
            "DEBUG Invoice Update - Status: " .
                json_encode($request->input("status")),
        );

        $validated = $request->validate([
            "customer_id" => "required|exists:customers,id",
            "title" => "required|string|max:255",
            "po_number" => "nullable|string|max:50",
            "date" => "required|date",
            "due_date" => "nullable|date|after_or_equal:date",
            "currency" => \App\Helpers\CurrencyHelper::getValidationRule(),
            "status" => "required|in:Draft,Sent,Paid,Cancel",
            "terms" => "nullable|string|max:2000",
            "notes" => "nullable|string|max:2000",
            "items" => "required|array|min:1",
            "items.*.item_id" => "required|exists:items,id",
            "items.*.price" => "required|numeric|min:0|max:999999.99",
            "items.*.qty" => "required|integer|min:1|max:9999",
        ]);

        // Use user's default currency if not provided
        $currency =
            $validated["currency"] ??
            (auth()->user()->settings->default_currency ?? "USD");

        // Update invoice
        $invoice->update([
            "customer_id" => $validated["customer_id"],
            "title" => $validated["title"],
            "po_number" => $validated["po_number"],
            "date" => $validated["date"],
            "due_date" => $validated["due_date"],
            "currency" => $currency,
            "status" => $validated["status"],
            "terms" => $validated["terms"],
            "notes" => $validated["notes"],
        ]);

        // Remove existing items
        $invoice->invoiceItems()->delete();

        // Add updated items
        foreach ($validated["items"] as $itemData) {
            InvoiceItem::create([
                "invoice_id" => $invoice->id,
                "item_id" => $itemData["item_id"],
                "price" => $itemData["price"],
                "qty" => $itemData["qty"],
            ]);
        }

        // Recalculate totals
        $invoice->calculateTotals();
        $invoice->save();

        return redirect()
            ->route("invoices.show", $invoice)
            ->with("success", "Invoice updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize("delete", $invoice);

        $invoice->delete();

        return redirect()
            ->route("invoices.index")
            ->with("success", "Invoice deleted successfully.");
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(Invoice $invoice): RedirectResponse
    {
        $this->authorize("update", $invoice);

        $invoice->update(["status" => "Sent"]);

        return redirect()
            ->route("invoices.show", $invoice)
            ->with("success", "Invoice marked as sent.");
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Invoice $invoice): RedirectResponse
    {
        $this->authorize("update", $invoice);

        $invoice->markAsPaid();

        return redirect()
            ->route("invoices.show", $invoice)
            ->with("success", "Invoice marked as paid.");
    }

    /**
     * Mark invoice as cancelled.
     */
    public function markAsCancelled(Invoice $invoice): RedirectResponse
    {
        $this->authorize("update", $invoice);

        $invoice->update(["status" => "Cancel"]);

        return redirect()
            ->route("invoices.show", $invoice)
            ->with("success", "Invoice marked as cancelled.");
    }

    /**
     * Add an item to an invoice (AJAX endpoint).
     */
    public function addItem(Request $request, Invoice $invoice)
    {
        $this->authorize("update", $invoice);

        $validated = $request->validate([
            "item_id" => "required|exists:items,id",
            "price" => "required|numeric|min:0|max:999999.99",
            "qty" => "required|integer|min:1|max:9999",
        ]);

        $invoiceItem = InvoiceItem::create([
            "invoice_id" => $invoice->id,
            "item_id" => $validated["item_id"],
            "price" => $validated["price"],
            "qty" => $validated["qty"],
        ]);

        return response()->json([
            "success" => true,
            "invoice_item" => $invoiceItem->load("item"),
            "invoice_total" => $invoice->fresh()->total,
        ]);
    }

    /**
     * Remove an item from an invoice (AJAX endpoint).
     */
    public function removeItem(InvoiceItem $invoiceItem)
    {
        $this->authorize("update", $invoiceItem->invoice);

        $invoiceId = $invoiceItem->invoice_id;
        $invoiceItem->delete();

        $invoice = Invoice::find($invoiceId);

        return response()->json([
            "success" => true,
            "invoice_total" => $invoice->total,
        ]);
    }

    /**
     * Generate PDF for the invoice.
     */
    public function generatePdf(
        Invoice $invoice,
        PdfService $pdfService,
    ): Response|RedirectResponse {
        $this->authorize("view", $invoice);

        try {
            $filePath = $pdfService->generateInvoice($invoice);
            $filename = "invoice-{$invoice->invoice_number}.pdf";

            return $pdfService->downloadPdf($filePath, $filename);
        } catch (\Exception $e) {
            return back()->with(
                "error",
                "Failed to generate PDF: " . $e->getMessage(),
            );
        }
    }

    /**
     * Download PDF for the invoice.
     */
    public function downloadPdf(
        Invoice $invoice,
        PdfService $pdfService,
    ): Response|RedirectResponse {
        $this->authorize("view", $invoice);

        try {
            $filePath = $pdfService->generateInvoice($invoice);
            $filename = "invoice-{$invoice->invoice_number}.pdf";

            return $pdfService->downloadPdf($filePath, $filename);
        } catch (\Exception $e) {
            return back()->with(
                "error",
                "Failed to generate PDF: " . $e->getMessage(),
            );
        }
    }

    /**
     * Send invoice via email.
     */
    public function sendEmail(
        Invoice $invoice,
        EmailService $emailService,
    ): RedirectResponse {
        $this->authorize("view", $invoice);

        try {
            if ($emailService->sendInvoice($invoice, true)) {
                // Update invoice status to sent
                $invoice->update(["status" => "Sent"]);

                return back()->with(
                    "success",
                    "Invoice sent successfully to " . $invoice->customer->email,
                );
            } else {
                return back()->with(
                    "error",
                    "Failed to send invoice. Please check your email configuration.",
                );
            }
        } catch (\Exception $e) {
            return back()->with(
                "error",
                "Failed to send invoice: " . $e->getMessage(),
            );
        }
    }

    /**
     * Send multiple invoices via email.
     */
    public function sendBulkEmails(
        Request $request,
        EmailService $emailService,
    ): RedirectResponse {
        $validated = $request->validate([
            "invoice_ids" => ["required", "array", "min:1"],
            "invoice_ids.*" => ["required", "integer", "exists:invoices,id"],
        ]);

        $invoices = auth()
            ->user()
            ->invoices()
            ->whereIn("id", $validated["invoice_ids"])
            ->get();

        try {
            $results = $emailService->sendBulkInvoices($invoices->toArray());

            $successCount = collect($results)
                ->where("status", "success")
                ->count();
            $totalCount = count($results);

            return back()->with(
                "success",
                "Successfully sent {$successCount} out of {$totalCount} invoices via email.",
            );
        } catch (\Exception $e) {
            return back()->with(
                "error",
                "Failed to send bulk emails: " . $e->getMessage(),
            );
        }
    }
}
