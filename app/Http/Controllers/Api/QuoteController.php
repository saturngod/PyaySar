<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class QuoteController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = auth()->user()->quotes()->with('customer');

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->get('status'));
        }

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->forCustomer($request->get('customer_id'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->search($search);
        }

        // Date range filter
        if ($request->has('start_date')) {
            $query->whereDate('date', '>=', $request->get('start_date'));
        }
        if ($request->has('end_date')) {
            $query->whereDate('date', '<=', $request->get('end_date'));
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['date', 'total', 'quote_number', 'status', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $quotes = $query->paginate($request->get('per_page', 15));

        return $this->paginated($quotes, 'Quotes retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'title' => 'required|string|max:255',
            'po_number' => 'nullable|string|max:50',
            'date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date',
            'currency' => 'required|string|size:3|in:USD,EUR,GBP,JPY,CAD,AUD,CHF',
            'terms' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.price' => 'required|numeric|min:0|max:999999.99',
            'items.*.qty' => 'required|integer|min:1|max:9999',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        // Verify customer ownership
        $customer = auth()->user()->customers()->find($validated['customer_id']);
        if (!$customer) {
            return $this->error('Customer not found', 422);
        }

        // Create the quote
        $quote = auth()->user()->quotes()->create([
            'customer_id' => $validated['customer_id'],
            'title' => $validated['title'],
            'po_number' => $validated['po_number'],
            'date' => $validated['date'],
            'valid_until' => $validated['valid_until'] ?? now()->addDays(30),
            'currency' => $validated['currency'],
            'terms' => $validated['terms'],
            'notes' => $validated['notes'],
            'status' => 'Draft',
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'] ?? 0,
        ]);

        // Add quote items
        foreach ($validated['items'] as $itemData) {
            QuoteItem::create([
                'quote_id' => $quote->id,
                'item_id' => $itemData['item_id'],
                'price' => $itemData['price'],
                'qty' => $itemData['qty'],
            ]);
        }

        // Calculate totals
        $quote->calculateTotals();
        $quote->save();

        // Load relationships for response
        $quote->load(['customer', 'quoteItems.item']);

        return $this->success($quote, 'Quote created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote): JsonResponse
    {
        if ($quote->user_id !== auth()->id()) {
            return $this->error('Quote not found', 404);
        }

        $quote->load(['customer', 'quoteItems.item']);

        return $this->success($quote, 'Quote retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quote $quote): JsonResponse
    {
        if ($quote->user_id !== auth()->id()) {
            return $this->error('Quote not found', 404);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'title' => 'required|string|max:255',
            'po_number' => 'nullable|string|max:50',
            'date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date',
            'currency' => 'required|string|size:3|in:USD,EUR,GBP,JPY,CAD,AUD,CHF',
            'status' => 'required|in:Draft,Sent,Seen,Converted',
            'terms' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.price' => 'required|numeric|min:0|max:999999.99',
            'items.*.qty' => 'required|integer|min:1|max:9999',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        // Verify customer ownership
        $customer = auth()->user()->customers()->find($validated['customer_id']);
        if (!$customer) {
            return $this->error('Customer not found', 422);
        }

        // Update quote
        $quote->update([
            'customer_id' => $validated['customer_id'],
            'title' => $validated['title'],
            'po_number' => $validated['po_number'],
            'date' => $validated['date'],
            'valid_until' => $validated['valid_until'],
            'currency' => $validated['currency'],
            'status' => $validated['status'],
            'terms' => $validated['terms'],
            'notes' => $validated['notes'],
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'] ?? 0,
        ]);

        // Remove existing items
        $quote->quoteItems()->delete();

        // Add updated items
        foreach ($validated['items'] as $itemData) {
            QuoteItem::create([
                'quote_id' => $quote->id,
                'item_id' => $itemData['item_id'],
                'price' => $itemData['price'],
                'qty' => $itemData['qty'],
            ]);
        }

        // Recalculate totals
        $quote->calculateTotals();
        $quote->save();

        // Load relationships for response
        $quote->load(['customer', 'quoteItems.item']);

        return $this->success($quote, 'Quote updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote): JsonResponse
    {
        if ($quote->user_id !== auth()->id()) {
            return $this->error('Quote not found', 404);
        }

        $quote->delete();

        return $this->success(null, 'Quote deleted successfully');
    }

    /**
     * Update quote status
     */
    public function updateStatus(Request $request, Quote $quote): JsonResponse
    {
        if ($quote->user_id !== auth()->id()) {
            return $this->error('Quote not found', 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:Draft,Sent,Seen,Converted',
        ]);

        $quote->update(['status' => $validated['status']]);

        return $this->success($quote, 'Quote status updated successfully');
    }

    /**
     * Convert quote to invoice
     */
    public function convertToInvoice(Quote $quote): JsonResponse
    {
        if ($quote->user_id !== auth()->id()) {
            return $this->error('Quote not found', 404);
        }

        // Create invoice from quote
        $invoice = auth()->user()->invoices()->create([
            'customer_id' => $quote->customer_id,
            'title' => $quote->title,
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'currency' => $quote->currency,
            'tax_rate' => $quote->tax_rate,
            'discount_type' => $quote->discount_type,
            'discount_value' => $quote->discount_value,
            'notes' => $quote->notes,
            'status' => 'draft',
        ]);

        // Copy quote items to invoice items
        foreach ($quote->quoteItems as $quoteItem) {
            $invoice->invoiceItems()->create([
                'item_id' => $quoteItem->item_id,
                'quantity' => $quoteItem->qty,
                'unit_price' => $quoteItem->price,
            ]);
        }

        // Calculate totals
        $invoice->calculateTotals();
        $invoice->save();

        // Update quote status
        $quote->update(['status' => 'Converted']);

        $invoice->load(['customer', 'invoiceItems.item']);

        return $this->success($invoice, 'Quote converted to invoice successfully', 201);
    }

    /**
     * Get quote statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_quotes' => auth()->user()->quotes()->count(),
            'draft_quotes' => auth()->user()->quotes()->where('status', 'Draft')->count(),
            'sent_quotes' => auth()->user()->quotes()->where('status', 'Sent')->count(),
            'seen_quotes' => auth()->user()->quotes()->where('status', 'Seen')->count(),
            'converted_quotes' => auth()->user()->quotes()->where('status', 'Converted')->count(),
            'total_value' => auth()->user()->quotes()->sum('total'),
            'average_quote_value' => auth()->user()->quotes()->avg('total') ?? 0,
            'conversion_rate' => auth()->user()->quotes()->count() > 0 ?
                (auth()->user()->quotes()->where('status', 'Converted')->count() / auth()->user()->quotes()->count()) * 100 : 0,
        ];

        return $this->success($stats, 'Quote statistics retrieved successfully');
    }

    /**
     * Send quote via email
     */
    public function sendEmail(Quote $quote): JsonResponse
    {
        if ($quote->user_id !== auth()->id()) {
            return $this->error('Quote not found', 404);
        }

        try {
            $emailService = new \App\Services\EmailService();

            if ($emailService->sendQuote($quote)) {
                $quote->update(['status' => 'Sent']);
                return $this->success($quote, 'Quote sent successfully');
            } else {
                return $this->error('Failed to send quote. Please check your email configuration.', 500);
            }
        } catch (\Exception $e) {
            return $this->error('Failed to send quote: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate quote PDF
     */
    public function generatePdf(Quote $quote): JsonResponse
    {
        if ($quote->user_id !== auth()->id()) {
            return $this->error('Quote not found', 404);
        }

        try {
            $pdfService = new \App\Services\PdfService();
            $filePath = $pdfService->generateQuote($quote);

            return $this->success([
                'download_url' => asset("storage/{$filePath}"),
                'filename' => "quote-{$quote->quote_number}.pdf"
            ], 'PDF generated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to generate PDF: ' . $e->getMessage(), 500);
        }
    }
}