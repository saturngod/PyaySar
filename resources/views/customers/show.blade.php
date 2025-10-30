@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="customers" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
                        <p class="text-gray-600 mt-1">Customer details and activity</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('customers.edit', $customer) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-edit mr-2"></i>Edit Customer
                        </a>
                        <a href="{{ route('customers.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Customers
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Customer Information -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Basic Details</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <p class="text-sm text-gray-600">Customer Name:</p>
                                            <p class="font-medium text-gray-900">{{ $customer->name }}</p>
                                        </div>
                                        @if($customer->contact_person)
                                            <div>
                                                <p class="text-sm text-gray-600">Contact Person:</p>
                                                <p class="font-medium text-gray-900">{{ $customer->contact_person }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Contact Information</h4>
                                    <div class="space-y-2">
                                        @if($customer->contact_email)
                                            <div>
                                                <p class="text-sm text-gray-600">Email:</p>
                                                <p class="font-medium text-gray-900">
                                                    <a href="mailto:{{ $customer->contact_email }}" class="text-primary-600">{{ $customer->contact_email }}</a>
                                                </p>
                                            </div>
                                        @endif
                                        @if($customer->contact_phone)
                                            <div>
                                                <p class="text-sm text-gray-600">Phone:</p>
                                                <p class="font-medium text-gray-900">
                                                    <a href="tel:{{ $customer->contact_phone }}" class="text-primary-600">{{ $customer->contact_phone }}</a>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if($customer->address)
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Address</h4>
                                        <p class="text-gray-900 whitespace-pre-line">{{ $customer->address }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Recent Quotes -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Recent Quotes</h3>
                                <a href="{{ route('quotes.create') }}?customer_id={{ $customer->id }}" class="text-primary-600 text-sm">
                                    <i class="fas fa-plus mr-1"></i>New Quote
                                </a>
                            </div>
                            @if($customer->quotes->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quote #</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($customer->quotes as $quote)
                                                <tr>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        <a href="{{ route('quotes.show', $quote) }}" class="text-primary-600 font-medium">
                                                            {{ $quote->quote_number }}
                                                        </a>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $quote->date->format('M j, Y') }}
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $quote->currency }} {{ number_format($quote->total, 2) }}
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        @switch($quote->status)
                                                            @case('Draft')
                                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                                    Draft
                                                                </span>
                                                                @break
                                                            @case('Sent')
                                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-100 text-primary-800">
                                                                    Sent
                                                                </span>
                                                                @break
                                                            @case('Seen')
                                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                    Seen
                                                                </span>
                                                                @break
                                                            @case('Converted')
                                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                                    Converted
                                                                </span>
                                                                @break
                                                        @endswitch
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($customer->quotes->count() >= 5)
                                    <div class="mt-4 text-center">
                                        <a href="{{ route('quotes.index', ['customer_id' => $customer->id]) }}" class="text-primary-600 text-sm">
                                            View all quotes for this customer →
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-file-alt text-gray-400 text-3xl mb-3"></i>
                                    <p class="text-gray-500">No quotes found for this customer</p>
                                    <a href="{{ route('quotes.create') }}?customer_id={{ $customer->id }}" class="mt-3 inline-flex items-center text-primary-600 text-sm">
                                        <i class="fas fa-plus mr-1"></i>Create first quote
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Recent Invoices -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Recent Invoices</h3>
                                <a href="{{ route('invoices.create') }}?customer_id={{ $customer->id }}" class="text-primary-600 text-sm">
                                    <i class="fas fa-plus mr-1"></i>New Invoice
                                </a>
                            </div>
                            @if($customer->invoices->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($customer->invoices as $invoice)
                                                <tr>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-primary-600 font-medium">
                                                            {{ $invoice->invoice_number }}
                                                        </a>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $invoice->date->format('M j, Y') }}
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $invoice->due_date?->format('M j, Y') ?? '-' }}
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        @switch($invoice->status)
                                                            @case('Draft')
                                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                                    Draft
                                                                </span>
                                                                @break
                                                            @case('Sent')
                                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-100 text-primary-800">
                                                                    Sent
                                                                </span>
                                                                @break
                                                            @case('Paid')
                                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                    Paid
                                                                </span>
                                                                @break
                                                            @case('Cancel')
                                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                                    Cancelled
                                                                </span>
                                                                @break
                                                        @endswitch
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($customer->invoices->count() >= 5)
                                    <div class="mt-4 text-center">
                                        <a href="{{ route('invoices.index', ['customer_id' => $customer->id]) }}" class="text-primary-600 text-sm">
                                            View all invoices for this customer →
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-file-invoice text-gray-400 text-3xl mb-3"></i>
                                    <p class="text-gray-500">No invoices found for this customer</p>
                                    <a href="{{ route('invoices.create') }}?customer_id={{ $customer->id }}" class="mt-3 inline-flex items-center text-primary-600 text-sm">
                                        <i class="fas fa-plus mr-1"></i>Create first invoice
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Quick Stats -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Stats</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total Quotes:</span>
                                    <span class="font-bold text-lg">{{ $customer->quotes()->count() }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total Invoices:</span>
                                    <span class="font-bold text-lg">{{ $customer->invoices()->count() }}</span>
                                </div>
                                <div class="border-t pt-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Converted Quotes:</span>
                                        <span class="font-medium text-green-600">
                                            {{ $customer->quotes()->where('status', 'Converted')->count() }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-sm text-gray-600">Paid Invoices:</span>
                                        <span class="font-medium text-green-600">
                                            {{ $customer->invoices()->where('status', 'Paid')->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <a href="{{ route('quotes.create') }}?customer_id={{ $customer->id }}" class="w-full bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 inline-block text-center">
                                    <i class="fas fa-plus mr-2"></i>Create Quote
                                </a>
                                <a href="{{ route('invoices.create') }}?customer_id={{ $customer->id }}" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 inline-block text-center">
                                    <i class="fas fa-plus mr-2"></i>Create Invoice
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 inline-block text-center">
                                    <i class="fas fa-edit mr-2"></i>Edit Customer
                                </a>
                            </div>
                        </div>

                        <!-- Contact Actions -->
                        @if($customer->contact_email || $customer->contact_phone)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact</h3>
                                <div class="space-y-3">
                                    @if($customer->contact_email)
                                        <a href="mailto:{{ $customer->contact_email }}" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 inline-block text-center">
                                            <i class="fas fa-envelope mr-2"></i>Send Email
                                        </a>
                                    @endif
                                    @if($customer->contact_phone)
                                        <a href="tel:{{ $customer->contact_phone }}" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 inline-block text-center">
                                            <i class="fas fa-phone mr-2"></i>Call Customer
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
