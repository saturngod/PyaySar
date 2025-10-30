@props([
    'name',
    'id' => null,
    'options' => [],
    'value' => '',
    'placeholder' => 'Select an option',
    'required' => false,
    'onChange' => null,
    'dataAttributes' => [],
    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm'
])

@php
    $selectId = $id ?? $name;
    $hasError = $errors && $errors->has($name);
    $errorClass = $hasError ? 'border-red-500' : '';
    $finalClass = $class . ' ' . $errorClass;
@endphp

<div class="searchable-select-container relative" data-select-id="{{ $selectId }}">
    <!-- Hidden select to maintain form compatibility -->
    <select name="{{ $name }}"
            id="{{ $selectId }}"
            {{ $required ? 'required' : '' }}
            class="hidden {{ $finalClass }}">
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $key => $option)
            @if(is_array($option))
                <option value="{{ $key }}"
                        @foreach($option['data'] ?? [] as $dataKey => $dataValue)
                            data-{{ $dataKey }}="{{ $dataValue }}"
                        @endforeach
                        {{ (string) $value === (string) $key ? 'selected' : '' }}>
                    {{ $option['label'] }}
                </option>
            @else
                <option value="{{ $key }}" {{ (string) $value === (string) $key ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endif
        @endforeach
    </select>

    <!-- Custom searchable select UI -->
    <div class="searchable-select-wrapper relative">
        <div class="searchable-select-trigger {{ $finalClass }} relative cursor-pointer px-3 py-2"
             tabindex="0"
             role="combobox"
             aria-expanded="false"
             aria-haspopup="listbox">
            <span class="selected-value text-gray-500">{{ $placeholder }}</span>
            <span class="arrow absolute right-2 top-1/2 transform -translate-y-1/2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
        </div>

        <div class="searchable-select-dropdown hidden absolute left-0 top-full z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg overflow-hidden">
            <div class="search-input-container p-3 border-b border-gray-200 bg-gray-50">
                <input type="text"
                       class="search-input w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                       placeholder="Search..."
                       autocomplete="off">
            </div>
            <div class="options-container max-h-60 overflow-y-auto bg-white">
                @foreach($options as $key => $option)
                    @if(is_array($option))
                        <div class="select-option px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                             data-value="{{ $key }}"
                             @foreach($option['data'] ?? [] as $dataKey => $dataValue)
                                 data-{{ $dataKey }}="{{ $dataValue }}"
                             @endforeach>
                            {{ $option['label'] }}
                        </div>
                    @else
                        <div class="select-option px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                             data-value="{{ $key }}">
                            {{ $option }}
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('[data-select-id="{{ $selectId }}"]');
    if (!container) return;

    const trigger = container.querySelector('.searchable-select-trigger');
    const dropdown = container.querySelector('.searchable-select-dropdown');
    const searchInput = container.querySelector('.search-input');
    const options = container.querySelectorAll('.select-option');
    const hiddenSelect = container.querySelector('select');
    const selectedValue = container.querySelector('.selected-value');

    // Initialize selected value
    const currentValue = hiddenSelect.value;
    if (currentValue) {
        const selectedOption = hiddenSelect.querySelector(`option[value="${currentValue}"]`);
        if (selectedOption) {
            selectedValue.textContent = selectedOption.textContent;
            selectedValue.classList.remove('text-gray-500');
        }
    }

    // Toggle dropdown
    trigger.addEventListener('click', function() {
        const isOpen = !dropdown.classList.contains('hidden');
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    // Keyboard navigation
    trigger.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openDropdown();
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    // Search functionality
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();

        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    });

    // Option selection
    options.forEach(option => {
        option.addEventListener('click', function() {
            selectOption(this);
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!container.contains(e.target)) {
            closeDropdown();
        }
    });

    function openDropdown() {
        dropdown.classList.remove('hidden');
        trigger.setAttribute('aria-expanded', 'true');
        searchInput.value = '';
        searchInput.focus();

        // Show all options initially
        options.forEach(option => {
            option.style.display = 'block';
        });
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        trigger.setAttribute('aria-expanded', 'false');
    }

    function selectOption(option) {
        const value = option.getAttribute('data-value');
        const text = option.textContent;

        // Update hidden select
        hiddenSelect.value = value;

        // Update display
        selectedValue.textContent = text;
        selectedValue.classList.remove('text-gray-500');

        // Trigger change event
        hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));

        @if($onChange)
            // Custom onChange handler
            {{ $onChange }}(value);
        @endif

        closeDropdown();
    }
});
</script>
