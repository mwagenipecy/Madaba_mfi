<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Error') — {{ config('app.name', 'Madaba MFI') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: Figtree, ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gradient-to-br from-[#176836] to-[#0f4a25]">
        <div class="w-full max-w-lg">
            <div class="text-center mb-6">
                <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="inline-flex items-center gap-2 text-white/90 hover:text-white text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    {{ config('app.name', 'Madaba MFI') }}
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="h-1.5 @yield('accent-bar', 'bg-green-600')"></div>
                <div class="p-8 sm:p-10 text-center">
                    <p class="text-6xl sm:text-7xl font-bold tracking-tight @yield('code-color', 'text-green-700')">
                        @yield('code')
                    </p>
                    <h1 class="mt-4 text-xl sm:text-2xl font-semibold text-gray-900">
                        @yield('heading')
                    </h1>
                    <p class="mt-3 text-sm sm:text-base text-gray-600 leading-relaxed">
                        @yield('message')
                    </p>

                    @hasSection('extra')
                        <div class="mt-4">
                            @yield('extra')
                        </div>
                    @endif

                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                        @yield('actions')
                    </div>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-white/70">
                @if(isset($statusCode))
                    Error {{ $statusCode }} ·
                @endif
                {{ now()->format('d M Y H:i') }}
            </p>
        </div>
    </div>
</body>
</html>
