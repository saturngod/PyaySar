<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $customers = auth()
            ->user()
            ->customers()
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->withCount(["quotes", "invoices"])
            ->orderBy("id", "desc")
            ->simplePaginate(10);

        return view("customers.index", compact("customers"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view("customers.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "contact_person" => "nullable|string|max:255",
            "contact_phone" => "nullable|string|max:50",
            "contact_email" => "nullable|email|max:255",
            "address" => "nullable|string|max:1000",
        ]);

        $validated["user_id"] = auth()->id();

        Customer::create($validated);

        return redirect()
            ->route("customers.index")
            ->with("success", "Customer created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): View
    {
        $this->authorize("view", $customer);

        $customer->load([
            "quotes" => fn($query) => $query->latest()->limit(5),
            "invoices" => fn($query) => $query->latest()->limit(5),
        ]);

        return view("customers.show", compact("customer"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): View
    {
        $this->authorize("update", $customer);

        return view("customers.edit", compact("customer"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        Customer $customer,
    ): RedirectResponse {
        $this->authorize("update", $customer);

        $validated = $request->validate([
            "name" => "required|string|max:255",
            "contact_person" => "nullable|string|max:255",
            "contact_phone" => "nullable|string|max:50",
            "contact_email" => "nullable|email|max:255",
            "address" => "nullable|string|max:1000",
        ]);

        $customer->update($validated);

        return redirect()
            ->route("customers.index")
            ->with("success", "Customer updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize("delete", $customer);

        // Check if customer has quotes or invoices
        if ($customer->quotes()->exists() || $customer->invoices()->exists()) {
            return redirect()
                ->route("customers.index")
                ->with(
                    "error",
                    "Cannot delete customer that has quotes or invoices.",
                );
        }

        $customer->delete();

        return redirect()
            ->route("customers.index")
            ->with("success", "Customer deleted successfully.");
    }

    /**
     * Search customers for autocomplete.
     */
    public function search(Request $request)
    {
        $search = $request->get("q");

        $customers = auth()
            ->user()
            ->customers()
            ->when($search, function ($query, $search) {
                $query->search($search);
            })
            ->orderBy("name")
            ->limit(10)
            ->get(["id", "name", "contact_person", "contact_email"]);

        return response()->json($customers);
    }
}
