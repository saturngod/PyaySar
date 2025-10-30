<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class InvoiceController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = auth()->user()->invoices()->with("customer");

        // Filter by status
        if ($request->has("status")) {
            $query->byStatus($request->get("status"));
        }

        // Filter by customer
        if ($request->has("customer_id")) {
            $query->forCustomer($request->get("customer_id"));
        }

        // Search functionality
        if ($request->has("search")) {
            $search = $request->get("search");
            $query->search($search);
        }

        // Date range filter
        if ($request->has("start_date")) {
            $query->whereDate("date", ">=", $request->get("start_date"));
        }
        if ($request->has("end_date")) {
            $query->whereDate("date", "<=", $request->get("end_date"));
        }

        // Due date filter
        if ($request->has("due_start_date")) {
            $query->whereDate(
                "due_date",
                ">=",
                $request->get("due_start_date"),
            );
        }
        if ($request->has("due_end_date")) {
            $query->whereDate("due_date", "<=", $request->get("due_end_date"));
        }

        // Sort functionality
        $sortBy = $request->get("sort_by", "date");
        $sortOrder = $request->get("sort_order", "desc");

        if (
            in_array($sortBy, [
                "date",
                "due_date",
                "total",
                "invoice_number",
                "status",
                "created_at",
            ])
        ) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $invoices = $query->paginate($request->get("per_page", 15));

        return $this->paginated($invoices, "Invoices retrieved successfully");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "customer_id" => "required|exists:customers,id",
            "title" => "required|string|max:255",
            "po_number" => "nullable|string|max:50",
            "date" => "required|date",
            "due_date" => "required|date|after_or_equal:date",
            "currency" => \App\Helpers\CurrencyHelper::getValidationRule(),
            "terms" => "nullable|string|max:2000",
            "notes" => "nullable|string|max:2000",
            "items" => "required|array|min:1",
            "items.*.item_id" => "required|exists:items,id",
            "items.*.unit_price" => "required|numeric|min:0|max:999999.99",
            "items.*.quantity" => "required|integer|min:1|max:9999",
            "tax_rate" => "nullable|numeric|min:0|max:100",
            "discount_type" => "nullable|in:fixed,percentage",
            "discount_value" => "nullable|numeric|min:0|max:999999.99",
        ]);

        // Verify customer ownership
        $customer = auth()
            ->user()
            ->customers()
            ->find($validated["customer_id"]);
        if (!$customer) {
            return $this->error("Customer not found", 422);
        }

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
                "status" => "draft",
                "tax_rate" => $validated["tax_rate"] ?? 0,
                "discount_type" => $validated["discount_type"],
                "discount_value" => $validated["discount_value"] ?? 0,
            ]);

        // Add invoice items
        foreach ($validated["items"] as $itemData) {
            InvoiceItem::create([
                "invoice_id" => $invoice->id,
                "item_id" => $itemData["item_id"],
                "unit_price" => $itemData["unit_price"],
                "quantity" => $itemData["quantity"],
            ]);
        }

        // Calculate totals
        $invoice->calculateTotals();
        $invoice->save();

        // Load relationships for response
        $invoice->load(["customer", "invoiceItems.item"]);

        return $this->success($invoice, "Invoice created successfully", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            return $this->error("Invoice not found", 404);
        }

        $invoice->load(["customer", "invoiceItems.item"]);

        return $this->success($invoice, "Invoice retrieved successfully");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            return $this->error("Invoice not found", 404);
        }

        // Prevent updates to paid invoices
        if ($invoice->status === "paid") {
            return $this->error("Cannot update paid invoices", 422);
        }

        $validated = $request->validate([
            "customer_id" => "required|exists:customers,id",
            "title" => "required|string|max:255",
            "po_number" => "nullable|string|max:50",
            "date" => "required|date",
            "due_date" => "required|date|after_or_equal:date",
            "currency" =>
                "required|string|size:3|in:USD,EUR,GBP,JPY,CAD,AUD,CHF",
            "status" => "required|in:draft,sent,paid,overdue,cancelled",
            "terms" => "nullable|string|max:2000",
            "notes" => "nullable|string|max:2000",
            "items" => "required|array|min:1",
            "items.*.item_id" => "required|exists:items,id",
            "items.*.unit_price" => "required|numeric|min:0|max:999999.99",
            "items.*.quantity" => "required|integer|min:1|max:9999",
            "tax_rate" => "nullable|numeric|min:0|max:100",
            "discount_type" => "nullable|in:fixed,percentage",
            "discount_value" => "nullable|numeric|min:0|max:999999.99",
        ]);

        // Verify customer ownership
        $customer = auth()
            ->user()
            ->customers()
            ->find($validated["customer_id"]);
        if (!$customer) {
            return $this->error("Customer not found", 422);
        }

        // Update invoice
        $invoice->update([
            "customer_id" => $validated["customer_id"],
            "title" => $validated["title"],
            "po_number" => $validated["po_number"],
            "date" => $validated["date"],
            "due_date" => $validated["due_date"],
            "currency" => $validated["currency"],
            "status" => $validated["status"],
            "terms" => $validated["terms"],
            "notes" => $validated["notes"],
            "tax_rate" => $validated["tax_rate"] ?? 0,
            "discount_type" => $validated["discount_type"],
            "discount_value" => $validated["discount_value"] ?? 0,
        ]);

        // Remove existing items
        $invoice->invoiceItems()->delete();

        // Add updated items
        foreach ($validated["items"] as $itemData) {
            InvoiceItem::create([
                "invoice_id" => $invoice->id,
                "item_id" => $itemData["item_id"],
                "unit_price" => $itemData["unit_price"],
                "quantity" => $itemData["quantity"],
            ]);
        }

        // Recalculate totals
        $invoice->calculateTotals();
        $invoice->save();

        // Load relationships for response
        $invoice->load(["customer", "invoiceItems.item"]);

        return $this->success($invoice, "Invoice updated successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            return $this->error("Invoice not found", 404);
        }

        // Prevent deletion of paid invoices
        if ($invoice->status === "paid") {
            return $this->error("Cannot delete paid invoices", 422);
        }

        $invoice->delete();

        return $this->success(null, "Invoice deleted successfully");
    }

    /**
     * Update invoice status
     */
    public function updateStatus(
        Request $request,
        Invoice $invoice,
    ): JsonResponse {
        if ($invoice->user_id !== auth()->id()) {
            return $this->error("Invoice not found", 404);
        }

        $validated = $request->validate([
            "status" => "required|in:draft,sent,paid,overdue,cancelled",
        ]);

        $invoice->update(["status" => $validated["status"]]);

        return $this->success($invoice, "Invoice status updated successfully");
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice): JsonResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            return $this->error("Invoice not found", 404);
        }

        $invoice->update(["status" => "paid"]);

        return $this->success($invoice, "Invoice marked as paid successfully");
    }

    /**
     * Get invoice statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            "total_invoices" => auth()->user()->invoices()->count(),
            "draft_invoices" => auth()
                ->user()
                ->invoices()
                ->where("status", "draft")
                ->count(),
            "sent_invoices" => auth()
                ->user()
                ->invoices()
                ->where("status", "sent")
                ->count(),
            "paid_invoices" => auth()
                ->user()
                ->invoices()
                ->where("status", "paid")
                ->count(),
            "overdue_invoices" => auth()
                ->user()
                ->invoices()
                ->where("status", "overdue")
                ->count(),
            "cancelled_invoices" => auth()
                ->user()
                ->invoices()
                ->where("status", "cancelled")
                ->count(),
            "total_revenue" => auth()
                ->user()
                ->invoices()
                ->where("status", "paid")
                ->sum("total"),
            "outstanding_amount" => auth()
                ->user()
                ->invoices()
                ->whereIn("status", ["sent", "overdue"])
                ->sum("total"),
            "average_invoice_value" =>
                auth()->user()->invoices()->avg("total") ?? 0,
            "overdue_amount" => auth()
                ->user()
                ->invoices()
                ->where("status", "overdue")
                ->sum("total"),
        ];

        return $this->success(
            $stats,
            "Invoice statistics retrieved successfully",
        );
    }

    /**
     * Send invoice via email
     */
    public function sendEmail(Invoice $invoice): JsonResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            return $this->error("Invoice not found", 404);
        }

        try {
            $emailService = new \App\Services\EmailService();

            if ($emailService->sendInvoice($invoice)) {
                $invoice->update(["status" => "sent"]);
                return $this->success($invoice, "Invoice sent successfully");
            } else {
                return $this->error(
                    "Failed to send invoice. Please check your email configuration.",
                    500,
                );
            }
        } catch (\Exception $e) {
            return $this->error(
                "Failed to send invoice: " . $e->getMessage(),
                500,
            );
        }
    }

    /**
     * Generate invoice PDF
     */
    public function generatePdf(Invoice $invoice): JsonResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            return $this->error("Invoice not found", 404);
        }

        try {
            $pdfService = new \App\Services\PdfService();
            $filePath = $pdfService->generateInvoice($invoice);

            return $this->success(
                [
                    "download_url" => asset("storage/{$filePath}"),
                    "filename" => "invoice-{$invoice->invoice_number}.pdf",
                ],
                "PDF generated successfully",
            );
        } catch (\Exception $e) {
            return $this->error(
                "Failed to generate PDF: " . $e->getMessage(),
                500,
            );
        }
    }

    /**
     * Get overdue invoices
     */
    public function overdue(Request $request): JsonResponse
    {
        $invoices = auth()
            ->user()
            ->invoices()
            ->where("status", "overdue")
            ->with("customer")
            ->orderBy("due_date")
            ->paginate($request->get("per_page", 15));

        return $this->paginated(
            $invoices,
            "Overdue invoices retrieved successfully",
        );
    }

    /**
     * Get outstanding invoices
     */
    public function outstanding(Request $request): JsonResponse
    {
        $invoices = auth()
            ->user()
            ->invoices()
            ->whereIn("status", ["sent", "overdue"])
            ->with("customer")
            ->orderBy("due_date")
            ->paginate($request->get("per_page", 15));

        return $this->paginated(
            $invoices,
            "Outstanding invoices retrieved successfully",
        );
    }
}
