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
                        <h1 class="text-2xl font-bold text-gray-900">Invoice #{{ $invoice->invoice_number }}</h1>
                        <p class="text-gray-600 mt-1">{{ $invoice->title }}</p>
                    </div>
                    <div class="flex space-x-3">
                        @if($invoice->status !== 'Paid')
                            <form action="{{ route('invoices.mark-paid', $invoice) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-check mr-2"></i>Mark as Paid
                                </button>
                            </form>
                        @endif
                        @if($invoice->status === 'Draft')
                            <form action="{{ route('invoices.mark-sent', $invoice) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition">
                                    <i class="fas fa-envelope mr-2"></i>Mark as Sent
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('invoices.edit', $invoice) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        <div class="relative">
                            <button type="button" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition" onclick="toggleDropdown()">
                                <i class="fas fa-ellipsis-v mr-2"></i>More
                            </button>
                            <div id="dropdown" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                <div class="py-1">
                                    <a href="{{ route('invoices.pdf', $invoice) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-download mr-2"></i>Download PDF
                                    </a>
                                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this invoice?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            <i class="fas fa-trash mr-2"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('invoices.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                            <i class="fas fa-arrow-left mr-2"></i>Back
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Invoice Details -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Invoice Details</h3>
                                    <p class="text-sm text-gray-500 mt-1">Invoice #{{ $invoice->invoice_number }}</p>
                                </div>
                                <div class="text-right">
                                    @switch($invoice->status)
                                        @case('Draft')
                                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Draft
                                            </span>
                                            @break
                                        @case('Sent')
                                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-primary-100 text-primary-800">
                                                Sent
                                            </span>
                                            @break
                                        @case('Paid')
                                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Paid
                                            </span>
                                            @break
                                        @case('Cancel')
                                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                            @break
                                    @endswitch
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Customer:</p>
                                    <p class="font-medium">{{ $invoice->customer->name }}</p>
                                    @if($invoice->customer->email)
                                        <p class="text-sm text-gray-500">{{ $invoice->customer->email }}</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Invoice Date:</p>
                                    <p class="font-medium">{{ $invoice->date->format('F j, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Due Date:</p>
                                    <p class="font-medium">{{ $invoice->due_date?->format('F j, Y') ?? 'Not set' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Currency:</p>
                                    <p class="font-medium">{{ $invoice->currency }}</p>
                                </div>
                                @if($invoice->po_number)
                                    <div>
                                        <p class="text-sm text-gray-600">PO Number:</p>
                                        <p class="font-medium">{{ $invoice->po_number }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Items</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($invoice->invoiceItems as $item)
                                            <tr>
                                                <td class="px-4 py-4 text-sm text-gray-900">
                                                    {{ $item->item->name }}
                                                </td>
                                                <td class="px-4 py-4 text-sm text-gray-900 text-center">
                                                    {{ $item->qty }}
                                                </td>
                                                <td class="px-4 py-4 text-sm text-gray-900 text-right">
                                                    {{ $invoice->currency }} {{ number_format($item->price, 2) }}
                                                </td>
                                                <td class="px-4 py-4 text-sm font-medium text-gray-900 text-right">
                                                    {{ $invoice->currency }} {{ number_format($item->qty * $item->price, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Notes & Terms -->
                        @if($invoice->notes || $invoice->terms)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Notes & Terms</h3>
                                @if($invoice->notes)
                                    <div class="mb-4">
                                        <p class="text-sm font-medium text-gray-700 mb-1">Notes:</p>
                                        <p class="text-sm text-gray-600">{{ $invoice->notes }}</p>
                                    </div>
                                @endif
                                @if($invoice->terms)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Terms & Conditions:</p>
                                        <p class="text-sm text-gray-600">{{ $invoice->terms }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Summary -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Summary</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium">{{ $invoice->currency }} {{ number_format($invoice->sub_total, 2) }}</span>
                                </div>
                                @if($invoice->discount_amount > 0)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Discount:</span>
                                        <span class="font-medium text-red-600">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</span>
                                    </div>
                                @endif
                                @if($invoice->tax_amount > 0)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Tax:</span>
                                        <span class="font-medium">{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="border-t pt-3">
                                    <div class="flex justify-between">
                                        <span class="text-base font-medium text-gray-900">Total:</span>
                                        <span class="text-lg font-bold text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                            <div class="space-y-3">
                                <a href="{{ route('invoices.pdf', $invoice) }}" class="w-full bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition inline-block text-center">
                                    <i class="fas fa-download mr-2"></i>Download PDF
                                </a>
                                @if($invoice->status !== 'Paid')
                                    <form action="{{ route('invoices.mark-paid', $invoice) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                                            <i class="fas fa-check mr-2"></i>Mark as Paid
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('invoices.edit', $invoice) }}" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition inline-block text-center">
                                    <i class="fas fa-edit mr-2"></i>Edit Invoice
                                </a>
                            </div>
                        </div>

                        <!-- Quote Reference -->
                        @if($invoice->quote)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Quote Reference</h3>
                                <p class="text-sm text-gray-600">This invoice was created from:</p>
                                <a href="{{ route('quotes.show', $invoice->quote) }}" class="text-primary-600 hover:text-primary-800 font-medium">
                                    Quote #{{ $invoice->quote->quote_number }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function toggleDropdown() {
    const dropdown = document.getElementById('dropdown');
    dropdown.classList.toggle('hidden');

    // Close dropdown when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
            document.removeEventListener('click', closeDropdown);
        }
    });
}
</script>
@endsection
