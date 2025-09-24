<x-layouts.app>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">Livewire Layout Test</h1>
                    <p class="mb-4">This page tests the Livewire layout component.</p>
                    
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <strong>Success!</strong> The Livewire layout is working correctly.
                    </div>
                    
                    <div class="space-y-2">
                        <p><strong>Layout:</strong> components.layouts.app</p>
                        <p><strong>Status:</strong> ✅ Working</p>
                        <p><strong>Components:</strong> Navigation menu, banner, and main content area</p>
                    </div>
                    
                    <div class="mt-6">
                        <a href="{{ route('register') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Test Register Flow (Livewire Component)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
