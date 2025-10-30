@props([
    'name' => null,
    'label' => null,
    'required' => false,
    'error' => null,
    'rows' => 3,
    'placeholder' => null,
    'disabled' => false,
    'class' => null,
    'value' => null,
])

@php
    $hasError = $error ?? ($name && $errors->has($name) ? $errors->first($name) : null);
    $baseClasses = 'block w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-offset-0 placeholder-gray-400 hover:border-gray-400 resize-none';

    $stateClasses = $hasError
        ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 shadow-red-50'
        : 'border-gray-300 text-gray-900 focus:border-primary-500 focus:ring-primary-500';

    $disabledClasses = $disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed border-gray-200' : '';

    $classes = implode(' ', array_filter([$baseClasses, $stateClasses, $disabledClasses, $class]));
@endphp

<div class="space-y-2">
    @if($label)
        <label class="block text-sm font-semibold text-gray-700">
            {{ $label }}
            @if($required) <span class="text-red-500 ml-1">*</span> @endif
        </label>
    @endif

    <div class="relative">
        <textarea
            {{ $attributes->merge(['class' => $classes]) }}
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
        >{{ old($name, $value ?? '') }}</textarea>
        @if($hasError)
            <div class="absolute top-3 right-3">
                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
        @endif
    </div>

    @if($hasError)
        <p class="text-sm text-red-600 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            {{ $hasError }}
        </p>
    @endif
</div>
