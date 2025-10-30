@props([
    'headers' => [],
    'caption' => null,
    'striped' => true,
    'bordered' => true,
    'hover' => true,
    'compact' => false,
])

@php
    $tableClasses = 'min-w-full divide-y divide-gray-200/50';
    $headerClasses = 'bg-gray-50 border-b border-gray-200/50';
    $rowClasses = '';

    if ($striped) {
        $rowClasses .= ' even:bg-white odd:bg-gray-50/30';
    }

    if ($hover) {
        $rowClasses .= ' hover:bg-primary-50 hover:shadow-soft';
    }

    if ($bordered) {
        $tableClasses .= ' border border-gray-200/50 shadow-soft';
        $headerClasses .= ' border-b border-gray-200/50';
    }
@endphp

<div class="bg-white overflow-hidden rounded-xl shadow-soft {{ $attributes->get('class') }}">
    @if($caption)
        <div class="px-6 py-4 border-b border-gray-200/50 bg-white">
            <h3 class="text-lg font-semibold text-gray-900">{{ $caption }}</h3>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="{{ $tableClasses }}">
            @if(!empty($headers))
                <thead class="{{ $headerClasses }}">
                    <tr>
                        @foreach($headers as $header)
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody class="bg-white divide-y divide-gray-200/50">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($paginator) && $paginator->hasPages())
        <div class="px-6 py-4 bg-white border-t border-gray-200/50">
            {{ $paginator->links('pagination::tailwind') }}
        </div>
    @endif

    @if(isset($empty) && $empty)
        <div class="text-center py-16">
            <div class="w-20 h-20 bg-gray-200 rounded-2xl mx-auto mb-6 flex items-center justify-center shadow-soft">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-5V8a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h1m0-8V6a2 2 0 012-2h6a2 2 0 012 2v2m0 0h8a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2v-2z"></path>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $empty['title'] ?? 'No items' }}</h3>
            <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">{{ $empty['description'] ?? 'Get started by creating a new item.' }}</p>
            @if(isset($empty['action']))
                <div class="mt-8">
                    {{ $empty['action'] }}
                </div>
            @endif
        </div>
    @endif
</div>
