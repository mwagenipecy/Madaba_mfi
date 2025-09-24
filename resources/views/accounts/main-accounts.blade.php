<x-app-shell title="Main Accounts" header="Main Accounts">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Main Account Categories</h1>
                    <p class="text-gray-600 mt-1">The 5 main account categories: Assets, Revenue, Liability, Equity, and Expense</p>
                </div>
                <a href="{{ route('accounts.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Create Sub-Account
                </a>
            </div>
        </div>

        <!-- Account Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($categories as $category)
                @php
                    $borderColor = match(optional($category->accountType)->name) {
                        'Assets' => 'border-green-500',
                        'Revenue' => 'border-blue-500',
                        'Liability' => 'border-yellow-500',
                        'Equity' => 'border-purple-500',
                        'Expense' => 'border-red-500',
                        default => 'border-gray-300'
                    };
                    $badge = in_array(optional($category->accountType)->name, ['Assets','Expense']) ? 'Debit Balance' : 'Credit Balance';
                    $balanceColor = in_array(optional($category->accountType)->name, ['Assets','Expense']) ? 'text-green-600' : 'text-blue-600';
                @endphp
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 {{ $borderColor }}">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h3>
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ $badge }}</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">{{ optional($category->accountType)->name }} category</p>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Balance:</span>
                            <span class="font-semibold {{ $balanceColor }}">TZS {{ number_format($category->total_balance ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Sub-Accounts:</span>
                            <span class="font-semibold">{{ $category->sub_accounts_count }}</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('accounts.main-accounts.subaccounts', $category) }}" class="text-green-600 hover:text-green-700 text-sm font-medium">
                            View Sub-Accounts →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3">
                    <div class="p-6 text-center text-gray-500 border rounded-lg">No main categories found.</div>
                </div>
            @endforelse
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Create Cash Account</p>
                        <p class="text-sm text-gray-600">Under Assets category</p>
                    </div>
                </a>

                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Create Bank Account</p>
                        <p class="text-sm text-gray-600">Under Assets category</p>
                    </div>
                </a>

                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Create Customer Deposit</p>
                        <p class="text-sm text-gray-600">Under Liability category</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-shell>
