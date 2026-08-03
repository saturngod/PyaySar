<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceStatusRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoices) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to', 'customer_id']);

        $invoices = $this->invoices->list(Auth::user(), [
            'status' => $request->status,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'customer_id' => $request->customer_id,
        ]);

        $customers = Auth::user()->customers()->select('id', 'name', 'avatar')->get();

        return Inertia::render('invoices/index', [
            'invoices' => $invoices,
            'filters' => $filters,
            'customers' => $customers,
        ]);
    }

    public function searchItems(Request $request)
    {
        $query = $request->input('query');

        if (! $query) {
            return response()->json(['result' => []]);
        }

        $items = $this->invoices->searchItems(Auth::user(), $query);

        return response()->json(['result' => $items]);
    }

    public function create()
    {
        $customers = Auth::user()->customers()->select('id', 'name', 'email', 'address', 'avatar')->get();
        // Simple logic for next invoice number (could be improved)
        $nextId = (Invoice::max('id') ?? 0) + 1;
        $nextInvoiceNumber = 'INV-'.$nextId;

        return Inertia::render('invoices/create', [
            'customers' => $customers,
            'nextInvoiceNumber' => $nextInvoiceNumber,
            'userPreference' => Auth::user()->preference,
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $this->invoices->create(Auth::user(), $request->validated());

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $invoice->load(['items', 'customer']);
        $customers = Auth::user()->customers()->select('id', 'name', 'email', 'address', 'avatar')->get();

        return Inertia::render('invoices/edit', [
            'invoice' => $invoice,
            'customers' => $customers,
            'userPreference' => Auth::user()->preference,
        ]);
    }

    public function show(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $invoice->load(['items', 'customer']);
        $customers = Auth::user()->customers()->select('id', 'name', 'email', 'address', 'avatar')->get();

        return Inertia::render('invoices/show', [
            'invoice' => $invoice,
            'customers' => $customers,
            'userPreference' => Auth::user()->preference,
        ]);
    }

    /**
     * Return the full invoice (with items + customer) and the user's
     * preference as JSON. Used by the invoice list page to generate a PDF
     * without navigating to the detail page. Eager-loading customer and
     * preference makes the avatar_url / company_logo_url accessors fire so
     * the client-side PDF can fetch signed image URLs.
     */
    public function showJson(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $invoice->load(['items', 'customer']);

        return response()->json([
            'invoice' => $invoice,
            'userPreference' => Auth::user()->preference,
        ]);
    }

    /**
     * Stream an invoice image through the app so browser-side PDF generation
     * never needs cross-origin access to the underlying object store.
     */
    public function pdfImage(Invoice $invoice, string $image)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $invoice->loadMissing('customer');

        $path = match ($image) {
            'avatar' => $invoice->customer?->avatar,
            'logo' => Auth::user()->preference?->company_logo,
        };

        abort_unless($path && Storage::exists($path), 404);

        return Storage::response($path, null, [
            'Cache-Control' => 'private, max-age=300',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $this->invoices->update($invoice, $request->validated());

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function duplicate(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $newInvoice = $this->invoices->duplicate($invoice);

        return redirect()->route('invoices.show', $newInvoice)->with('success', 'Invoice duplicated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $this->invoices->delete($invoice);

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function updateStatus(UpdateInvoiceStatusRequest $request, Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $this->invoices->changeStatus($invoice, $request->validated()['status']);

        return back()->with('success', 'Invoice status updated.');
    }

    public function history(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        return response()->json($invoice->statusHistories()->orderBy('changed_at', 'desc')->get());
    }
}
