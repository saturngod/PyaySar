@extends('layouts.app')

@section('title', 'Create Item')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="items.index" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Item</h1>
                    <p class="text-sm text-gray-500 mt-1">Add a new item to your inventory</p>
                </div>
                <a href="{{ route('items.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
        </header>

        <div class="p-6">
            <div class="max-w-2xl mx-auto">
                <x-card>
                    <form method="POST" action="{{ route('items.store') }}">
                        @csrf

                        <div class="space-y-6">
                            <!-- Name -->
                            <x-input
                                name="name"
                                label="Item Name"
                                type="text"
                                required
                                placeholder="Enter item name"
                                value="{{ old('name') }}"
                                error="{{ $errors->first('name') }}"
                                hint="Give your item a descriptive name"
                            />

                            <!-- Description -->
                            <x-textarea
                                name="description"
                                label="Description"
                                rows="3"
                                placeholder="Enter item description (optional)"
                                value="{{ old('description') }}"
                                error="{{ $errors->first('description') }}"
                                hint="Provide additional details about this item"
                            />

                            <!-- Price and Currency -->
                            <div class="grid grid-cols-2 gap-6">
                                <x-input
                                    name="price"
                                    label="Price"
                                    type="number"
                                    required
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    value="{{ old('price') }}"
                                    error="{{ $errors->first('price') }}"
                                    hint="Set the price per unit"
                                />

                                <x-select
                                    name="currency"
                                    label="Currency *"
                                    required
                                    :options="\App\Helpers\CurrencyHelper::getAllCurrencies()"
                                    :value="old('currency', $userSettings->default_currency)"
                                    error="{{ $errors->first('currency') }}"
                                />
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('items.index') }}" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                Cancel
                            </a>
                            <x-button type="submit" variant="primary">
                                Create Item
                            </x-button>
                        </div>
                    </form>
                </x-card>
            </div>
        </div>
    </main>
</div>
@endsection
