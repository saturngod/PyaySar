@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="items" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Item</h1>
                        <p class="text-gray-600 mt-1">Update item information</p>
                    </div>
                    <a href="{{ route('items.show', $item) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Item
                    </a>
                </div>

                <!-- Form -->
                <form action="{{ route('items.update', $item) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Form -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-6">Item Information</h3>

                                <div class="space-y-6">
                                    <!-- Basic Information -->
                                    <x-input
                                        name="name"
                                        label="Item Name *"
                                        type="text"
                                        required
                                        placeholder="Enter item name"
                                        value="{{ old('name', $item->name) }}"
                                        error="{{ $errors->first('name') }}"
                                    />

                                    <x-textarea
                                        name="description"
                                        label="Description"
                                        rows="4"
                                        placeholder="Enter a detailed description of this item"
                                        value="{{ old('description', $item->description) }}"
                                        error="{{ $errors->first('description') }}"
                                        hint="Provide a clear description to help customers understand what this item includes."
                                    />

                                    <!-- Pricing Information -->
                                    <div class="border-t pt-6">
                                        <h4 class="text-md font-medium text-gray-900 mb-4">Pricing Information</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input
                                                name="price"
                                                label="Unit Price *"
                                                type="number"
                                                required
                                                step="0.01"
                                                min="0"
                                                max="999999.99"
                                                placeholder="0.00"
                                                value="{{ old('price', $item->price) }}"
                                                error="{{ $errors->first('price') }}"
                                                hint="Enter the price per unit"
                                            />
                                            <x-select
                                                name="currency"
                                                label="Currency *"
                                                required
                                                :options="\App\Helpers\CurrencyHelper::getAllCurrencies()"
                                                :value="old('currency', $item->currency ?? $userSettings->default_currency)"
                                                error="{{ $errors->first('currency') }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Current Usage -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Current Usage</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Used in Quotes:</span>
                                        <span class="font-bold">{{ $item->quoteItems()->count() }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Used in Invoices:</span>
                                        <span class="font-bold">{{ $item->invoiceItems()->count() }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Total Usage:</span>
                                        <span class="font-bold">{{ $item->quoteItems()->count() + $item->invoiceItems()->count() }}</span>
                                    </div>
                                </div>
                                @if($item->quoteItems()->count() > 0 || $item->invoiceItems()->count() > 0)
                                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                        <p class="text-sm text-yellow-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            This item is used in {{ $item->quoteItems()->count() + $item->invoiceItems()->count() }} quote(s)/invoice(s)
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                                <div class="space-y-3">
                                    <button type="submit" class="w-full bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition">
                                        <i class="fas fa-save mr-2"></i>Update Item
                                    </button>
                                    <a href="{{ route('items.show', $item) }}" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition inline-block text-center">
                                        Cancel
                                    </a>
                                </div>
                            </div>

                            <!-- Danger Zone -->
                            @if($item->quoteItems()->count() == 0 && $item->invoiceItems()->count() == 0)
                                <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
                                    <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
                                    <p class="text-sm text-red-700 mb-4">Once you delete an item, there is no going back. Please be certain.</p>
                                    <form action="{{ route('items.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete this item? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">
                                            <i class="fas fa-trash mr-2"></i>Delete Item
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="bg-gray-50 rounded-lg border border-gray-200 p-6">
                                    <h3 class="text-lg font-medium text-gray-700 mb-2">Cannot Delete</h3>
                                    <p class="text-sm text-gray-600">
                                        This item cannot be deleted because it is used in {{ $item->quoteItems()->count() + $item->invoiceItems()->count() }} quote(s)/invoice(s).
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function updateCurrencySymbol() {
    const currency = document.getElementById('currency').value;
    const symbolElement = document.getElementById('price-currency-symbol');

    const symbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'JPY': '¥',
        'CAD': 'C$',
        'AUD': 'A$',
        'CHF': 'CHF'
    };

    symbolElement.textContent = symbols[currency] || '$';
}

// Initialize currency symbol on page load
document.addEventListener('DOMContentLoaded', updateCurrencySymbol);
</script>
@endsection
