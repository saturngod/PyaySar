<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CustomerController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = auth()->user()->customers();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['name', 'email', 'company', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $customers = $query->paginate($request->get('per_page', 15));

        return $this->paginated($customers, 'Customers retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email,NULL,id,user_id,' . auth()->id(),
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        $customer = auth()->user()->customers()->create($validated);

        return $this->success($customer, 'Customer created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): JsonResponse
    {
        if ($customer->user_id !== auth()->id()) {
            return $this->error('Customer not found', 404);
        }

        // Load relationships and statistics
        $customer->loadCount(['quotes', 'invoices']);
        $customer->load(['quotes' => function ($query) {
            $query->latest()->limit(5);
        }, 'invoices' => function ($query) {
            $query->latest()->limit(5);
        }]);

        $customer->append([
            'total_invoices_amount',
            'paid_invoices_amount',
            'outstanding_amount',
            'conversion_rate'
        ]);

        return $this->success($customer, 'Customer retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        if ($customer->user_id !== auth()->id()) {
            return $this->error('Customer not found', 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers')->where(function ($query) use ($customer) {
                    return $query->where('user_id', auth()->id())
                                ->where('id', '!=', $customer->id);
                })
            ],
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        $customer->update($validated);

        return $this->success($customer, 'Customer updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        if ($customer->user_id !== auth()->id()) {
            return $this->error('Customer not found', 404);
        }

        // Check if customer has any quotes or invoices
        if ($customer->quotes()->exists() || $customer->invoices()->exists()) {
            return $this->error('Cannot delete customer that has quotes or invoices', 422);
        }

        $customer->delete();

        return $this->success(null, 'Customer deleted successfully');
    }

    /**
     * Get customer statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_customers' => auth()->user()->customers()->count(),
            'active_customers' => auth()->user()->customers()->where('is_active', true)->count(),
            'inactive_customers' => auth()->user()->customers()->where('is_active', false)->count(),
            'customers_with_invoices' => auth()->user()->customers()->has('invoices')->count(),
            'customers_with_quotes' => auth()->user()->customers()->has('quotes')->count(),
            'average_invoices_per_customer' => auth()->user()->customers()
                ->withCount('invoices')
                ->avg('invoices_count') ?? 0,
            'total_revenue' => auth()->user()->customers()
                ->selectRaw('SUM((SELECT COALESCE(SUM(total), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "paid")) as total_revenue')
                ->value('total_revenue') ?? 0,
        ];

        return $this->success($stats, 'Customer statistics retrieved successfully');
    }

    /**
     * Search customers for autocomplete
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50);

        if (empty($query)) {
            return $this->success([], 'No search query provided');
        }

        $customers = auth()->user()->customers()
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('company', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'email', 'company', 'phone')
            ->limit($limit)
            ->get();

        return $this->success($customers, 'Customers retrieved successfully');
    }

    /**
     * Get customer activity
     */
    public function activity(Customer $customer, Request $request): JsonResponse
    {
        if ($customer->user_id !== auth()->id()) {
            return $this->error('Customer not found', 404);
        }

        $limit = min($request->get('limit', 20), 100);

        $quotes = $customer->quotes()
            ->select('id', 'quote_number', 'title', 'status', 'total', 'created_at')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($quote) {
                return [
                    'type' => 'quote',
                    'id' => $quote->id,
                    'number' => $quote->quote_number,
                    'title' => $quote->title,
                    'status' => $quote->status,
                    'amount' => $quote->total,
                    'date' => $quote->created_at->toISOString(),
                ];
            });

        $invoices = $customer->invoices()
            ->select('id', 'invoice_number', 'title', 'status', 'total', 'created_at')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($invoice) {
                return [
                    'type' => 'invoice',
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'title' => $invoice->title,
                    'status' => $invoice->status,
                    'amount' => $invoice->total,
                    'date' => $invoice->created_at->toISOString(),
                ];
            });

        $activity = $quotes->concat($invoices)
            ->sortByDesc('date')
            ->values()
            ->take($limit);

        return $this->success($activity, 'Customer activity retrieved successfully');
    }
}