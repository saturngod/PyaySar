@props([
    'variant' => 'info',
    'dismissible' => false,
])

@php
    $baseClasses = 'rounded-xl p-4 flex items-start shadow-soft';

    $variantClasses = match($variant) {
        'success' => 'bg-green-50 text-green-800 border border-green-200 shadow-green-50/50',
        'warning' => 'bg-yellow-50 text-yellow-800 border border-yellow-200 shadow-yellow-50/50',
        'danger' => 'bg-red-50 text-red-800 border border-red-200 shadow-red-50/50',
        'info' => 'bg-primary-50 text-primary-800 border border-blue-200 shadow-blue-50/50',
        'default' => 'bg-gray-50 text-gray-800 border border-gray-200 shadow-gray-50/50',
        default => 'bg-primary-50 text-primary-800 border border-blue-200 shadow-blue-50/50',
    };

    $iconClasses = match($variant) {
        'success' => 'text-green-500',
        'warning' => 'text-yellow-500',
        'danger' => 'text-red-500',
        'info' => 'text-primary-500',
        'default' => 'text-gray-500',
        default => 'text-primary-500',
    };

    $icons = [
        'success' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
        'warning' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
        'danger' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>',
        'info' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>',
        'default' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>',
    ];
@endphp

@once
    @push('scripts')
        <script>
            function dismissAlert(alertId) {
                const alert = document.getElementById(alertId);
                if (alert) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        alert.remove();
                    }, 200);
                }
            }
        </script>
    @endpush
@endonce

<div id="{{ $attributes->get('id') }}" class="{{ $baseClasses }} {{ $variantClasses }}" role="alert">
    <div class="flex-shrink-0 {{ $iconClasses }}">
        {!! $icons[$variant] !!}
    </div>
    <div class="ml-3 flex-1">
        {{ $slot }}
    </div>
    @if($dismissible)
        <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
                <button
                    type="button"
                    onclick="dismissAlert('{{ $attributes->get('id') }}')"
                    class="inline-flex rounded-lg p-1.5 {{ $variantClasses }} hover:bg-opacity-80"
                >
                    <span class="sr-only">Dismiss</span>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>
