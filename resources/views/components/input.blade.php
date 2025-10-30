@props([
    'name',
    'label' => null,
    'type' => 'text',
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'class' => null,
    'prefix' => null,
    'suffix' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $error ?? ($errors->has($name) ? $errors->first($name) : null);
    $baseClasses = 'block w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-offset-0 placeholder-gray-400 hover:border-gray-400';

    $stateClasses = $hasError
        ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 shadow-red-50'
        : 'border-gray-300 text-gray-900 focus:border-primary-500 focus:ring-primary-500';

    $disabledClasses = $disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed border-gray-200' : '';

    $classes = implode(' ', array_filter([$baseClasses, $stateClasses, $disabledClasses, $class]));

    // Adjust padding based on prefix/suffix
    $paddingClasses = '';
    if ($prefix && $suffix) {
        $paddingClasses = 'pl-10 pr-12';
    } elseif ($prefix) {
        $paddingClasses = 'pl-10 pr-4';
    } elseif ($suffix) {
        $paddingClasses = 'pl-4 pr-12';
    } else {
        $paddingClasses = 'px-4';
    }

    // Replace default padding with adjusted padding
    $classes = preg_replace('/\bpx-4\b/', $paddingClasses, $classes);
@endphp

<div {{ $attributes->except(['name', 'type', 'id', 'value', 'placeholder', 'required', 'disabled', 'label', 'hint', 'error', 'class', 'prefix', 'suffix']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-semibold text-gray-700 mb-2">
            {{ $label }}
            @if($required) <span class="text-red-500 ml-1">*</span> @endif
        </label>
    @endif

    <div class="relative">
        @if($prefix)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">{{ $prefix }}</span>
            </div>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ $value ?? old($name) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="{{ $classes }}"
        >

        @if($suffix)
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">{{ $suffix }}</span>
            </div>
        @endif

        @if($hasError && !$prefix && !$suffix)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
        @endif
    </div>

    @if($hasError)
        <p class="mt-2 text-sm text-red-600 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            {{ $hasError }}
        </p>
    @endif

    @if($hint && !$hasError)
        <p class="mt-2 text-sm text-gray-500 italic">{{ $hint }}</p>
    @endif
</div>
