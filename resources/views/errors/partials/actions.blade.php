@php
    $homeUrl = auth()->check() ? route('dashboard') : url('/');
    $homeLabel = auth()->check() ? 'Dashboard' : 'Home';
@endphp

<a href="{{ $homeUrl }}"
   class="inline-flex items-center justify-center w-full sm:w-auto px-5 py-2.5 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition-colors">
    {{ $homeLabel }}
</a>

@if(!empty($showBack))
    <button type="button" onclick="history.back()"
            class="inline-flex items-center justify-center w-full sm:w-auto px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors">
        Go Back
    </button>
@endif

@if(!empty($showRefresh))
    <button type="button" onclick="location.reload()"
            class="inline-flex items-center justify-center w-full sm:w-auto px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors">
        Try Again
    </button>
@endif

@if(!empty($showLogin) && !auth()->check())
    <a href="{{ route('login') }}"
       class="inline-flex items-center justify-center w-full sm:w-auto px-5 py-2.5 rounded-lg border border-green-600 text-green-700 text-sm font-medium hover:bg-green-50 transition-colors">
        Sign In
    </a>
@endif
