<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ItemController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = auth()->user()->items();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['name', 'unit_price', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $items = $query->paginate($request->get('per_page', 15));

        return $this->paginated($items, 'Items retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit_price' => 'required|numeric|min:0|max:999999.99',
            'sku' => 'nullable|string|max:100|unique:items,sku,NULL,id,user_id,' . auth()->id(),
            'is_active' => 'boolean',
            'cost' => 'nullable|numeric|min:0|max:999999.99',
            'stock_quantity' => 'nullable|integer|min:0|max:999999',
            'reorder_level' => 'nullable|integer|min:0|max:999999',
        ]);

        $item = auth()->user()->items()->create($validated);

        return $this->success($item, 'Item created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item): JsonResponse
    {
        if ($item->user_id !== auth()->id()) {
            return $this->error('Item not found', 404);
        }

        // Load additional statistics
        $item->loadCount(['invoiceItems', 'quoteItems']);

        $item->append([
            'total_sold_quantity',
            'total_revenue',
            'recent_usage'
        ]);

        return $this->success($item, 'Item retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item): JsonResponse
    {
        if ($item->user_id !== auth()->id()) {
            return $this->error('Item not found', 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit_price' => 'required|numeric|min:0|max:999999.99',
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('items')->where(function ($query) use ($item) {
                    return $query->where('user_id', auth()->id())
                                ->where('id', '!=', $item->id);
                })
            ],
            'is_active' => 'boolean',
            'cost' => 'nullable|numeric|min:0|max:999999.99',
            'stock_quantity' => 'nullable|integer|min:0|max:999999',
            'reorder_level' => 'nullable|integer|min:0|max:999999',
        ]);

        $item->update($validated);

        return $this->success($item, 'Item updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item): JsonResponse
    {
        if ($item->user_id !== auth()->id()) {
            return $this->error('Item not found', 404);
        }

        // Check if item is being used in any quotes or invoices
        if ($item->quoteItems()->exists() || $item->invoiceItems()->exists()) {
            return $this->error('Cannot delete item that is used in quotes or invoices', 422);
        }

        $item->delete();

        return $this->success(null, 'Item deleted successfully');
    }

    /**
     * Get item statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_items' => auth()->user()->items()->count(),
            'active_items' => auth()->user()->items()->where('is_active', true)->count(),
            'inactive_items' => auth()->user()->items()->where('is_active', false)->count(),
            'low_stock_items' => auth()->user()->items()
                ->whereColumn('stock_quantity', '<=', 'reorder_level')
                ->where('reorder_level', '>', 0)
                ->count(),
            'average_price' => auth()->user()->items()->avg('unit_price') ?? 0,
            'total_value' => auth()->user()->items()
                ->selectRaw('SUM(stock_quantity * unit_price) as total_value')
                ->value('total_value') ?? 0,
        ];

        return $this->success($stats, 'Item statistics retrieved successfully');
    }

    /**
     * Search items for autocomplete
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50);

        if (empty($query)) {
            return $this->success([], 'No search query provided');
        }

        $items = auth()->user()->items()
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'sku', 'unit_price', 'description')
            ->limit($limit)
            ->get();

        return $this->success($items, 'Items retrieved successfully');
    }
}