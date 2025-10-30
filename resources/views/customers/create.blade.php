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
                        <h1 class="text-2xl font-bold text-gray-900">Add New Customer</h1>
                        <p class="text-gray-600 mt-1">Create a new customer record</p>
                    </div>
                    <a href="{{ route('customers.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Customers
                    </a>
                </div>

                <!-- Form -->
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf
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
                                        value="{{ old('name') }}"
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
                                                value="{{ old('contact_person') }}"
                                                error="{{ $errors->first('contact_person') }}"
                                            />
                                            <x-input
                                                name="contact_phone"
                                                label="Phone Number"
                                                type="tel"
                                                placeholder="Enter phone number"
                                                value="{{ old('contact_phone') }}"
                                                error="{{ $errors->first('contact_phone') }}"
                                            />
                                            <div class="md:col-span-2">
                                                <x-input
                                                    name="contact_email"
                                                    label="Email Address"
                                                    type="email"
                                                    placeholder="Enter email address"
                                                    value="{{ old('contact_email') }}"
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
                                            value="{{ old('address') }}"
                                            error="{{ $errors->first('address') }}"
                                            hint="Include street address, city, state/province, postal code, and country for complete address information."
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Quick Actions -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                                <div class="space-y-3">
                                    <button type="submit" class="w-full bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">
                                        <i class="fas fa-save mr-2"></i>Create Customer
                                    </button>
                                    <a href="{{ route('customers.index') }}" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 inline-block text-center">
                                        Cancel
                                    </a>
                                </div>
                            </div>

                            <!-- Tips -->
                            <div class="bg-primary-50 rounded-lg border border-blue-200 p-6">
                                <h3 class="text-lg font-medium text-primary-900 mb-3">
                                    <i class="fas fa-lightbulb mr-2"></i>Tips
                                </h3>
                                <ul class="text-sm text-primary-800 space-y-2">
                                    <li>• Include complete contact information for easy communication</li>
                                    <li>• Add a full address for accurate invoicing and shipping</li>
                                    <li>• Contact person helps identify the right person to speak with</li>
                                    <li>• Email address is used for sending quotes and invoices</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
