@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="dashboard" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200/50 px-6 py-6 shadow-soft">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 text-gray-900">
                        Dashboard
                    </h1>
                    <p class="text-gray-600 mt-2">Welcome back, <span class="font-semibold text-primary-600">{{ auth()->user()->name }}</span>! 👋</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ now()->format('l, F j, Y') }}</p>
                        <p class="text-xs text-gray-500">{{ now()->format('g:i A') }}</p>
                    </div>
                    <div class="relative">
                        <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </button>
                        <div class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-6 space-y-8">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Quotes Card -->
                <x-card class="border-0 shadow-soft hover:shadow-medium transition-all duration-300 group">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <div class="w-14 h-14 bg-primary-600 rounded-xl flex items-center justify-center shadow-soft group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate mb-1">
                                    Total Quotes
                                </dt>
                                <dd class="text-3xl font-bold text-gray-900">
                                    {{ $stats['total_quotes'] }}
                                </dd>
                                <p class="text-xs text-green-600 mt-1 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    12% from last month
                                </p>
                            </dl>
                        </div>
                    </div>
                </x-card>

                <!-- Total Invoices Card -->
                <x-card class="border-0 shadow-soft hover:shadow-medium transition-all duration-300 group">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 bg-green-600 rounded-xl flex items-center justify-center shadow-soft group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-5V8a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h1m0-8V6a2 2 0 012-2h6a2 2 0 012 2v2m0 0h8a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2v-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate mb-1">
                                    Total Invoices
                                </dt>
                                <dd class="text-3xl font-bold text-gray-900">
                                    {{ $stats['total_invoices'] }}
                                </dd>
                                <p class="text-xs text-green-600 mt-1 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    8% from last month
                                </p>
                            </dl>
                        </div>
                    </div>
                </x-card>

                <!-- Customers Card -->
                <x-card class="border-0 shadow-soft hover:shadow-medium transition-all duration-300 group">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 bg-purple-600 rounded-xl flex items-center justify-center shadow-soft group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate mb-1">
                                    Customers
                                </dt>
                                <dd class="text-3xl font-bold text-gray-900">
                                    {{ $stats['total_customers'] }}
                                </dd>
                                <p class="text-xs text-green-600 mt-1 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    15% from last month
                                </p>
                            </dl>
                        </div>
                    </div>
                </x-card>

                <!-- Items Card -->
                <x-card class="border-0 shadow-soft hover:shadow-medium transition-all duration-300 group">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 bg-yellow-600 rounded-xl flex items-center justify-center shadow-soft group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate mb-1">
                                    Items
                                </dt>
                                <dd class="text-3xl font-bold text-gray-900">
                                    {{ $stats['total_items'] }}
                                </dd>
                                <p class="text-xs text-red-600 mt-1 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    3% from last month
                                </p>
                            </dl>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Quick Actions -->
            <x-card class="border-0 shadow-soft">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Quick Actions</h3>
                        <span class="text-sm text-gray-500">Get started quickly</span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('items.create') }}"
                           class="group flex flex-col items-center p-6 border-2 border-dashed border-gray-300/50 rounded-xl hover:border-primary-400 hover:bg-primary-50/30 hover:shadow-soft transition-all duration-300 ">
                            <div class="w-14 h-14 bg-primary-100 group-hover:bg-primary-200 rounded-xl mx-auto mb-4 flex items-center justify-center transition-all duration-300 group-hover:scale-110">
                                <svg class="w-7 h-7 text-primary-600 group-hover:text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 group-hover:text-primary-700">New Item</span>
                            <span class="text-xs text-gray-500 mt-1">Add product/service</span>
                        </a>

                        <a href="{{ route('customers.create') }}"
                           class="group flex flex-col items-center p-6 border-2 border-dashed border-gray-300/50 rounded-xl hover:border-purple-400 hover:bg-purple-50/30 hover:shadow-soft transition-all duration-300 ">
                            <div class="w-14 h-14 bg-purple-100 group-hover:bg-purple-200 rounded-xl mx-auto mb-4 flex items-center justify-center transition-all duration-300 group-hover:scale-110">
                                <svg class="w-7 h-7 text-purple-600 group-hover:text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 group-hover:text-purple-700">New Customer</span>
                            <span class="text-xs text-gray-500 mt-1">Add client</span>
                        </a>

                        <a href="{{ route('quotes.create') }}"
                           class="group flex flex-col items-center p-6 border-2 border-dashed border-gray-300/50 rounded-xl hover:border-yellow-400 hover:bg-yellow-50/30 hover:shadow-soft transition-all duration-300 ">
                            <div class="w-14 h-14 bg-yellow-100 group-hover:bg-yellow-200 rounded-xl mx-auto mb-4 flex items-center justify-center transition-all duration-300 group-hover:scale-110">
                                <svg class="w-7 h-7 text-yellow-600 group-hover:text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 group-hover:text-yellow-700">New Quote</span>
                            <span class="text-xs text-gray-500 mt-1">Create estimate</span>
                        </a>

                        <a href="{{ route('invoices.create') }}"
                           class="group flex flex-col items-center p-6 border-2 border-dashed border-gray-300/50 rounded-xl hover:border-green-400 hover:bg-green-50/30 hover:shadow-soft transition-all duration-300 ">
                            <div class="w-14 h-14 bg-green-100 group-hover:bg-green-200 rounded-xl mx-auto mb-4 flex items-center justify-center transition-all duration-300 group-hover:scale-110">
                                <svg class="w-7 h-7 text-green-600 group-hover:text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-5V8a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h1m0-8V6a2 2 0 012-2h6a2 2 0 012 2v2m0 0h8a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2v-2z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 group-hover:text-green-700">New Invoice</span>
                            <span class="text-xs text-gray-500 mt-1">Bill client</span>
                        </a>
                    </div>
                </div>
            </x-card>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Quotes -->
                <x-card class="border-0 shadow-soft hover:shadow-medium transition-all duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Recent Quotes</h3>
                                <p class="text-sm text-gray-500 mt-1">Latest estimates created</p>
                            </div>
                            <a href="{{ route('quotes.index') }}" class="inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors duration-200">
                                View All
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                        @if($recentQuotes->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentQuotes as $quote)
                                    <div class="flex items-center justify-between p-4 bg-primary-50/50 rounded-xl hover:bg-primary-50 hover:shadow-soft transition-all duration-200 group">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-200">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900 group-hover:text-primary-700 transition-colors duration-200">{{ $quote->title }}</div>
                                                    <div class="text-sm text-gray-500">{{ $quote->customer->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-bold text-gray-900">{{ $quote->total_with_currency }}</div>
                                            <div class="text-xs text-gray-500">{{ $quote->date->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-16 h-16 bg-gray-200 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">No quotes yet</h3>
                                <p class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">Create your first quote to get started with estimating projects</p>
                                <x-button href="{{ route('quotes.create') }}" variant="primary" size="sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create Quote
                                </x-button>
                            </div>
                        @endif
                    </div>
                </x-card>

                <!-- Recent Invoices -->
                <x-card>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Invoices</h3>
                            <a href="{{ route('invoices.index') }}" class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                                View All
                            </a>
                        </div>
                        @if($recentInvoices->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentInvoices as $invoice)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-5V8a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h1m0-8V6a2 2 0 012-2h6a2 2 0 012 2v2m0 0h8a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2v-2z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $invoice->title }}</div>
                                                    <div class="text-sm text-gray-500">{{ $invoice->customer->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-semibold text-gray-900">{{ $invoice->total_with_currency }}</div>
                                            <div class="text-xs text-gray-500">{{ $invoice->date->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg mx-auto mb-3 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-5V8a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h1m0-8V6a2 2 0 012-2h6a2 2 0 012 2v2m0 0h8a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2v-2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 mb-1">No invoices yet</h3>
                                <p class="text-sm text-gray-500 mb-4">Create your first invoice to get started</p>
                                <x-button href="{{ route('invoices.create') }}" size="sm">
                                    Create Invoice
                                </x-button>
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>
    </main>
</div>
@endsection
