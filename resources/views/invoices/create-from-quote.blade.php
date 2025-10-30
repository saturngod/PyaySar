@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="invoices" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Create Invoice from Quote</h1>
                        <p class="text-gray-600 mt-1">Convert Quote #{{ $quote->quote_number }} to an invoice</p>
                    </div>
                    <a href="{{ route('quotes.show', $quote) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Quote
                    </a>
                </div>

                <!-- Quote Details -->
                <div class="bg-primary-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-primary-500 mr-3"></i>
                        <div>
                            <h3 class="text-sm font-medium text-primary-900">Converting from Quote #{{ $quote->quote_number }}</h3>
                            <p class="text-sm text-primary-700">This invoice will be pre-filled with information from the quote. You can modify the details before creating the invoice.</p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('invoices.store-from-quote', $quote) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Form -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Invoice Details -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-input
                                        name="title"
                                        label="Invoice Title *"
                                        type="text"
                                        required
                                        placeholder="Enter invoice title"
                                        value="{{ old('title', $quote->title) }}"
                                        error="{{ $errors->first('title') }}"
                                    />
                                    <x-input
                                        name="po_number"
                                        label="PO Number"
                                        type="text"
                                        placeholder="Enter PO number"
                                        value="{{ old('po_number', $quote->po_number) }}"
                                        error="{{ $errors->first('po_number') }}"
                                    />
                                    <div>
                                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                                        <select id="customer_id" name="customer_id" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('customer_id') border-red-500 @enderror">
                                            <option value="">Select a customer</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ old('customer_id', $quote->customer_id) == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('customer_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Invoice Date *</label>
                                        <input type="date" id="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('date') border-red-500 @enderror">
                                        @error('date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                        <input type="date" id="due_date" name="due_date" value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('due_date') border-red-500 @enderror">
                                        @error('due_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                        <div class="mt-1 p-2 bg-gray-50 rounded-md border border-gray-200">
                                            <span class="text-sm font-medium text-gray-900">{{ $quote->currency }}</span>
                                            <input type="hidden" name="currency" value="{{ $quote->currency }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quote Items Preview -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Items from Quote</h3>
                                <div class="space-y-3">
                                    @foreach($quote->quoteItems as $item)
                                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                            <div class="grid grid-cols-12 gap-3 items-center">
                                                <div class="col-span-6">
                                                    <p class="text-sm font-medium text-gray-900">{{ $item->item->name }}</p>
                                                </div>
                                                <div class="col-span-2">
                                                    <p class="text-sm text-gray-600">Qty: {{ $item->qty }}</p>
                                                </div>
                                                <div class="col-span-3">
                                                    <p class="text-sm text-gray-600">Price: {{ $quote->currency }} {{ number_format($item->price, 2) }}</p>
                                                </div>
                                                <div class="col-span-1">
                                                    <p class="text-sm font-medium text-gray-900">{{ $quote->currency }} {{ number_format($item->qty * $item->price, 2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-sm text-gray-500 mt-3">These items will be automatically copied to the invoice.</p>
                            </div>

                            <!-- Notes & Terms -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Notes & Terms</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                        <textarea id="notes" name="notes" rows="3"
                                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $quote->notes) }}</textarea>
                                        <p class="text-xs text-gray-500 mt-1">Pre-filled from quote. You can modify as needed.</p>
                                    </div>
                                    <div>
                                        <label for="terms" class="block text-sm font-medium text-gray-700 mb-1">Terms & Conditions</label>
                                        <textarea id="terms" name="terms" rows="3"
                                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('terms', $quote->terms) }}</textarea>
                                        <p class="text-xs text-gray-500 mt-1">Pre-filled from quote. You can modify as needed.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Quote Summary -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Quote Summary</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Quote Number:</span>
                                        <span class="font-medium">{{ $quote->quote_number }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Quote Date:</span>
                                        <span class="font-medium">{{ $quote->date->format('M j, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Customer:</span>
                                        <span class="font-medium">{{ $quote->customer->name }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Items:</span>
                                        <span class="font-medium">{{ $quote->quoteItems->count() }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Quote Total:</span>
                                        <span class="font-bold">{{ $quote->currency }} {{ number_format($quote->total, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Create Invoice</h3>
                                <div class="space-y-3">
                                    <button type="submit" class="w-full bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition">
                                        <i class="fas fa-plus mr-2"></i>Create Invoice
                                    </button>
                                    <a href="{{ route('quotes.show', $quote) }}" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition inline-block text-center">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
