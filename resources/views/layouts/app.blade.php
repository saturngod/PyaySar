<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Dashboard') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">


</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <div class="min-h-screen">
        @auth
            @yield('content')
        @else
            <!-- Guest Layout -->
            <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
                <div class="max-w-md w-full space-y-8 animate-fade-in">
                    @yield('content')
                </div>
            </div>
        @endauth
    </div>

    <!-- Flash Messages -->
    <div id="flash-messages" class="fixed top-4 right-4 z-50 space-y-2">
        @if(session('success'))
            <x-alert variant="success" dismissible id="flash-success" class="w-96">
                {{ session('success') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert variant="danger" dismissible id="flash-error" class="w-96">
                {{ session('error') }}
            </x-alert>
        @endif

        @if(session('status'))
            <x-alert variant="info" dismissible id="flash-info" class="w-96">
                {{ session('status') }}
            </x-alert>
        @endif

        @if($errors->any())
            <x-alert variant="danger" dismissible id="flash-validation" class="w-96">
                <div class="font-medium">Validation Errors:</div>
                <ul class="mt-1 text-sm list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
    </div>

    <!-- Scripts Section -->
    @stack('scripts')

    <script>
        // Auto-dismiss flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('#flash-messages .alert');
            flashMessages.forEach(function(message, index) {
                setTimeout(function() {
                    const id = message.getAttribute('id');
                    if (id) {
                        dismissAlert(id);
                    }
                }, 5000 + (index * 500));
            });
        });
    </script>
</body>
</html>
