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
                        <h1 class="text-2xl font-bold text-gray-900">Edit Customer</h1>
                        <p class="text-gray-600 mt-1">Update customer information</p>
                    </div>
                    <a href="{{ route('customers.show', $customer) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Customer
                    </a>
                </div>

                <!-- Form -->
                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Form -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-6">Customer Information</h3>

                                <div class="space-y-6">
                                    <!-- Basic Information -->
                                    <x-input
                                        name="name"
                                        label="Customer Name *"
                                        type="text"
                                        required
                                        placeholder="Enter customer company name"
                                        value="{{ old('name', $customer->name) }}"
                                        error="{{ $errors->first('name') }}"
                                    />

                                    <!-- Contact Information -->
                                    <div class="border-t pt-6">
                                        <h4 class="text-md font-medium text-gray-900 mb-4">Contact Information</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input
                                                name="contact_person"
                                                label="Contact Person"
                                                type="text"
                                                placeholder="Enter contact person name"
                                                value="{{ old('contact_person', $customer->contact_person) }}"
                                                error="{{ $errors->first('contact_person') }}"
                                            />
                                            <x-input
                                                name="contact_phone"
                                                label="Phone Number"
                                                type="tel"
                                                placeholder="Enter phone number"
                                                value="{{ old('contact_phone', $customer->contact_phone) }}"
                                                error="{{ $errors->first('contact_phone') }}"
                                            />
                                            <div class="md:col-span-2">
                                                <x-input
                                                    name="contact_email"
                                                    label="Email Address"
                                                    type="email"
                                                    placeholder="Enter email address"
                                                    value="{{ old('contact_email', $customer->contact_email) }}"
                                                    error="{{ $errors->first('contact_email') }}"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Address Information -->
                                    <div class="border-t pt-6">
                                        <h4 class="text-md font-medium text-gray-900 mb-4">Address Information</h4>
                                        <x-textarea
                                            name="address"
                                            label="Full Address"
                                            rows="3"
                                            placeholder="Enter complete address including street, city, state/province, postal code, and country"
                                            value="{{ old('address', $customer->address) }}"
                                            error="{{ $errors->first('address') }}"
                                            hint="Include street address, city, state/province, postal code, and country for complete address information."
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Current Status -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Current Status</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Total Quotes:</span>
                                        <span class="font-bold">{{ $customer->quotes()->count() }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Total Invoices:</span>
                                        <span class="font-bold">{{ $customer->invoices()->count() }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Converted Quotes:</span>
                                        <span class="font-medium text-green-600">
                                            {{ $customer->quotes()->where('status', 'Converted')->count() }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Paid Invoices:</span>
                                        <span class="font-medium text-green-600">
                                            {{ $customer->invoices()->where('status', 'Paid')->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                                <div class="space-y-3">
                                    <button type="submit" class="w-full bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition">
                                        <i class="fas fa-save mr-2"></i>Update Customer
                                    </button>
                                    <a href="{{ route('customers.show', $customer) }}" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition inline-block text-center">
                                        Cancel
                                    </a>
                                </div>
                            </div>

                            <!-- Danger Zone -->
                            <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
                                <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
                                <p class="text-sm text-red-700 mb-4">Once you delete a customer, there is no going back. Please be certain.</p>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete this customer? This action cannot be undone and will also delete all associated quotes and invoices if they exist.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                                        <i class="fas fa-trash mr-2"></i>Delete Customer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
