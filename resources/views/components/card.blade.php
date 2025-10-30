@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'default',
    'border' => true,
    'shadow' => true,
])

@php
    $baseClasses = 'bg-white rounded-xl';

    $paddingClasses = match($padding) {
        'none' => '',
        'sm' => 'p-4',
        'default' => 'p-6',
        'lg' => 'p-8',
        default => 'p-6',
    };

    $borderClasses = $border ? 'border border-gray-200' : '';
    $shadowClasses = $shadow ? 'shadow-soft ' : '';

    $classes = implode(' ', array_filter([$baseClasses, $borderClasses, $shadowClasses]));
@endphp

<div {{ $attributes->merge(['class' => $classes . ' group']) }}>
    @if($title || $subtitle || isset($header))
        <div class="mb-6">
            @if(isset($header))
                {{ $header }}
            @else
                @if($title)
                    <h3 class="text-xl font-bold text-gray-900 group-hover:text-primary-700">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-sm text-gray-500 mt-2 leading-relaxed">{{ $subtitle }}</p>
                @endif
            @endif
        </div>
    @endif

    <div class="{{ $padding !== 'none' ? $paddingClasses : '' }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="mt-6 pt-4 border-t border-gray-100 bg-gray-50/50 -mx-6 -mb-6 px-6 pb-6 rounded-b-xl">
            {{ $footer }}
        </div>
    @endif
</div>
