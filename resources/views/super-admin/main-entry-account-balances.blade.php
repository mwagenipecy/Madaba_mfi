<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Main Entry Account Balances
            </h2>
            <div class="flex space-x-2">
                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">
                    Total Balance: {{ number_format($totalBalance, 2) }} TZS
                </span>
                <span class="bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded">
                    {{ $totalAccounts }} Accounts
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if($mainEntryAccounts->count() > 0)
                        @foreach($mainEntryAccounts as $organizationName => $accounts)
                            <div class="mb-8">
                                <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-800">{{ $organizationName }}</h3>
                                    <div class="flex space-x-2">
                                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">
                                            Org Balance: {{ number_format($accounts->sum('balance'), 2) }} TZS
                                        </span>
                                        <span class="bg-gray-100 text-gray-800 text-sm font-medium px-2.5 py-0.5 rounded">
                                            {{ $accounts->count() }} Accounts
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($accounts as $account)
                                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                                            <div class="flex justify-between items-start mb-2">
                                                <h4 class="font-medium text-gray-900">{{ $account->name }}</h4>
                                                <span class="{{ $account->mirror_account_badge_color }} text-xs font-medium px-2 py-1 rounded">
                                                    {{ $account->mirror_account_status }}
                                                </span>
                                            </div>
                                            
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Account Number:</span>
                                                    <span class="font-mono">{{ $account->account_number }}</span>
                                                </div>
                                                
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Branch:</span>
                                                    <span class="font-medium">
                                                        {{ $account->branch ? $account->branch->name : 'HQ' }}
                                                        @if($account->branch && $account->branch->is_hq)
                                                            <span class="text-blue-600">(HQ)</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Type:</span>
                                                    <span class="font-medium">{{ $account->accountType->name ?? 'N/A' }}</span>
                                                </div>
                                                
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-600">Balance:</span>
                                                    <span class="font-bold text-lg {{ $account->balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ number_format($account->balance, 2) }} TZS
                                                    </span>
                                                </div>

                                                @if($account->mirrorRealAccount)
                                                    <div class="mt-3 pt-2 border-t border-gray-200">
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-gray-600 text-xs">Mirrors:</span>
                                                            <span class="text-xs font-medium text-blue-600">
                                                                {{ $account->mirrorRealAccount->provider_name }}
                                                                ({{ $account->mirrorRealAccount->external_account_name ?? $account->mirrorRealAccount->external_account_id }})
                                                            </span>
                                                        </div>
                                                        <div class="flex justify-between items-center mt-1">
                                                            <span class="text-gray-600 text-xs">Real Balance:</span>
                                                            <span class="text-xs font-bold {{ $account->mirrorRealAccount->last_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                {{ number_format($account->mirrorRealAccount->last_balance, 2) }} TZS
                                                            </span>
                                                        </div>
                                                        <div class="flex justify-between items-center mt-1">
                                                            <span class="text-gray-600 text-xs">Last Sync:</span>
                                                            <span class="text-xs text-gray-500">
                                                                {{ $account->mirrorRealAccount->last_sync_at ? $account->mirrorRealAccount->last_sync_at->format('M d, Y H:i') : 'Never' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($account->mirror_description)
                                                    <div class="mt-2 pt-2 border-t border-gray-200">
                                                        <p class="text-xs text-gray-600">{{ $account->mirror_description }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400 text-6xl mb-4">🏦</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Main Entry Accounts Found</h3>
                            <p class="text-gray-600">There are no main entry accounts configured yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
