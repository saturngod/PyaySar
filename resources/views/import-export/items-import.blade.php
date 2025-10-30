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
                        <h1 class="text-2xl font-bold text-gray-900">Import Items</h1>
                        <p class="text-gray-600 mt-1">Import items and services from a CSV file</p>
                    </div>
                    <a href="{{ route('import-export.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Import/Export
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Upload Form -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Upload CSV File</h3>

                            <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors">
                                <div id="upload-content">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-900 mb-2">Drop your CSV file here</p>
                                    <p class="text-sm text-gray-500 mb-4">or click to browse</p>
                                    <button type="button" onclick="document.getElementById('csv_file').click()" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition">
                                        Choose File
                                    </button>
                                    <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" class="hidden" onchange="handleFileSelect(event)">
                                </div>
                                <div id="upload-preview" class="hidden">
                                    <i class="fas fa-file-csv text-4xl text-green-500 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-900 mb-2" id="file-name"></p>
                                    <p class="text-sm text-gray-500 mb-4" id="file-size"></p>
                                    <button type="button" onclick="clearFile()" class="bg-red-100 text-red-600 px-4 py-2 rounded-md hover:bg-red-200 transition">
                                        <i class="fas fa-times mr-2"></i>Clear File
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-center">
                                <a href="{{ route('download.item.template') }}" class="text-primary-600 hover:text-primary-800 text-sm">
                                    <i class="fas fa-download mr-1"></i>Download CSV Template
                                </a>
                            </div>

                            <form id="import-form" action="{{ route('import.items') }}" method="POST" class="mt-6 hidden">
                                @csrf
                                <input type="hidden" name="csv_file" id="hidden-file-input">
                                <button type="submit" class="w-full bg-green-600 text-white px-4 py-3 rounded-md hover:bg-green-700 transition font-medium">
                                    <i class="fas fa-upload mr-2"></i>Import Items
                                </button>
                            </form>
                        </div>

                        <!-- CSV Format Guidelines -->
                        <div class="bg-primary-50 rounded-lg border border-blue-200 p-6">
                            <h3 class="text-lg font-medium text-primary-900 mb-4">
                                <i class="fas fa-info-circle mr-2"></i>CSV Format Guidelines
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <h4 class="font-medium text-primary-900 mb-1">Required Columns:</h4>
                                    <ul class="text-sm text-primary-800 space-y-1">
                                        <li>• <code>name</code> - Item name (required)</li>
                                        <li>• <code>price</code> - Unit price (required)</li>
                                        <li>• <code>currency</code> - Currency code (required)</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-primary-900 mb-1">Optional Columns:</h4>
                                    <ul class="text-sm text-primary-800 space-y-1">
                                        <li>• <code>description</code> - Item description</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-primary-900 mb-1">Valid Currency Codes:</h4>
                                    <ul class="text-sm text-primary-800 space-y-1">
                                        <li>• USD, EUR, GBP, JPY, CAD, AUD, CHF</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-primary-900 mb-1">Important Notes:</h4>
                                    <ul class="text-sm text-primary-800 space-y-1">
                                        <li>• Use UTF-8 encoding for special characters</li>
                                        <li>• Maximum file size: 10MB</li>
                                        <li>• Duplicate item names will be skipped</li>
                                        <li>• Price must be a valid decimal number</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Progress -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Import Progress</h3>
                            <div id="progress-container" class="space-y-3">
                                <div class="text-center py-8">
                                    <i class="fas fa-file-upload text-gray-400 text-3xl mb-3"></i>
                                    <p class="text-gray-500 text-sm">Upload a CSV file to begin</p>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Tips</h3>
                            <ul class="text-sm text-gray-600 space-y-2">
                                <li>• Always download the latest template</li>
                                <li>• Review your data before importing</li>
                                <li>• Test with a small batch first</li>
                                <li>• Keep a backup of your data</li>
                                <li>• Check import results for errors</li>
                            </ul>
                        </div>

                        <!-- Sample Data -->
                        <div class="bg-gray-50 rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Sample CSV Data</h3>
                            <pre class="text-xs bg-white p-3 rounded border overflow-x-auto">
