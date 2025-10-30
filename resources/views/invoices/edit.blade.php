@extends('layouts.app')

@section('title', 'Edit Invoice')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="invoices" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Invoice #{{ $invoice->invoice_number }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $invoice->title }}</p>
                </div>
                <a href="{{ route('invoices.show', $invoice) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Invoice
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoiceForm">
            @csrf
            @method('PUT')
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <!-- Invoice Details -->
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Invoice Details</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Edit the basic information for this invoice.</p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Customer *
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select name="customer_id" required class="block w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 border-gray-300 focus:border-blue-500 text-gray-900">
                                <option value="">Select a customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-input
                            name="title"
                            label="Invoice Title *"
                            type="text"
                            required
                            placeholder="e.g., Website Development Invoice"
                            value="{{ old('title', $invoice->title) }}"
                            error="{{ $errors->first('title') }}"
                        />

                        <x-input
                            name="po_number"
                            label="PO Number"
                            type="text"
                            placeholder="Customer PO number"
                            value="{{ old('po_number', $invoice->po_number) }}"
                            error="{{ $errors->first('po_number') }}"
                        />

                        <x-input
                            name="date"
                            label="Invoice Date *"
                            type="date"
                            required
                            value="{{ old('date', $invoice->date->format('Y-m-d')) }}"
                            error="{{ $errors->first('date') }}"
                        />

                        <x-input
                            name="due_date"
                            label="Due Date"
                            type="date"
                            value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}"
                            placeholder="Invoice due date"
                            error="{{ $errors->first('due_date') }}"
                        />

                        <input type="hidden" name="currency" value="{{ auth()->user()->settings->default_currency ?? 'USD' }}" />

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Status *
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select name="status" required class="block w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 border-gray-300 focus:border-blue-500 text-gray-900">
                                <option value="">Select status</option>
                                <option value="Draft" {{ old('status', $invoice->status) == 'Draft' ? 'selected' : '' }}>Draft</option>
                                <option value="Sent" {{ old('status', $invoice->status) == 'Sent' ? 'selected' : '' }}>Sent</option>
                                <option value="Paid" {{ old('status', $invoice->status) == 'Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Cancel" {{ old('status', $invoice->status) == 'Cancel' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <x-textarea
                        name="terms"
                        label="Terms & Conditions"
                        rows="3"
                        placeholder="Payment terms, warranty information, etc."
                        :value="old('terms', $invoice->terms)"
                        error="{{ $errors->first('terms') }}"
                    />

                    <x-textarea
                        name="notes"
                        label="Notes"
                        rows="3"
                        placeholder="Additional notes for the customer"
                        :value="old('notes', $invoice->notes)"
                        error="{{ $errors->first('notes') }}"
                    />
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Invoice Items</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Edit items on this invoice.</p>
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
                        @forelse($invoice->invoiceItems as $index => $invoiceItem)
                            <div class="px-4 pb-4 sm:px-6" id="item-{{ $index }}">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-5">
                                            <label for="items[{{ $index }}][item_id]" class="block text-sm font-medium text-gray-700">
                                                Item <span class="text-red-500">*</span>
                                            </label>
                                            <select name="items[{{ $index }}][item_id]"
                                                    id="items[{{ $index }}][item_id]"
                                                    onchange="updateItemDetails({{ $index }})"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm searchable-select @error('items.' . $index . '.item_id') border-red-500 @enderror">
                                                <option value="">Select an item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}"
                                                            data-description="{{ $item->description }}"
                                                            data-price="{{ $item->price }}"
                                                            {{ old('items.' . $index . '.item_id', $invoiceItem->item_id) == $item->id ? 'selected' : '' }}>
                                                        {{ $item->name }} ({{ $item->currency }} {{ number_format($item->price, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('items.' . $index . '.item_id')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                            <div class="mt-2 text-sm text-gray-600">
                                                <span id="item-description-{{ $index }}">{{ $invoiceItem->item->description ?? '' }}</span>
                                            </div>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="items[{{ $index }}][qty]" class="block text-sm font-medium text-gray-700">
                                                Quantity <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number"
                                                   name="items[{{ $index }}][qty]"
                                                   id="items[{{ $index }}][qty]"
                                                   value="{{ old('items.' . $index . '.qty', $invoiceItem->qty) }}"
                                                   min="1"
                                                   max="9999"
                                                   required
                                                   onchange="calculateTotals()"
                                                   class="mt-1 block w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-offset-0 placeholder-gray-400 hover:border-gray-400 border-gray-300 text-gray-900 focus:border-primary-500 focus:ring-primary-500 @error('items.' . $index . '.qty') border-red-500 focus:border-red-500 focus:ring-red-500 shadow-red-50 @enderror">
                                            @error('items.' . $index . '.qty')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="items[{{ $index }}][price]" class="block text-sm font-medium text-gray-700">
                                                Unit Price <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number"
                                                   name="items[{{ $index }}][price]"
                                                   id="items[{{ $index }}][price]"
                                                   value="{{ old('items.' . $index . '.price', $invoiceItem->price) }}"
                                                   step="0.01"
                                                   min="0"
                                                   max="999999.99"
                                                   required
                                                   onchange="calculateTotals()"
                                                   class="mt-1 block w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-offset-0 placeholder-gray-400 hover:border-gray-400 border-gray-300 text-gray-900 focus:border-primary-500 focus:ring-primary-500 @error('items.' . $index . '.price') border-red-500 focus:border-red-500 focus:ring-red-500 shadow-red-50 @enderror">
                                            @error('items.' . $index . '.price')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="items[{{ $index }}][total]" class="block text-sm font-medium text-gray-700">
                                                Total
                                            </label>
                                            <div class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm bg-gray-100 px-3 py-2">
                                                {{ $invoiceItem->currency }} <span id="item-total-{{ $index }}">{{ number_format($invoiceItem->qty * $invoiceItem->price, 2) }}</span>
                                            </div>
                                        </div>
                                        <div class="md:col-span-1 flex items-end">
                                            <button type="button"
                                                    onclick="removeItem({{ $index }})"
                                                    class="inline-flex items-center px-3 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 pb-4 sm:px-6">
                                <div class="text-center py-8 text-gray-500">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="mt-2 text-sm">No items added yet. Click "Add Item" to start.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Invoice Totals</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Review and adjust the calculated totals for this invoice.</p>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-5 sm:px-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div></div>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium" id="subtotal-amount">{{ $invoice->currency }} <span>0.00</span></span>
                                    </div>
                                    <div>
                                        <x-input
                                            name="discount_amount"
                                            label="Discount Amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value="{{ old('discount_amount', $invoice->discount_amount ?? 0) }}"
                                            error="{{ $errors->first('discount_amount') }}"
                                            onchange="calculateTotals()"
                                            prefix="{{ $invoice->currency }}"
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
                                            value="{{ old('tax_rate', $invoice->tax_rate ?? 0) }}"
                                            error="{{ $errors->first('tax_rate') }}"
                                            onchange="calculateTotals()"
                                            suffix="%"
                                        />
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tax Amount:</span>
                                        <span class="font-medium" id="tax-amount">{{ $invoice->currency }} <span>0.00</span></span>
                                    </div>
                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span>Total:</span>
                                            <span id="total-amount">{{ $invoice->currency }} <span>0.00</span></span>
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
                <a href="{{ route('invoices.show', $invoice) }}"
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </a>
                <button type="submit"
                        class="ml-3 bg-primary-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Update Invoice
                </button>
            </div>
        </form>
    </div>
</main>
</div>


<script>
let itemCount = {{ $invoice->invoiceItems->count() > 0 ? $invoice->invoiceItems->count() : 0 }};

function addItem() {
    const container = document.getElementById('items-container');
    const newItemId = itemCount++;

    const itemHtml = `
        <div class="px-4 pb-4 sm:px-6" id="item-${newItemId}">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5">
                        <label for="items[${newItemId}][item_id]" class="block text-sm font-medium text-gray-700">
                            Item <span class="text-red-500">*</span>
                        </label>
                        <select name="items[${newItemId}][item_id]"
                                    id="items[${newItemId}][item_id]"
                                    onchange="updateItemDetails(${newItemId})"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm searchable-select">
                            <option value="">Select an item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}"
                                        data-description="{{ $item->description }}"
                                        data-price="{{ $item->price }}">
                                    {{ $item->name }} ({{ $item->currency }} {{ number_format($item->price, 2) }})
                                </option>
                            @endforeach
                             </select>
                        <div class="mt-2 text-sm text-gray-600">
                            <span id="item-description-${newItemId}"></span>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label for="items[${newItemId}][qty]" class="block text-sm font-medium text-gray-700">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="items[${newItemId}][qty]"
                               id="items[${newItemId}][qty]"
                               value="1"
                               min="1"
                               max="9999"
                               required
                               onchange="calculateTotals()"
                               class="mt-1 block w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-offset-0 placeholder-gray-400 hover:border-gray-400 border-gray-300 text-gray-900 focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="items[${newItemId}][price]" class="block text-sm font-medium text-gray-700">
                            Unit Price <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="items[${newItemId}][price]"
                               id="items[${newItemId}][price]"
                               value="0.00"
                               step="0.01"
                               min="0"
                               max="999999.99"
                               required
                               onchange="calculateTotals()"
                               class="mt-1 block w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-offset-0 placeholder-gray-400 hover:border-gray-400 border-gray-300 text-gray-900 focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="items[${newItemId}][total]" class="block text-sm font-medium text-gray-700">
                            Total
                        </label>
                        <div class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm bg-gray-100 px-3 py-2">
                            {{ $invoice->currency ?? 'USD' }} <span id="item-total-${newItemId}">0.00</span>
                        </div>
                    </div>
                    <div class="md:col-span-1 flex items-end">
                        <button type="button"
                                onclick="removeItem(${newItemId})"
                                class="inline-flex items-center px-3 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', itemHtml);
    calculateTotals();

    // Initialize searchable selects for the newly added item
    setTimeout(() => {
        const newItemElement = document.getElementById(`item-${newItemId}`);
        if (newItemElement) {
            initSearchableSelects(newItemElement);
        }
    }, 100);
}

function removeItem(id) {
    const element = document.getElementById(`item-${id}`);
    if (element) {
        element.remove();
        calculateTotals();
    }
}

function updateItemDetails(id) {
    const select = document.querySelector(`select[name="items[${id}][item_id]"]`);
    const descriptionElement = document.getElementById(`item-description-${id}`);
    const priceInput = document.querySelector(`input[name="items[${id}][price]"]`);

    const selectedOption = select.options[select.selectedIndex];
    const description = selectedOption.getAttribute('data-description');
    const price = selectedOption.getAttribute('data-price');

    descriptionElement.textContent = description || '';
    if (price && priceInput.value === '0.00') {
        priceInput.value = price;
    }

    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;

    // Calculate subtotal from items
    document.querySelectorAll('[id^="item-"]').forEach(itemElement => {
        const idMatch = itemElement.id.match(/item-(\d+)/);
        if (idMatch) {
            const id = idMatch[1];
            const priceInput = document.querySelector(`input[name="items[${id}][price]"]`);
            const qtyInput = document.querySelector(`input[name="items[${id}][qty]"]`);
            const totalElement = document.getElementById(`item-total-${id}`);

            if (priceInput && qtyInput) {
                const price = parseFloat(priceInput.value || 0);
                const quantity = parseFloat(qtyInput.value || 0);

                const itemTotal = price * quantity;
                subtotal += itemTotal;

                if (totalElement) {
                    totalElement.textContent = itemTotal.toFixed(2);
                }
            }
        }
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
}

// Initialize totals on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set initial values for discount and tax if they exist in the invoice
    @isset($invoice->discount_amount)
        if (document.getElementById('discount_amount')) {
            document.getElementById('discount_amount').value = '{{ $invoice->discount_amount }}';
        }
    @endisset

    @isset($invoice->tax_rate)
        if (document.getElementById('tax_rate')) {
            document.getElementById('tax_rate').value = '{{ $invoice->tax_rate }}';
        }
    @endisset

    // Trigger initial calculation
    setTimeout(calculateTotals, 100);

    // Initialize existing searchable selects
    initSearchableSelectsForNewContent();
});
</script>
@endsection
