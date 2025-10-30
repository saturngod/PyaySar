@props([
    'currentRoute' => null,
])

@php
    $navigation = [
        [
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>',
        ],
        [
            'name' => 'Items',
            'route' => 'items.index',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
        ],
        [
            'name' => 'Customers',
            'route' => 'customers.index',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
        ],
        [
            'name' => 'Quotes',
            'route' => 'quotes.index',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
        ],
        [
            'name' => 'Invoices',
            'route' => 'invoices.index',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
        ],
        [
            'name' => 'Reports',
            'route' => 'reports.index',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>',
        ],
        [
            'name' => 'Settings',
            'route' => 'settings.edit',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>',
        ],
        [
            'name' => 'Logout',
            'route' => 'logout',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>',
            'isLogout' => true,
        ],
    ];

    $currentRoute = $currentRoute ?? request()->route()->getName();
@endphp

<aside class="w-64 bg-white border-r border-gray-200 h-full overflow-y-auto shadow-soft">
    <!-- Logo/Brand -->
    <div class="flex items-center h-16 px-6 border-b border-gray-200 bg-white">
        <div class="flex items-center space-x-3">

            <h1 class="text-xl font-bold text-gray-900">{{ config('app.name') }}</h1>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-1">
        @foreach($navigation as $item)
            @php
                $isActive = $currentRoute === $item['route'] ||
                           (str_starts_with($currentRoute, str_replace('.index', '', $item['route'])));
            @endphp

            @if(isset($item['isLogout']) && $item['isLogout'])
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="group w-full flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                                   text-red-600 hover:bg-red-50 hover:text-red-700 hover:shadow-soft">
                        <span class="flex-shrink-0 w-5">
                            {!! $item['icon'] !!}
                        </span>
                        <span class="ml-3">{{ $item['name'] }}</span>
                        <svg class="ml-auto w-4 h-4 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            @else
                <a href="{{ route($item['route']) }}"
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                          {{ $isActive
                              ? 'bg-primary-600 text-white shadow-medium'
                              : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 hover:shadow-soft' }}">
                    <span class="flex-shrink-0 w-5">
                        {!! $item['icon'] !!}
                    </span>
                    <span class="ml-3 font-medium">{{ $item['name'] }}</span>

                    @if($isActive)
                        <span class="ml-auto w-2 h-2 bg-white rounded-full"></span>
                    @else
                        <svg class="ml-auto w-4 h-4 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>


</aside>
