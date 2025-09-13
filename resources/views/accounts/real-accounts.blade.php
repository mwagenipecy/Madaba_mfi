<x-app-shell title="Real Accounts" header="Real Accounts">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Real Accounts</h1>
                    <p class="text-gray-600 mt-1">External system integration (MNO/Bank) with real-time balance sync</p>
                </div>
                <a href="{{ route('accounts.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Create Real Account
                </a>
            </div>
        </div>

        <!-- Real Accounts List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">External Integration Accounts</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <!-- This will be populated by Livewire component -->
                <div class="p-6 text-center text-gray-500">
                    <p>Real accounts will be displayed here</p>
                    <p class="text-sm mt-1">These accounts integrate with MNO, Bank, and Payment Gateway systems</p>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>

