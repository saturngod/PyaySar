<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceStatusHistory;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceReportService $reportService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, Invoice>
     */
    public function list(User $user, array $filters = []): array
    {
        return $user->invoices()
            ->with(['customer:id,name'])
            ->when(isset($filters['status']) && $filters['status'] !== 'all', function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('open_date', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('open_date', '<=', $filters['date_to']);
            })
            ->when(isset($filters['customer_id']) && $filters['customer_id'] !== 'all', function ($query) use ($filters) {
                $query->where('customer_id', $filters['customer_id']);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Invoice
    {
        $subTotal = collect($data['items'])->sum(fn ($item) => $item['qty'] * $item['price']);
        $total = $subTotal;

        $invoice = DB::transaction(function () use ($user, $data, $subTotal, $total) {
            $invoice = $user->invoices()->create([
                'invoice_number' => $data['invoice_number'],
                'customer_id' => $data['customer_id'],
                'open_date' => $data['open_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => $data['status'],
                'currency' => $data['currency'],
                'notes' => $data['notes'] ?? null,
                'bank_account_info' => $data['bank_account_info'] ?? null,
                'sub_total' => $subTotal,
                'total' => $total,
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total_price' => $item['qty'] * $item['price'],
                ]);
            }

            $this->reportService->recomputeForDate($user, Carbon::parse($data['open_date']));

            return $invoice;
        });

        return $invoice;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Invoice $invoice, array $data): Invoice
    {
        $subTotal = collect($data['items'])->sum(fn ($item) => $item['qty'] * $item['price']);
        $total = $subTotal;

        $oldOpenDate = $invoice->open_date->toDateString();

        DB::transaction(function () use ($invoice, $data, $subTotal, $total, $oldOpenDate, $user) {
            $oldStatus = $invoice->status;
            $newStatus = $data['status'];

            $invoice->update([
                'invoice_number' => $data['invoice_number'],
                'customer_id' => $data['customer_id'],
                'open_date' => $data['open_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => $newStatus,
                'currency' => $data['currency'],
                'notes' => $data['notes'] ?? null,
                'bank_account_info' => $data['bank_account_info'] ?? null,
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

            $invoice->items()->delete();

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total_price' => $item['qty'] * $item['price'],
                ]);
            }

            // Recompute report(s) inside the same transaction.
            $newOpenDate = $data['open_date'];
            if ($oldOpenDate !== $newOpenDate) {
                $this->reportService->recomputeForDate($user, Carbon::parse($oldOpenDate));
            }
            $this->reportService->recomputeForDate($user, Carbon::parse($newOpenDate));
        });

        return $invoice->fresh(['items', 'customer']);
    }

    public function changeStatus(Invoice $invoice, string $status): Invoice
    {
        $oldStatus = $invoice->status;

        if ($oldStatus !== $status) {
            DB::transaction(function () use ($invoice, $oldStatus, $status) {
                $invoice->update(['status' => $status]);

                InvoiceStatusHistory::create([
                    'invoice_id' => $invoice->id,
                    'from_status' => $oldStatus,
                    'to_status' => $status,
                    'changed_at' => now(),
                ]);

                $this->reportService->recomputeForDate($invoice->user, Carbon::parse($invoice->open_date));
            });
        }

        return $invoice->fresh(['statusHistories']);
    }

    public function duplicate(Invoice $invoice): Invoice
    {
        $invoice->load('items');

        $newInvoice = DB::transaction(function () use ($invoice) {
            $nextId = (Invoice::max('id') ?? 0) + 1;
            $user = $invoice->user()->first();

            $newInvoice = $user->invoices()->create([
                'invoice_number' => 'INV-'.$nextId,
                'customer_id' => $invoice->customer_id,
                'open_date' => now(),
                'due_date' => null,
                'status' => 'Draft',
                'currency' => $invoice->currency,
                'notes' => $invoice->notes,
                'bank_account_info' => $invoice->bank_account_info,
                'sub_total' => $invoice->sub_total,
                'total' => $invoice->total,
            ]);

            foreach ($invoice->items as $item) {
                $newInvoice->items()->create([
                    'item_name' => $item->item_name,
                    'description' => $item->description,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total_price' => $item->total_price,
                ]);
            }

            $this->reportService->recomputeForDate($user, Carbon::now());

            return $newInvoice;
        });

        return $newInvoice;
    }

    public function delete(Invoice $invoice): void
    {
        $user = $invoice->user;
        $openDate = Carbon::parse($invoice->open_date);

        DB::transaction(function () use ($invoice, $user, $openDate) {
            $invoice->delete();
            $this->reportService->recomputeForDate($user, $openDate);
        });
    }

    /**
     * @return array<int, array{id: int, name: string, description: ?string, price: float}>
     */
    public function searchItems(User $user, string $query): array
    {
        return $user->items()
            ->where('name', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => (float) $item->price,
            ])
            ->all();
    }
}
