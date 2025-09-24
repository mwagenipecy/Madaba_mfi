<x-app-shell :title="'Sub-Accounts - ' . $category->name" :header="'Sub-Accounts - ' . $category->name">
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $category->name }} - Sub-Accounts</h1>
                    <p class="text-gray-600 mt-1">Organization-wide sub-accounts under this category, grouped by branch</p>
                </div>
                <a href="{{ route('accounts.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Create Sub-Account
                </a>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm text-gray-600">Category</p>
                    <p class="text-lg font-semibold text-gray-900">{{ optional($category->accountType)->name ?? 'Category' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Sub-Accounts</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $subAccounts->count() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Balance</p>
                    <p class="text-lg font-semibold text-gray-900">TZS {{ number_format($totalBalance, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Sub-Accounts Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($subAccounts as $account)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-medium">{{ $account->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $account->account_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $account->branch?->name ?? 'HQ' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $account->status_badge_color }}">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold">TZS {{ number_format($account->balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No sub-accounts found under this category.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-shell>


