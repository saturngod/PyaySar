@extends('layouts.app')

@section('title', $item->name)

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="items.index" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('items.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $item->name }}</h1>
                        <p class="text-sm text-gray-500 mt-1">Item Details</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('items.edit', $item) }}"
                       class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('items.destroy', $item) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors duration-200"
                                onclick="return confirm('Are you sure you want to delete this item?')">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div class="p-6">
            <div class="max-w-4xl mx-auto">
                <div class="space-y-6">
                    <!-- Main Information -->
                        <!-- Item Details Card -->
                        <x-card>
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">{{ $item->name }}</h2>
                                    <p class="text-sm text-gray-500">Item ID: {{ $item->id }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-2">Price</h3>
                                    <p class="text-2xl font-bold text-gray-900">{{ $item->formatted_price }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-2">Currency</h3>
                                    <x-badge>{{ $item->currency }}</x-badge>
                                </div>
                            </div>

                            @if($item->description)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                                    <p class="text-gray-700">{{ $item->description }}</p>
                                </div>
                            @endif

                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Created:</span>
                                        <span class="ml-2 text-gray-900">{{ $item->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Last Updated:</span>
                                        <span class="ml-2 text-gray-900">{{ $item->updated_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </x-card>

                        <!-- Usage Statistics Card -->
                        <x-card>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Usage Statistics</h3>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="text-center p-4 bg-primary-50 rounded-lg">
                                    <div class="text-2xl font-bold text-primary-600">
                                        {{ $item->quoteItems()->count() }}
                                    </div>
                                    <div class="text-sm text-primary-800 mt-1">Used in Quotes</div>
                                </div>
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">
                                        {{ $item->invoiceItems()->count() }}
                                    </div>
                                    <div class="text-sm text-green-800 mt-1">Used in Invoices</div>
                                </div>
                            </div>
                        </x-card>
                    </div>


                </div>
            </div>
        </div>
    </main>
</div>
@endsection
