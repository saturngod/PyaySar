@extends('layouts.app')

@section('title', 'Create Quote')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="quotes" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Quote</h1>
                    <p class="mt-1 text-sm text-gray-600">Create a new quote for your customer</p>
                </div>
                <a href="{{ route('quotes.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Quotes
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('quotes.store') }}" id="quoteForm">
            @csrf
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <!-- Quote Details -->
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Quote Details</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Enter the basic information for this quote.</p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-gray-700">
                                Customer <span class="text-red-500">*</span>
                            </label>
                            <select name="customer_id"
                                    id="customer_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm @error('customer_id') border-red-500 @enderror">
                                <option value="">Select a customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-input
                            name="title"
                            label="Quote Title *"
                            type="text"
                            required
                            placeholder="e.g., Website Development Quote"
                            value="{{ old('title') }}"
                            error="{{ $errors->first('title') }}"
                        />

                        <x-input
                            name="po_number"
                            label="PO Number"
                            type="text"
                            placeholder="Customer PO number"
                            value="{{ old('po_number') }}"
                            error="{{ $errors->first('po_number') }}"
                        />

                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">
                                Quote Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   name="date"
                                   id="date"
                                   value="{{ old('date', now()->format('Y-m-d')) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm @error('date') border-red-500 @enderror">
                            @error('date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700">
                                Currency <span class="text-red-500">*</span>
                            </label>
                            <select name="currency"
                                    id="currency"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm @error('currency') border-red-500 @enderror">
                                <option value="USD" {{ old('currency', 'USD') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                <option value="JPY" {{ old('currency') === 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                                <option value="CAD" {{ old('currency') === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                <option value="AUD" {{ old('currency') === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                <option value="CHF" {{ old('currency') === 'CHF' ? 'selected' : '' }}>CHF - Swiss Franc</option>
                            </select>
                            @error('currency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="terms" class="block text-sm font-medium text-gray-700">
                            Terms & Conditions
                        </label>
                        <textarea name="terms"
                                  id="terms"
                                  rows="3"
                                  placeholder="Payment terms, warranty information, etc."
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm @error('terms') border-red-500 @enderror">{{ old('terms') }}</textarea>
                        @error('terms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700">
                            Notes
                        </label>
                        <textarea name="notes"
                                  id="notes"
                                  rows="3"
                                  placeholder="Additional notes for the customer"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Quote Items -->
            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Quote Items</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Add items to this quote.</p>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-3 sm:px-6">
                        <button type="button"
                                onclick="addItem()"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Item
                        </button>
                    </div>

                    <div id="items-container">
                        <div class="px-4 pb-4 sm:px-6">
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="mt-2 text-sm">No items added yet. Click "Add Item" to start.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quote Totals -->
            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Quote Totals</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Configure discount and tax for this quote.</p>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-5 sm:px-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div></div>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium" id="subtotal-amount">{{ $currency ?? 'USD' }} <span>0.00</span></span>
                                    </div>
                                    <div>
                                        <x-input
                                            name="discount_amount"
                                            label="Discount Amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value="{{ old('discount_amount', 0) }}"
                                            error="{{ $errors->first('discount_amount') }}"
                                            onchange="calculateTotals()"
                                            prefix="{{ $currency ?? 'USD' }}"
                                        />
                                    </div>
                                    <div>
                                        <x-input
                                            name="tax_rate"
                                            label="Tax Rate (%)"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            value="{{ old('tax_rate', 0) }}"
                                            error="{{ $errors->first('tax_rate') }}"
                                            onchange="calculateTotals()"
                                            suffix="%"
                                        />
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tax Amount:</span>
                                        <span class="font-medium" id="tax-amount">{{ $currency ?? 'USD' }} <span>0.00</span></span>
                                    </div>
                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span>Total:</span>
                                            <span id="total-amount">{{ $currency ?? 'USD' }} <span>0.00</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex justify-end">
                <a href="{{ route('quotes.index') }}"
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </a>
                <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Create Quote
                </button>
            </div>
        </form>
    </div>
</div>


<script>
let itemCount = 0;
const items = @json($items);

function addItem() {
    itemCount++;
    const container = document.getElementById('items-container');

    const itemHtml = `
        <div class="item-row border-t border-gray-200 px-4 py-4 sm:px-6" id="item-${itemCount}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Item</label>
                    <select name="items[${itemCount}][item_id]"
                            required
                            onchange="updateItemDetails(${itemCount})"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm searchable-select">
                        <option value="">Select an item</option>
                        ${items.map(item =>
                            `<option value="${item.id}" data-price="${item.unit_price}" data-description="${item.description}">
                                ${item.name}
                            </option>`
                        ).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Price</label>
                    <input type="number"
                           name="items[${itemCount}][price]"
                           step="0.01"
                           min="0"
                           required
                           onchange="calculateTotals()"
                           class="mt-1 block w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-offset-0 placeholder-gray-400 hover:border-gray-400 border-gray-300 text-gray-900 focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number"
                           name="items[${itemCount}][qty]"
                           min="1"
                           required
                           onchange="calculateTotals()"
                           class="mt-1 block w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-offset-0 placeholder-gray-400 hover:border-gray-400 border-gray-300 text-gray-900 focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div class="flex items-end">
                    <button type="button"
                            onclick="removeItem(${itemCount})"
                            class="inline-flex items-center px-3 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Remove
                    </button>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600">
                <span id="item-description-${itemCount}"></span>
            </div>
        </div>
    `;

    // Remove the empty state message if this is the first item
    if (itemCount === 1) {
        container.innerHTML = '';
    }

    container.insertAdjacentHTML('beforeend', itemHtml);
}

function removeItem(id) {
    const element = document.getElementById(`item-${id}`);
    element.remove();
    calculateTotals();
}

function updateItemDetails(id) {
    const select = document.querySelector(`select[name="items[${id}][item_id]"]`);
    const descriptionElement = document.getElementById(`item-description-${id}`);
    const priceInput = document.querySelector(`input[name="items[${id}][price]"]`);

    const selectedOption = select.options[select.selectedIndex];
    const description = selectedOption.getAttribute('data-description');
    const price = selectedOption.getAttribute('data-price');

    descriptionElement.textContent = description || '';
    if (price && priceInput.value === '') {
        priceInput.value = price;
    }

    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const id = row.id.replace('item-', '');
        const price = parseFloat(document.querySelector(`input[name="items[${id}][price]"]`)?.value || 0);
        const quantity = parseFloat(document.querySelector(`input[name="items[${id}][qty]"]`)?.value || 0);

        subtotal += price * quantity;
    });

    // Get discount and tax values
    const discountAmountInput = document.getElementById('discount_amount');
    const taxRateInput = document.getElementById('tax_rate');

    const discountAmount = discountAmountInput ? parseFloat(discountAmountInput.value || 0) : 0;
    const taxRate = taxRateInput ? parseFloat(taxRateInput.value || 0) : 0;

    // Calculate discount (cannot exceed subtotal)
    const discountedSubtotal = Math.max(0, subtotal - discountAmount);

    // Calculate tax
    const taxAmount = discountedSubtotal * (taxRate / 100);

    // Calculate final total
    const total = discountedSubtotal + taxAmount;

    // Update display elements
    const subtotalDisplay = document.querySelector('#subtotal-amount span:last-child');
    const taxAmountDisplay = document.querySelector('#tax-amount span:last-child');
    const totalDisplay = document.querySelector('#total-amount span:last-child');

    if (subtotalDisplay) {
        subtotalDisplay.textContent = subtotal.toFixed(2);
    }
    if (taxAmountDisplay) {
        taxAmountDisplay.textContent = taxAmount.toFixed(2);
    }
    if (totalDisplay) {
        totalDisplay.textContent = total.toFixed(2);
    }

    // Update totals display if needed (legacy fallback)
    const totalsElement = document.getElementById('totals-display');
    if (totalsElement) {
        totalsElement.innerHTML = `
            <div class="bg-gray-50 px-4 py-3 sm:px-6">
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-gray-900">Subtotal:</span>
                    <span class="text-gray-900">$${subtotal.toFixed(2)}</span>
                </div>
            </div>
        `;
    }
}

// Initialize with one item if the form is empty
if (document.getElementById('items-container').querySelector('.text-center')) {
    addItem();
    // Initialize searchable selects after adding the first item
    setTimeout(() => {
        initSearchableSelectsForNewContent();
    }, 100);
}
</script>
    </main>
</div>
@endsection
