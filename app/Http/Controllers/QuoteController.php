<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\PdfService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuoteController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $quotes = auth()
            ->user()
            ->quotes()
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
        $statuses = ["Draft", "Sent", "Seen"];

        return view("quotes.index", compact("quotes", "customers", "statuses"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $customers = auth()->user()->customers()->orderBy("name")->get();
        $items = auth()->user()->items()->orderBy("name")->get();

        return view("quotes.create", compact("customers", "items"));
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
            "currency" => \App\Helpers\CurrencyHelper::getValidationRule(),
            "terms" => "nullable|string|max:2000",
            "notes" => "nullable|string|max:2000",
            "items" => "required|array|min:1",
            "items.*.item_id" => "required|exists:items,id",
            "items.*.price" => "required|numeric|min:0|max:999999.99",
            "items.*.qty" => "required|integer|min:1|max:9999",
        ]);

        // Create the quote
        $quote = auth()
            ->user()
            ->quotes()
            ->create([
                "customer_id" => $validated["customer_id"],
                "title" => $validated["title"],
                "po_number" => $validated["po_number"],
                "date" => $validated["date"],
                "currency" => $validated["currency"],
                "terms" => $validated["terms"],
                "notes" => $validated["notes"],
                "status" => "Draft",
            ]);

        // Add quote items
        foreach ($validated["items"] as $itemData) {
            QuoteItem::create([
                "quote_id" => $quote->id,
                "item_id" => $itemData["item_id"],
                "price" => $itemData["price"],
                "qty" => $itemData["qty"],
            ]);
        }

        // Calculate totals
        $quote->calculateTotals();
        $quote->save();

        return redirect()
            ->route("quotes.show", $quote)
            ->with("success", "Quote created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote): View
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403, "Unauthorized");
        }

        $quote->load(["customer", "quoteItems.item"]);

        return view("quotes.show", compact("quote"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quote $quote): View
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403, "Unauthorized");
        }

        $quote->load("quoteItems.item");
        $customers = auth()->user()->customers()->orderBy("name")->get();
        $items = auth()->user()->items()->orderBy("name")->get();

        return view("quotes.edit", compact("quote", "customers", "items"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quote $quote): RedirectResponse
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403, "Unauthorized");
        }

        $validated = $request->validate([
            "customer_id" => "required|exists:customers,id",
            "title" => "required|string|max:255",
            "po_number" => "nullable|string|max:50",
            "date" => "required|date",
            "currency" => \App\Helpers\CurrencyHelper::getValidationRule(),
            "status" => "required|in:Draft,Sent,Seen",
            "terms" => "nullable|string|max:2000",
            "notes" => "nullable|string|max:2000",
            "items" => "required|array|min:1",
            "items.*.item_id" => "required|exists:items,id",
            "items.*.price" => "required|numeric|min:0|max:999999.99",
            "items.*.qty" => "required|integer|min:1|max:9999",
        ]);

        // Update quote
        $quote->update([
            "customer_id" => $validated["customer_id"],
            "title" => $validated["title"],
            "po_number" => $validated["po_number"],
            "date" => $validated["date"],
            "currency" => $validated["currency"],
            "status" => $validated["status"],
            "terms" => $validated["terms"],
            "notes" => $validated["notes"],
        ]);

        // Remove existing items
        $quote->quoteItems()->delete();

        // Add updated items
        foreach ($validated["items"] as $itemData) {
            QuoteItem::create([
                "quote_id" => $quote->id,
                "item_id" => $itemData["item_id"],
                "price" => $itemData["price"],
                "qty" => $itemData["qty"],
            ]);
        }

        // Recalculate totals
        $quote->calculateTotals();
        $quote->save();

        return redirect()
            ->route("quotes.show", $quote)
            ->with("success", "Quote updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403, "Unauthorized");
        }

        $quote->delete();

        return redirect()
            ->route("quotes.index")
            ->with("success", "Quote deleted successfully.");
    }

    /**
     * Mark quote as sent.
     */
    public function markAsSent(Quote $quote): RedirectResponse
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403, "Unauthorized");
        }

        $quote->update(["status" => "Sent"]);

        return redirect()
            ->route("quotes.show", $quote)
            ->with("success", "Quote marked as sent.");
    }

    /**
     * Mark quote as seen.
     */
    public function markAsSeen(Quote $quote): RedirectResponse
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403, "Unauthorized");
        }

        $quote->update(["status" => "Seen"]);

        return redirect()
            ->route("quotes.show", $quote)
            ->with("success", "Quote marked as seen.");
    }

    /**
     * Add an item to a quote (AJAX endpoint).
     */
    public function addItem(Request $request, Quote $quote)
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403, "Unauthorized");
        }

        $validated = $request->validate([
            "item_id" => "required|exists:items,id",
            "price" => "required|numeric|min:0|max:999999.99",
            "qty" => "required|integer|min:1|max:9999",
        ]);

        $quoteItem = QuoteItem::create([
            "quote_id" => $quote->id,
            "item_id" => $validated["item_id"],
            "price" => $validated["price"],
            "qty" => $validated["qty"],
        ]);

        return response()->json([
            "success" => true,
            "quote_item" => $quoteItem->load("item"),
            "quote_total" => $quote->fresh()->total,
        ]);
    }

    /**
     * Remove an item from a quote (AJAX endpoint).
     */
    public function removeItem(QuoteItem $quoteItem)
    {
        if ($quoteItem->quote->user_id !== auth()->id()) {
            abort(403, "Unauthorized");
        }

        $quoteId = $quoteItem->quote_id;
        $quoteItem->delete();

        $quote = Quote::find($quoteId);

        return response()->json([
            "success" => true,
            "quote_total" => $quote->total,
        ]);
    }

    /**
     * Convert quote to invoice.
     */
    public function convertToInvoice(Quote $quote): RedirectResponse
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403);
        }

        // Create invoice from quote
        $invoice = auth()
            ->user()
            ->invoices()
            ->create([
                "customer_id" => $quote->customer_id,
                "title" => $quote->title,
                "date" => now()->format("Y-m-d"),
                "due_date" => now()->addDays(30)->format("Y-m-d"),
                "currency" => $quote->currency,
                "tax_rate" => $quote->tax_rate,
                "discount_type" => $quote->discount_type,
                "discount_value" => $quote->discount_value,
                "notes" => $quote->notes,
                "status" => "draft",
                "sub_total" => $quote->sub_total ?? 0,
                "discount_amount" => $quote->discount_amount ?? 0,
                "total" => $quote->total ?? 0,
            ]);

        // Copy quote items to invoice items
        foreach ($quote->quoteItems as $quoteItem) {
            $invoice->invoiceItems()->create([
                "item_id" => $quoteItem->item_id,
                "price" => $quoteItem->price,
                "qty" => $quoteItem->qty,
            ]);
        }

        // Calculate totals
        $invoice->calculateTotals();
        $invoice->save();

        // Update quote status to show it was converted
        $quote->update(["status" => "Converted"]);

        return redirect()
            ->route("invoices.show", $invoice)
            ->with("success", "Quote converted to invoice successfully.");
    }

    /**
     * Generate PDF for the quote.
     */
    public function generatePdf(
        Quote $quote,
        PdfService $pdfService,
    ): Response|RedirectResponse {
        if ($quote->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $filePath = $pdfService->generateQuote($quote);
            $filename = "quote-{$quote->quote_number}.pdf";

            return $pdfService->downloadPdf($filePath, $filename);
        } catch (\Exception $e) {
            return back()->with(
                "error",
                "Failed to generate PDF: " . $e->getMessage(),
            );
        }
    }

    /**
     * Download PDF for the quote.
     */
    public function downloadPdf(
        Quote $quote,
        PdfService $pdfService,
    ): Response|RedirectResponse {
        if ($quote->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $filePath = $pdfService->generateQuote($quote);
            $filename = "quote-{$quote->quote_number}.pdf";

            return $pdfService->downloadPdf($filePath, $filename);
        } catch (\Exception $e) {
            return back()->with(
                "error",
                "Failed to generate PDF: " . $e->getMessage(),
            );
        }
    }

    /**
     * Send quote via email.
     */
    public function sendEmail(
        Quote $quote,
        EmailService $emailService,
    ): RedirectResponse {
        if ($quote->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            if ($emailService->sendQuote($quote, true)) {
                // Update quote status to sent
                $quote->update(["status" => "Sent"]);

                return back()->with(
                    "success",
                    "Quote sent successfully to " . $quote->customer->email,
                );
            } else {
                return back()->with(
                    "error",
                    "Failed to send quote. Please check your email configuration.",
                );
            }
        } catch (\Exception $e) {
            return back()->with(
                "error",
                "Failed to send quote: " . $e->getMessage(),
            );
        }
    }

    /**
     * Send multiple quotes via email.
     */
    public function sendBulkEmails(
        Request $request,
        EmailService $emailService,
    ): RedirectResponse {
        $validated = $request->validate([
            "quote_ids" => ["required", "array", "min:1"],
            "quote_ids.*" => ["required", "integer", "exists:quotes,id"],
        ]);

        $quotes = auth()
            ->user()
            ->quotes()
            ->whereIn("id", $validated["quote_ids"])
            ->get();

        try {
            $results = $emailService->sendBulkQuotes($quotes->toArray());

            $successCount = collect($results)
                ->where("status", "success")
                ->count();
            $totalCount = count($results);

            return back()->with(
                "success",
                "Successfully sent {$successCount} out of {$totalCount} quotes via email.",
            );
        } catch (\Exception $e) {
            return back()->with(
                "error",
                "Failed to send bulk emails: " . $e->getMessage(),
            );
        }
    }
}
