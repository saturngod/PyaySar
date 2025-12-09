<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use App\Models\InvoiceStatusHistory;
use App\Http\Requests\UpdateInvoiceStatusRequest;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to', 'customer_id']);

        $invoices = Auth::user()->invoices()
            ->with(['customer:id,name'])
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status !== 'all') {
                    $query->where('status', $request->status);
                }
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('open_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('open_date', '<=', $request->date_to);
            })
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                if ($request->customer_id !== 'all') {
                    $query->where('customer_id', $request->customer_id);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get(); // Using get() for now, pagination can be added later if needed

        $customers = Auth::user()->customers()->select('id', 'name', 'avatar')->get();

        return Inertia::render('invoices/index', [
            'invoices' => $invoices,
            'filters' => $filters,
            'customers' => $customers,
        ]);
    }

    public function create()
    {
        $customers = Auth::user()->customers()->select('id', 'name', 'email', 'address', 'avatar')->get();
        // Simple logic for next invoice number (could be improved)
        $nextInvoiceId = (Invoice::max('id') ?? 0) + 1;

        return Inertia::render('invoices/create', [
            'customers' => $customers,
            'nextInvoiceId' => $nextInvoiceId,
            'userPreference' => Auth::user()->preference,
        ]);
    }

    public function store(\App\Http\Requests\StoreInvoiceRequest $request)
    {
        $validated = $request->validated();
        
        // Calculate totals
        $subTotal = collect($validated['items'])->sum(function ($item) {
            return $item['qty'] * $item['price'];
        });
        
        // Assuming no discount logic in request for now since it wasn't in the form explicitly yet, but model supports it.
        // We can add discount later if needed.
        $total = $subTotal; // - discount

        DB::transaction(function () use ($validated, $subTotal, $total) {
            $invoice = Auth::user()->invoices()->create([
                'customer_id' => $validated['customer_id'],
                'open_date' => $validated['open_date'],
                'due_date' => $validated['due_date'] ?? null,
                'status' => $validated['status'],
                'currency' => $validated['currency'],
                'notes' => $validated['notes'] ?? null,
                'bank_account_info' => $validated['bank_account_info'] ?? null,
                'sub_total' => $subTotal,
                'total' => $total,
            ]);

            // Track initial status history (optional, or assuming 'Draft' means no history yet? Usually good to track)
            // But usually history tracks *changes*. Let's leave initial creation alone for now unless requested.

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total_price' => $item['qty'] * $item['price'],
                ]);
            }
        });

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

    public function update(\App\Http\Requests\StoreInvoiceRequest $request, Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validated();

        $subTotal = collect($validated['items'])->sum(function ($item) {
            return $item['qty'] * $item['price'];
        });
        $total = $subTotal;

        DB::transaction(function () use ($invoice, $validated, $subTotal, $total) {
            $oldStatus = $invoice->status;
            $newStatus = $validated['status'];

            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'open_date' => $validated['open_date'],
                'due_date' => $validated['due_date'] ?? null,
                'status' => $newStatus,
                'currency' => $validated['currency'],
                'notes' => $validated['notes'] ?? null,
                'bank_account_info' => $validated['bank_account_info'] ?? null,
                'sub_total' => $subTotal,
                'total' => $total,
            ]);

            if ($oldStatus !== $newStatus) {
                InvoiceStatusHistory::create([
                    'invoice_id' => $invoice->id,
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'changed_at' => now(),
                ]);
            }

            // Sync items: Delete all and recreate (easiest strategy for now)
            // Or careful sync. Re-creation is safer for data integrity if IDs are not tracked in frontend
            $invoice->items()->delete();

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total_price' => $item['qty'] * $item['price'],
                ]);
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }
        
        $invoice->delete();
        
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function updateStatus(UpdateInvoiceStatusRequest $request, Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validated();
        $oldStatus = $invoice->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus) {
            DB::transaction(function () use ($invoice, $oldStatus, $newStatus) {
                $invoice->update(['status' => $newStatus]);

                InvoiceStatusHistory::create([
                    'invoice_id' => $invoice->id,
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'changed_at' => now(),
                ]);
            });
        }

        return back()->with('success', 'Invoice status updated.');
    }

    public function history(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $history = InvoiceStatusHistory::where('invoice_id', $invoice->id)
            ->orderBy('changed_at', 'desc')
            ->get();

        return response()->json($history);
    }
}
