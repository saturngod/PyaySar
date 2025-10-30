@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="settings.edit" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
                        <p class="text-gray-600 mt-1">Manage your company settings and preferences</p>
                    </div>
                </div>

                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Form -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Company Information -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Company Information</h3>
                                <div class="space-y-4">
                                    <x-input
                                        name="company_name"
                                        label="Company Name"
                                        type="text"
                                        placeholder="Enter company name"
                                        value="{{ old('company_name', $setting->company_name) }}"
                                        error="{{ $errors->first('company_name') }}"
                                    />
                                    <x-input
                                        name="company_email"
                                        label="Company Email"
                                        type="email"
                                        placeholder="Enter company email"
                                        value="{{ old('company_email', $setting->company_email) }}"
                                        error="{{ $errors->first('company_email') }}"
                                    />
                                    <x-textarea
                                        name="company_address"
                                        label="Company Address"
                                        rows="3"
                                        placeholder="Enter company address"
                                        value="{{ old('company_address', $setting->company_address) }}"
                                        error="{{ $errors->first('company_address') }}"
                                    />
                                    <x-select
                                        name="currency"
                                        label="Default Currency"
                                        :options="\App\Helpers\CurrencyHelper::getAllCurrencies()"
                                        :value="old('currency', $setting->currency)"
                                        error="{{ $errors->first('currency') }}"
                                    />
                                </div>
                            </div>

                            <!-- Company Logo -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Company Logo</h3>
                                <div class="space-y-4">
                                    @if($setting->company_logo)
                                        <div class="flex items-center space-x-4">
                                            <img src="{{ asset('storage/' . $setting->company_logo) }}" alt="Company Logo" class="h-16 w-auto max-w-xs rounded border border-gray-200">
                                            <div>
                                                <p class="text-sm text-gray-600">Current logo</p>
                                                <form action="{{ route('settings.remove-logo') }}" method="POST" class="inline mt-2">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                                        <i class="fas fa-trash mr-1"></i>Remove logo
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                    <x-input
                                        name="company_logo"
                                        label="Upload New Logo"
                                        type="file"
                                        accept="image/*"
                                        error="{{ $errors->first('company_logo') }}"
                                        hint="Accepted formats: JPEG, PNG, JPG, GIF, SVG. Max size: 2MB."
                                    />
                                </div>
                            </div>

                            <!-- Default Documents Settings -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Default Document Settings</h3>
                                <div class="space-y-4">
                                    <x-textarea
                                        name="default_terms"
                                        label="Default Terms & Conditions"
                                        rows="4"
                                        placeholder="Enter default terms that will appear on all quotes and invoices"
                                        value="{{ old('default_terms', $setting->default_terms) }}"
                                        error="{{ $errors->first('default_terms') }}"
                                    />
                                    <x-textarea
                                        name="default_notes"
                                        label="Default Notes"
                                        rows="3"
                                        placeholder="Enter default notes that will appear on all quotes and invoices"
                                        value="{{ old('default_notes', $setting->default_notes) }}"
                                        error="{{ $errors->first('default_notes') }}"
                                    />
                                </div>
                            </div>

                            <!-- PDF Settings -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">PDF Settings</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-select
                                            name="pdf_font_size"
                                            label="Font Size"
                                            :options="[
                                                8 => '8px',
                                                10 => '10px',
                                                12 => '12px',
                                                14 => '14px',
                                                16 => '16px',
                                                18 => '18px',
                                                20 => '20px',
                                            ]"
                                            :value="old('pdf_font_size', $setting->pdf_settings['font_size'] ?? 12)"
                                            error="{{ $errors->first('pdf_font_size') }}"
                                        />
                                        <x-select
                                            name="pdf_font_family"
                                            label="Font Family"
                                            :options="[
                                                'Arial' => 'Arial',
                                                'Helvetica' => 'Helvetica',
                                                'Times New Roman' => 'Times New Roman',
                                            ]"
                                            :value="old('pdf_font_family', $setting->pdf_settings['font_family'] ?? 'Arial')"
                                            error="{{ $errors->first('pdf_font_family') }}"
                                        />
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <input type="hidden" name="pdf_show_logo" value="0">
                                            <input type="checkbox" id="pdf_show_logo" name="pdf_show_logo" value="1" {{ old('pdf_show_logo', $setting->pdf_settings['show_logo'] ?? true) ? 'checked' : '' }}
                                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                            <label for="pdf_show_logo" class="ml-2 text-sm text-gray-700">Show company logo in PDF</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="hidden" name="pdf_show_company_details" value="0">
                                            <input type="checkbox" id="pdf_show_company_details" name="pdf_show_company_details" value="1" {{ old('pdf_show_company_details', $setting->pdf_settings['show_company_details'] ?? true) ? 'checked' : '' }}
                                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                            <label for="pdf_show_company_details" class="ml-2 text-sm text-gray-700">Show company details in PDF</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="hidden" name="pdf_show_item_description" value="0">
                                            <input type="checkbox" id="pdf_show_item_description" name="pdf_show_item_description" value="1" {{ old('pdf_show_item_description', $setting->pdf_settings['show_item_description'] ?? true) ? 'checked' : '' }}
                                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                            <label for="pdf_show_item_description" class="ml-2 text-sm text-gray-700">Show item descriptions in PDF</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PDF Template Selection -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">PDF Template Selection</h3>
                                <div class="space-y-4">
                                    <p class="text-sm text-gray-600">Choose a template design for your quotes and invoices. <strong>Click "Preview" to see a sample, then click "Select" to choose your template.</strong> Selected templates will have a blue border.</p>

                                    <!-- Debug Info -->
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                        <p class="text-sm text-yellow-800 debug-selection">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Current selection: <strong>{{ old('pdf_template', $setting->pdf_settings['template'] ?? 'modern') }}</strong>
                                        </p>
                                    </div>

                                    @php
                                        $templates = [
                                            'modern' => [
                                                'name' => 'Modern',
                                                'description' => 'Clean and contemporary design with minimal elements',
                                                'preview' => 'templates/previews/modern.jpg'
                                            ],
                                            'classic' => [
                                                'name' => 'Classic',
                                                'description' => 'Traditional professional layout with clear structure',
                                                'preview' => 'templates/previews/classic.jpg'
                                            ],
                                            'minimal' => [
                                                'name' => 'Minimal',
                                                'description' => 'Simple and elegant design with essential elements only',
                                                'preview' => 'templates/previews/minimal.jpg'
                                            ],
                                            'bold' => [
                                                'name' => 'Bold',
                                                'description' => 'Eye-catching design with strong typography',
                                                'preview' => 'templates/previews/bold.jpg'
                                            ],
                                            'elegant' => [
                                                'name' => 'Elegant',
                                                'description' => 'Sophisticated design with refined details',
                                                'preview' => 'templates/previews/elegant.jpg'
                                            ],
                                            'corporate' => [
                                                'name' => 'Corporate',
                                                'description' => 'Professional business-focused layout',
                                                'preview' => 'templates/previews/corporate.jpg'
                                            ],
                                            'creative' => [
                                                'name' => 'Creative',
                                                'description' => 'Unique design for creative professionals',
                                                'preview' => 'templates/previews/creative.jpg'
                                            ],
                                            'technical' => [
                                                'name' => 'Technical',
                                                'description' => 'Detailed layout suitable for technical services',
                                                'preview' => 'templates/previews/technical.jpg'
                                            ],
                                            'luxury' => [
                                                'name' => 'Luxury',
                                                'description' => 'Premium design for high-end services',
                                                'preview' => 'templates/previews/luxury.jpg'
                                            ],
                                            'startup' => [
                                                'name' => 'Startup',
                                                'description' => 'Modern and fresh design for startups',
                                                'preview' => 'templates/previews/startup.jpg'
                                            ]
                                        ];
                                    @endphp

                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                        @foreach($templates as $templateKey => $template)
                                            <div class="relative group cursor-pointer">
                                                <input type="radio"
                                                       name="pdf_template"
                                                       value="{{ $templateKey }}"
                                                       id="template_{{ $templateKey }}"
                                                       {{ old('pdf_template', $setting->pdf_settings['template'] ?? 'modern') == $templateKey ? 'checked' : '' }}
                                                       class="sr-only peer">

                                                <div class="cursor-pointer template-card" data-template="{{ $templateKey }}">
                                                    <div class="relative overflow-hidden rounded-lg border-4 peer-checked:border-primary-500 peer-checked:shadow-xl border-gray-200 transition-all duration-200 group-hover:shadow-md">
                                                        <!-- Preview Image -->
                                                        <div class="aspect-[3/4] bg-gray-50 relative overflow-hidden">
                                                            <img src="https://picsum.photos/seed/{{ $templateKey }}-template/400/533.jpg"
                                                                     alt="{{ $template['name'] }} Template Preview"
                                                                     class="w-full h-full object-cover"
                                                                     loading="lazy">

                                                            <!-- Template Name Overlay -->
                                                            <div class="absolute top-0 left-0 right-0 bg-gradient-to-b from-black/60 to-transparent p-3">
                                                                <h4 class="text-white font-medium text-sm">{{ $template['name'] }}</h4>
                                                            </div>

                                                                                                                  </div>

                                                        <!-- Template Description -->
                                                        <div class="p-3 bg-white">
                                                            <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ $template['description'] }}</p>

                                                            <!-- Action Buttons -->
                                                            <div class="space-y-2">
                                                                <!-- Preview Button -->
                                                                <a href="{{ route('settings.template-preview', $templateKey) }}"
                                                                   target="_blank"
                                                                   class="w-full bg-gray-100 text-gray-800 px-3 py-2 rounded-md text-xs font-medium hover:bg-gray-200 transition-all duration-200 flex items-center justify-center">
                                                                    <i class="fas fa-eye mr-1"></i>Preview
                                                                </a>

                                                                <!-- Select Button -->
                                                                <button type="button"
                                                                        data-template-select="{{ $templateKey }}"
                                                                        class="w-full bg-primary-600 text-white px-3 py-2 rounded-md text-xs font-medium hover:bg-primary-700 transition-all duration-200 flex items-center justify-center">
                                                                    <i class="fas fa-check mr-1"></i>Select
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Selected Indicator -->
                                                        <div class="absolute top-2 right-2 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity duration-200 shadow-lg">
                                                            <i class="fas fa-check text-white text-sm"></i>
                                                        </div>

                                                        <!-- Selected Badge -->
                                                        <div class="absolute top-2 left-2 bg-primary-500 text-white px-2 py-1 rounded text-xs font-medium opacity-0 peer-checked:opacity-100 transition-opacity duration-200">
                                                            SELECTED
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Actions -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Save Settings</h3>
                                <div class="space-y-3">
                                    <button type="submit" class="w-full bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition">
                                        <i class="fas fa-save mr-2"></i>Save Settings
                                    </button>
                                    <a href="{{ route('dashboard') }}" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition inline-block text-center">
                                        Cancel
                                    </a>
                                </div>
                                @if(session('success'))
                                    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
                                        <p class="text-sm text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Tips -->
                            <div class="bg-primary-50 rounded-lg border border-blue-200 p-6">
                                <h3 class="text-lg font-medium text-primary-900 mb-3">
                                    <i class="fas fa-lightbulb mr-2"></i>Tips
                                </h3>
                                <ul class="text-sm text-primary-800 space-y-2">
                                    <li>• Upload a high-quality logo for professional PDFs</li>
                                    <li>• Set default terms to save time on new documents</li>
                                    <li>• Choose appropriate font size for readability</li>
                                    <li>• Keep company information up to date</li>
                                </ul>
                            </div>

                            <!-- PDF Preview Note -->
                            <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-6">
                                <h3 class="text-lg font-medium text-yellow-900 mb-3">
                                    <i class="fas fa-file-pdf mr-2"></i>PDF Preview
                                </h3>
                                <p class="text-sm text-yellow-800">
                                    Changes to PDF settings will apply to newly generated quotes and invoices. Existing PDFs will not be affected.
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the selected state based on current selection
    initializeSelectedState();

    // Handle template selection
    const selectButtons = document.querySelectorAll('[data-template-select]');

    selectButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const template = this.getAttribute('data-template-select');
            const card = this.closest('.template-card');

            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Updating...';
            this.disabled = true;

            // Send AJAX request to update template
            fetch('{{ route("settings.update-template") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    template: template
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset ALL select buttons to default state FIRST
                    document.querySelectorAll('[data-template-select]').forEach(btn => {
                        btn.innerHTML = '<i class="fas fa-check mr-1"></i>Select';
                        btn.classList.remove('bg-green-600');
                        btn.classList.add('bg-primary-600');
                        btn.disabled = false;
                    });

                    // Update all radio buttons
                    document.querySelectorAll('input[name="pdf_template"]').forEach(radio => {
                        radio.checked = radio.value === template;
                    });

                    // Update visual feedback
                    document.querySelectorAll('.template-card').forEach(c => {
                        c.classList.remove('ring-2', 'ring-primary-500');
                    });
                    card.classList.add('ring-2', 'ring-primary-500');

                    // Update debug info
                    const debugInfo = document.querySelector('.debug-selection');
                    if (debugInfo) {
                        debugInfo.innerHTML = `<i class="fas fa-check-circle mr-1"></i> Current selection: <strong>${template.charAt(0).toUpperCase() + template.slice(1)}</strong>`;
                    }

                    // Show success message
                    showNotification('Template updated successfully!', 'success');

                    // Update button state
                    this.innerHTML = '<i class="fas fa-check mr-1"></i>Selected';
                    this.classList.remove('bg-primary-600');
                    this.classList.add('bg-green-600');

                } else {
                    showNotification('Failed to update template', 'error');
                    // Reset button
                    this.innerHTML = '<i class="fas fa-check mr-1"></i>Select';
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error updating template', 'error');
                // Reset button
                this.innerHTML = '<i class="fas fa-check mr-1"></i>Select';
                this.disabled = false;
            });
        });
    });

    // Notification function
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
});

    // Initialize selected state on page load
    function initializeSelectedState() {
        const currentTemplate = '{{ $setting->pdf_settings["template"] ?? "modern" }}';

        // Set radio button
        const radio = document.querySelector(`input[name="pdf_template"][value="${currentTemplate}"]`);
        if (radio) {
            radio.checked = true;
        }

        // Update card visual
        const card = document.querySelector(`[data-template="${currentTemplate}"]`);
        if (card) {
            card.classList.add('ring-2', 'ring-primary-500', 'border-primary-500');
            card.classList.remove('border-gray-200');
        }

        // Update selected indicators
        updateSelectedIndicators(currentTemplate);

        // Update button state
        const selectBtn = document.querySelector(`[data-template-select="${currentTemplate}"]`);
        console.log('Found select button for', currentTemplate, ':', selectBtn);
        if (selectBtn) {
            selectBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Selected';
            selectBtn.classList.remove('bg-primary-600');
            selectBtn.classList.add('bg-green-600');
            console.log('Button updated to Selected state');
        } else {
            console.log('Select button NOT found for:', currentTemplate);
            // Debug: let's see what buttons exist
            const allButtons = document.querySelectorAll('[data-template-select]');
            console.log('All select buttons found:', allButtons.length);
            allButtons.forEach((btn, index) => {
                console.log(`Button ${index}: template="${btn.getAttribute('data-template-select')}"`);
            });
        }
    }

    // Update selected indicators (checkmark and badge)
    function updateSelectedIndicators(template) {
        // Remove all existing indicators
        document.querySelectorAll('.selected-checkmark, .selected-badge').forEach(el => {
            el.remove();
        });

        // Add indicators to selected template
        const card = document.querySelector(`[data-template="${template}"]`);
        if (card) {
            const container = card.querySelector('.relative');

            // Add checkmark
            const checkmark = document.createElement('div');
            checkmark.className = 'selected-checkmark absolute top-2 right-2 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center shadow-lg z-10';
            checkmark.innerHTML = '<i class="fas fa-check text-white text-sm"></i>';
            container.appendChild(checkmark);

            // Add badge
            const badge = document.createElement('div');
            badge.className = 'selected-badge absolute top-2 left-2 bg-primary-500 text-white px-2 py-1 rounded text-xs font-medium shadow-lg z-10';
            badge.textContent = 'SELECTED';
            container.appendChild(badge);
        }
}
</script>

@endsection
