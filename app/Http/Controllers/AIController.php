<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AIController extends Controller
{
    public function __construct(
        private OpenAIService $openAIService
    ) {}

    public function index()
    {
        return Inertia::render('ai/index');
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_history' => 'array',
        ]);

        $user = Auth::user();
        $prompt = $request->input('message');
        $conversationHistory = $request->input('conversation_history', []);

        try {
            $result = $this->openAIService->chatWithFunctions($user, $prompt, $conversationHistory);

            $functionName = $result['function'];
            $arguments = $result['arguments'];
            $assistantMessage = $result['assistant_message'];

            // Handle function calls
            if ($functionName === 'create_invoice') {
                $functionResult = $this->handleCreateInvoice($user, $arguments);
            } elseif ($functionName === 'list_invoices') {
                $functionResult = $this->handleListInvoices($user, $arguments);
            } elseif ($functionName === 'get_invoice_details') {
                $functionResult = $this->handleGetInvoiceDetails($user, $arguments);
            } else {
                // Simple chat response
                return response()->json([
                    'response' => $arguments['response'] ?? $assistantMessage['content'] ?? 'I understand. How can I help you with your invoices?',
                    'assistant_message' => $assistantMessage,
                ]);
            }

            return response()->json([
                'response' => $functionResult['message'],
                'data' => $functionResult['data'] ?? null,
                'assistant_message' => $assistantMessage,
                'function_result' => [
                    'name' => $functionName,
                    'result' => $functionResult,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process request: '.$e->getMessage(),
            ], 500);
        }
    }

    private function handleCreateInvoice($user, array $arguments): array
    {
        $customerName = $arguments['customer_name'];
        $items = $arguments['items'];
        $currency = $arguments['currency'] ?? $user->preference?->currency ?? 'USD';
        $dueDate = $arguments['due_date'] ?? null;
        $notes = $arguments['notes'] ?? null;
        $status = $arguments['status'] ?? 'draft';

        // Find or create customer
        $customer = $user->customers()->firstOrCreate(
            ['name' => $customerName],
            ['email' => '', 'address' => '']
        );

        // Calculate totals
        $subTotal = collect($items)->sum(fn ($item) => $item['qty'] * $item['price']);
        $total = $subTotal;

        // Generate invoice number
        $nextId = (Invoice::max('id') ?? 0) + 1;
        $invoiceNumber = 'INV-'.$nextId;

        $invoice = DB::transaction(function () use ($user, $customer, $invoiceNumber, $items, $dueDate, $notes, $status, $currency, $subTotal, $total) {
            $invoice = $user->invoices()->create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customer->id,
                'open_date' => now()->toDateString(),
                'due_date' => $dueDate,
                'status' => $status,
                'currency' => $currency,
                'notes' => $notes,
                'sub_total' => $subTotal,
                'total' => $total,
            ]);

            foreach ($items as $item) {
                $invoice->items()->create([
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total_price' => $item['qty'] * $item['price'],
                ]);
            }

            return $invoice;
        });

        // Format items for response
        $itemsSummary = collect($items)->map(fn ($i) => "{$i['item_name']} ({$i['qty']} x {$i['price']})")->join(', ');

        return [
            'message' => "✅ Invoice {$invoiceNumber} created for {$customerName}!\n\nItems: {$itemsSummary}\nTotal: {$total} {$currency}",
            'data' => [
                'invoice_number' => $invoiceNumber,
                'customer' => $customerName,
                'items' => $items,
                'currency' => $currency,
                'total' => $total,
                'status' => $status,
            ],
        ];
    }

    private function handleListInvoices($user, array $arguments): array
    {
        $query = $user->invoices()->with('customer');

        if (! empty($arguments['status'])) {
            $query->where('status', $arguments['status']);
        }

        if (! empty($arguments['customer_name'])) {
            $query->whereHas('customer', function ($q) use ($arguments) {
                $q->where('name', 'like', '%'.$arguments['customer_name'].'%');
            });
        }

        $limit = $arguments['limit'] ?? 10;
        $invoices = $query->orderBy('created_at', 'desc')->limit($limit)->get();

        $invoiceList = $invoices->map(fn ($inv) => [
            'invoice_number' => $inv->invoice_number,
            'customer' => $inv->customer?->name ?? 'Unknown',
            'total' => $inv->total,
            'status' => $inv->status,
            'open_date' => $inv->open_date?->format('Y-m-d'),
        ]);

        $count = $invoices->count();
        $message = $count > 0
            ? "Found {$count} invoice(s)."
            : 'No invoices found matching your criteria.';

        return [
            'message' => $message,
            'data' => $invoiceList,
        ];
    }

    private function handleGetInvoiceDetails($user, array $arguments): array
    {
        $invoiceNumber = $arguments['invoice_number'];

        $invoice = $user->invoices()
            ->with(['customer', 'items'])
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if (! $invoice) {
            return [
                'message' => "Invoice {$invoiceNumber} not found.",
                'data' => null,
            ];
        }

        return [
            'message' => "Invoice {$invoiceNumber} details:",
            'data' => [
                'invoice_number' => $invoice->invoice_number,
                'customer' => $invoice->customer?->name ?? 'Unknown',
                'status' => $invoice->status,
                'open_date' => $invoice->open_date?->format('Y-m-d'),
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'sub_total' => $invoice->sub_total,
                'total' => $invoice->total,
                'notes' => $invoice->notes,
                'items' => $invoice->items->map(fn ($item) => [
                    'name' => $item->item_name,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->total_price,
                ]),
            ],
        ];
    }
}
