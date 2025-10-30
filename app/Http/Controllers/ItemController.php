<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $items = auth()
            ->user()
            ->items()
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->orderBy("id", "desc")
            ->simplePaginate(10);

        return view("items.index", compact("items"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $userSettings = auth()->user()->settings;
        return view("items.create", compact("userSettings"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "description" => "nullable|string|max:1000",
            "price" => "required|numeric|min:0|max:999999.99",
            "currency" => \App\Helpers\CurrencyHelper::getValidationRule(),
        ]);

        $validated["user_id"] = auth()->id();

        Item::create($validated);

        return redirect()
            ->route("items.index")
            ->with("success", "Item created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item): View
    {
        // Ensure user can only access their own items
        if ($item->user_id !== auth()->id()) {
            abort(403);
        }

        return view("items.show", compact("item"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item): View
    {
        // Ensure user can only access their own items
        if ($item->user_id !== auth()->id()) {
            abort(403);
        }

        $userSettings = auth()->user()->settings;
        return view("items.edit", compact("item", "userSettings"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item): RedirectResponse
    {
        // Ensure user can only update their own items
        if ($item->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            "name" => "required|string|max:255",
            "description" => "nullable|string|max:1000",
            "price" => "required|numeric|min:0|max:999999.99",
            "currency" => \App\Helpers\CurrencyHelper::getValidationRule(),
        ]);

        $item->update($validated);

        return redirect()
            ->route("items.index")
            ->with("success", "Item updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item): RedirectResponse
    {
        // Ensure user can only delete their own items
        if ($item->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if item is used in any quotes or invoices
        if ($item->quoteItems()->exists() || $item->invoiceItems()->exists()) {
            return redirect()
                ->route("items.index")
                ->with(
                    "error",
                    "Cannot delete item that is used in quotes or invoices.",
                );
        }

        $item->delete();

        return redirect()
            ->route("items.index")
            ->with("success", "Item deleted successfully.");
    }

    /**
     * Search for items (AJAX endpoint).
     */
    public function search(Request $request)
    {
        $search = $request->get("q");

        $items = auth()
            ->user()
            ->items()
            ->when($search, function ($query, $search) {
                $query->search($search);
            })
            ->orderBy("name")
            ->limit(10)
            ->get(["id", "name", "price", "currency"]);

        return response()->json($items);
    }
}
