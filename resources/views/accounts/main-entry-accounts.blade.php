<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Main Entry Accounts
            </h2>
            <div class="flex space-x-2">
                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">
                    Total Balance: {{ number_format($totalBalance, 2) }} TZS
                </span>
                <span class="bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded">
                    {{ $mainEntryAccounts->count() }} Accounts
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-blue-600 text-sm font-medium">Total Balance</div>
                        <div class="text-2xl font-bold text-blue-900">{{ number_format($totalBalance, 2) }} TZS</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-green-600 text-sm font-medium">Main Entry Accounts</div>
                        <div class="text-2xl font-bold text-green-900">{{ $mainEntryAccounts->count() }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-purple-600 text-sm font-medium">With Mirror Accounts</div>
                        <div class="text-2xl font-bold text-purple-900">{{ $mainEntryAccounts->where('mirror_real_account_id', '!=', null)->count() }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-orange-600 text-sm font-medium">Average Balance</div>
                        <div class="text-2xl font-bold text-orange-900">{{ number_format($mainEntryAccounts->avg('balance'), 2) }} TZS</div>
                    </div>
                </div>
            </div>

            <!-- Main Entry Accounts -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($mainEntryAccounts->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($mainEntryAccounts as $account)
                                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="font-semibold text-lg text-gray-900">{{ $account->name }}</h3>
                                        <span class="{{ $account->mirror_account_badge_color }} text-xs font-medium px-2 py-1 rounded">
                                            {{ $account->mirror_account_status }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 text-sm">Account Number:</span>
                                            <span class="font-mono text-sm font-medium">{{ $account->account_number }}</span>
                                        </div>
                                        
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 text-sm">Branch:</span>
                                            <span class="font-medium text-sm">
                                                {{ $account->branch ? $account->branch->name : 'HQ' }}
                                                @if($account->branch && $account->branch->is_hq)
                                                    <span class="text-blue-600">(HQ)</span>
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 text-sm">Account Type:</span>
                                            <span class="font-medium text-sm">{{ $account->accountType->name ?? 'N/A' }}</span>
                                        </div>
                                        
                                        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                            <span class="text-gray-600 text-sm">System Balance:</span>
                                            <span class="font-bold text-lg {{ $account->balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($account->balance, 2) }} TZS
                                            </span>
                                        </div>

                                        @if($account->mirrorRealAccount)
                                            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                                <h4 class="text-sm font-semibold text-blue-900 mb-2">Real Bank Account</h4>
                                                <div class="space-y-2 text-sm">
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">Provider:</span>
                                                        <span class="font-medium text-blue-900">{{ $account->mirrorRealAccount->provider_name }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">Account:</span>
                                                        <span class="font-medium text-blue-900">{{ $account->mirrorRealAccount->external_account_name ?? $account->mirrorRealAccount->external_account_id }}</span>
                                                    </div>
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-blue-700">Real Balance:</span>
                                                        <span class="font-bold {{ $account->mirrorRealAccount->last_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                            {{ number_format($account->mirrorRealAccount->last_balance, 2) }} TZS
                                                        </span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">Last Sync:</span>
                                                        <span class="text-blue-600">
                                                            {{ $account->mirrorRealAccount->last_sync_at ? $account->mirrorRealAccount->last_sync_at->format('M d, Y H:i') : 'Never' }}
                                                        </span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">Sync Status:</span>
                                                        <span class="{{ $account->mirrorRealAccount->sync_status_badge_color }} text-xs font-medium px-2 py-1 rounded">
                                                            {{ ucfirst($account->mirrorRealAccount->sync_status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <div class="text-center">
                                                    <div class="text-gray-400 text-2xl mb-2">🏦</div>
                                                    <p class="text-sm text-gray-600">No mirror account configured</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($account->mirror_description)
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <h4 class="text-sm font-semibold text-gray-700 mb-1">Description:</h4>
                                                <p class="text-sm text-gray-600">{{ $account->mirror_description }}</p>
                                            </div>
                                        @endif

                                        <!-- Actions -->
                                        <div class="mt-4 pt-3 border-t border-gray-200">
                                            <div class="flex justify-between space-x-2">
                                                <a href="{{ route('accounts.show', $account) }}" 
                                                   class="flex-1 bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-2 px-3 rounded text-center">
                                                    View Details
                                                </a>
                                                <a href="{{ route('accounts.setup-mirror', $account) }}" 
                                                   class="flex-1 bg-green-500 hover:bg-green-700 text-white text-xs font-bold py-2 px-3 rounded text-center">
                                                    Setup Mirror
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400 text-6xl mb-4">🏦</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Main Entry Accounts</h3>
                            <p class="text-gray-600 mb-4">You don't have any main entry accounts configured yet.</p>
                            <a href="{{ route('accounts.index') }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                View All Accounts
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
