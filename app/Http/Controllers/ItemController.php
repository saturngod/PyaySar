<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class ItemController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Auth::user()->items()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('items/index', [
            'items' => $items,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('items/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request)
    {
        Auth::user()->items()->create($request->validated());

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $this->authorize('view', $item);

        return Inertia::render('items/show', [
            'item' => $item,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $this->authorize('update', $item);

        return Inertia::render('items/edit', [
            'item' => $item,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        $this->authorize('update', $item);

        $item->update($request->validated());

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);

        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }
}
