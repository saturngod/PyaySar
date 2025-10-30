@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto w-20 h-20 bg-primary-600 rounded-2xl flex items-center justify-center shadow-soft mb-6">
                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Welcome back
            </h2>
            <p class="text-gray-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700">
                    Sign up
                </a>
            </p>
        </div>

        <!-- Login Form -->
        <x-card class="shadow-medium">
            <form class="space-y-6" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="space-y-5">
                    <x-input
                        name="email"
                        type="email"
                        label="Email Address"
                        placeholder="Enter your email"
                        required
                        autocomplete="email"
                        value="{{ old('email') }}"
                    />

                    <x-input
                        name="password"
                        type="password"
                        label="Password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    />
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-primary-600 hover:text-primary-700">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div>
                    <x-button type="submit" variant="primary" size="lg" class="w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Sign In
                    </x-button>
                </div>
            </form>
        </x-card>

        <!-- Footer -->
        <div class="text-center">
            <p class="text-sm text-gray-500">
                By signing in, you agree to our
                <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">Terms</a> and
                <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">Privacy Policy</a>
            </p>
        </div>
    </div>
</div>
@endsection