name,description,price,currency
"Web Design Service","Complete website design and development",1500.00,USD
"SEO Optimization","Monthly SEO services and optimization",500.00,USD
"Logo Design","Professional logo design package",300.00,USD
"Content Writing","Blog posts and website content",100.00,USD
"Hosting Setup","Website hosting setup and configuration",250.00,USD</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Results Modal -->
<div id="results-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Import Results</h3>
                <button onclick="closeResults()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="results-content" class="space-y-4">
                <!-- Results will be populated here -->
            </div>
        </div>
    </div>
</div>

<script>
let selectedFile = null;

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        // Validate file type
        if (!file.name.match(/\.(csv|txt)$/i)) {
            alert('Please select a CSV file.');
            return;
        }

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB.');
            return;
        }

        selectedFile = file;
        displayFilePreview(file);
    }
}

function displayFilePreview(file) {
    document.getElementById('upload-content').classList.add('hidden');
    document.getElementById('upload-preview').classList.remove('hidden');
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = formatFileSize(file.size);
    document.getElementById('import-form').classList.remove('hidden');
}

function clearFile() {
    selectedFile = null;
    document.getElementById('csv_file').value = '';
    document.getElementById('hidden-file-input').value = '';
    document.getElementById('upload-content').classList.remove('hidden');
    document.getElementById('upload-preview').classList.add('hidden');
    document.getElementById('import-form').classList.add('hidden');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Drag and drop functionality
const uploadArea = document.getElementById('upload-area');

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('border-blue-400', 'bg-primary-50');
});

uploadArea.addEventListener('dragleave', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('border-blue-400', 'bg-primary-50');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('border-blue-400', 'bg-primary-50');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        if (file.name.match(/\.(csv|txt)$/i)) {
            handleFileSelect({ target: { files: [file] } });
        } else {
            alert('Please drop a CSV file.');
        }
    }
});

// Handle form submission
document.getElementById('import-form').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!selectedFile) {
        alert('Please select a file to import.');
        return;
    }

    const formData = new FormData();
    formData.append('csv_file', selectedFile);

    // Show progress
    showProgress();

    fetch('{{ route('import.items') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        showResults(data);
    })
    .catch(error => {
        hideProgress();
        alert('An error occurred during import. Please try again.');
        console.error('Import error:', error);
    });
});

function showProgress() {
    const container = document.getElementById('progress-container');
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="inline-flex items-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                <span class="text-primary-600">Importing items...</span>
            </div>
        </div>
    `;
}

function hideProgress() {
    const container = document.getElementById('progress-container');
    container.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-check-circle text-green-500 text-3xl mb-3"></i>
            <p class="text-gray-700 font-medium">Import completed</p>
        </div>
    `;
}

function showResults(data) {
    const modal = document.getElementById('results-modal');
    const content = document.getElementById('results-content');

    if (data.success) {
        content.innerHTML = `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-green-900">Import Successful</h4>
                        <p class="text-green-800 text-sm mt-1">${data.message}</p>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-primary-600">${data.data.imported}</div>
                    <div class="text-sm text-gray-600">Imported</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600">${data.data.skipped}</div>
                    <div class="text-sm text-gray-600">Skipped</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-600">${data.data.total}</div>
                    <div class="text-sm text-gray-600">Total Records</div>
                </div>
            </div>
            ${data.data.errors && data.data.errors.length > 0 ? `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="font-medium text-red-900 mb-2">Errors and Warnings:</h4>
                    <ul class="text-sm text-red-800 space-y-1">
                        ${data.data.errors.map(error => `<li>• ${error}</li>`).join('')}
                    </ul>
                </div>
            ` : ''}
        `;
    } else {
        content.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-red-900">Import Failed</h4>
                        <p class="text-red-800 text-sm mt-1">${data.message}</p>
                    </div>
                </div>
            </div>
        `;
    }

    modal.classList.remove('hidden');
}

function closeResults() {
    document.getElementById('results-modal').classList.add('hidden');
    // Clear file and reset form
    clearFile();
    // Redirect back to import/export page
    window.location.href = '{{ route('import-export.index') }}';
}
</script>
@endsection
