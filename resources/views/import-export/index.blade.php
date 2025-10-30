@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="import-export" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Import & Export</h1>
                        <p class="text-gray-600 mt-1">Manage your data import and export operations</p>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Current Data Statistics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6" id="statistics-container">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary-600" id="customers-count">-</div>
                            <div class="text-sm text-gray-600 mt-1">Customers</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600" id="items-count">-</div>
                            <div class="text-sm text-gray-600 mt-1">Items</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600" id="quotes-count">-</div>
                            <div class="text-sm text-gray-600 mt-1">Quotes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600" id="invoices-count">-</div>
                            <div class="text-sm text-gray-600 mt-1">Invoices</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Export Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fas fa-download mr-2 text-primary-600"></i>Export Data
                        </h3>

                        <div class="space-y-4">
                            <!-- Customers Export -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Customers</h4>
                                        <p class="text-sm text-gray-600">Export all customer data</p>
                                    </div>
                                    <form action="{{ route('export.customers') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition text-sm">
                                            <i class="fas fa-download mr-2"></i>Export
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Items Export -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Items</h4>
                                        <p class="text-sm text-gray-600">Export all items and services</p>
                                    </div>
                                    <form action="{{ route('export.items') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition text-sm">
                                            <i class="fas fa-download mr-2"></i>Export
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Quotes Export -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Quotes</h4>
                                        <p class="text-sm text-gray-600">Export quotes with date range</p>
                                    </div>
                                </div>
                                <form action="{{ route('export.quotes') }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                                            <input type="date" name="start_date" class="w-full rounded-md border-gray-300 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                                            <input type="date" name="end_date" class="w-full rounded-md border-gray-300 text-sm">
                                        </div>
                                    </div>
                                    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition text-sm w-full">
                                        <i class="fas fa-download mr-2"></i>Export Quotes
                                    </button>
                                </form>
                            </div>

                            <!-- Invoices Export -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Invoices</h4>
                                        <p class="text-sm text-gray-600">Export invoices with date range</p>
                                    </div>
                                </div>
                                <form action="{{ route('export.invoices') }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                                            <input type="date" name="start_date" class="w-full rounded-md border-gray-300 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                                            <input type="date" name="end_date" class="w-full rounded-md border-gray-300 text-sm">
                                        </div>
                                    </div>
                                    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition text-sm w-full">
                                        <i class="fas fa-download mr-2"></i>Export Invoices
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Import Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fas fa-upload mr-2 text-green-600"></i>Import Data
                        </h3>

                        <div class="space-y-4">
                            <!-- Customers Import -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Customers</h4>
                                        <p class="text-sm text-gray-600">Import customers from CSV file</p>
                                    </div>
                                    <a href="{{ route('import.customers.show') }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition text-sm">
                                        <i class="fas fa-upload mr-2"></i>Import
                                    </a>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('download.customer.template') }}" class="text-primary-600 hover:text-primary-800 text-sm">
                                        <i class="fas fa-file-csv mr-1"></i>Download CSV Template
                                    </a>
                                </div>
                            </div>

                            <!-- Items Import -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Items</h4>
                                        <p class="text-sm text-gray-600">Import items from CSV file</p>
                                    </div>
                                    <a href="{{ route('import.items.show') }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition text-sm">
                                        <i class="fas fa-upload mr-2"></i>Import
                                    </a>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('download.item.template') }}" class="text-primary-600 hover:text-primary-800 text-sm">
                                        <i class="fas fa-file-csv mr-1"></i>Download CSV Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Management -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-broom mr-2 text-orange-600"></i>File Management
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-3">Clean up old export files to free up storage space.</p>
                            <form action="{{ route('import-export.cleanup') }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="days_old" class="block text-sm font-medium text-gray-700 mb-1">Delete files older than</label>
                                    <div class="flex space-x-2">
                                        <input type="number" id="days_old" name="days_old" value="7" min="1" max="365" required
                                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <span class="flex items-center text-sm text-gray-600">days</span>
                                    </div>
                                </div>
                                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 transition text-sm">
                                    <i class="fas fa-trash mr-2"></i>Cleanup Old Files
                                </button>
                            </form>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="font-medium text-yellow-900 mb-2">
                                <i class="fas fa-info-circle mr-1"></i>Import Guidelines
                            </h4>
                            <ul class="text-sm text-yellow-800 space-y-1">
                                <li>• Use the provided CSV templates for best results</li>
                                <li>• Maximum file size: 10MB</li>
                                <li>• Supported formats: CSV, TXT</li>
                                <li>• Required fields must be filled in</li>
                                <li>• Check import results for any errors</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Import Results Modal -->
                <div id="import-results-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Import Results</h3>
                                <button onclick="closeImportResults()" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div id="import-results-content" class="space-y-3">
                                <!-- Results will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Load statistics on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
});

function loadStatistics() {
    fetch('{{ route("import-export.statistics") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('customers-count').textContent = data.data.customers_count;
                document.getElementById('items-count').textContent = data.data.items_count;
                document.getElementById('quotes-count').textContent = data.data.quotes_count;
                document.getElementById('invoices-count').textContent = data.data.invoices_count;
                document.getElementById('total-records').textContent = data.data.total_records;
            }
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}

function closeImportResults() {
    document.getElementById('import-results-modal').classList.add('hidden');
    // Reload statistics after import
    loadStatistics();
}

// Handle cleanup form submission with confirmation
document.querySelector('form[action*="cleanup"]').addEventListener('submit', function(e) {
    const daysOld = document.getElementById('days_old').value;
    if (!confirm(`Are you sure you want to delete all export files older than ${daysOld} days? This action cannot be undone.`)) {
        e.preventDefault();
    }
});
</script>
@endsection
