@props([
    'variant' => 'default',
    'size' => 'md',
])

@php
    $baseClasses = 'inline-flex items-center font-medium rounded-full';

    $variantClasses = match($variant) {
        'default' => 'bg-gray-100 text-gray-800',
        'primary' => 'bg-gray-900 text-white',
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-primary-100 text-primary-800',
        'draft' => 'bg-gray-100 text-gray-600',
        'sent' => 'bg-primary-100 text-primary-700',
        'paid' => 'bg-green-100 text-green-700',
        'overdue' => 'bg-red-100 text-red-700',
        'cancelled' => 'bg-gray-100 text-gray-500',
        default => 'bg-gray-100 text-gray-800',
    };

    $sizeClasses = match($size) {
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1.5 text-sm',
        default => 'px-3 py-1 text-sm',
    };

    $classes = implode(' ', [$baseClasses, $variantClasses, $sizeClasses]);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
