<x-app-shell title="Mapped Account Balances" header="Mapped Account Balances">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if($mappedAccounts->count() > 0)
                        @foreach($mappedAccounts as $organizationName => $accounts)
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
                                                <span class="{{ $account->mapping_status_badge_color }} text-xs font-medium px-2 py-1 rounded">
                                                    {{ $account->mapping_status }}
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

                                                @if($account->mappedRealAccount)
                                                    <div class="mt-3 pt-2 border-t border-gray-200">
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-gray-600 text-xs">Maps to:</span>
                                                            <span class="text-xs font-medium text-blue-600">
                                                                {{ $account->mappedRealAccount->provider_name }}
                                                                ({{ $account->mappedRealAccount->external_account_name ?? $account->mappedRealAccount->external_account_id }})
                                                            </span>
                                                        </div>
                                                        <div class="flex justify-between items-center mt-1">
                                                            <span class="text-gray-600 text-xs">Real Balance:</span>
                                                            <span class="text-xs font-bold {{ $account->mappedRealAccount->last_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                {{ number_format($account->mappedRealAccount->last_balance, 2) }} TZS
                                                            </span>
                                                        </div>
                                                        <div class="flex justify-between items-center mt-1">
                                                            <span class="text-gray-600 text-xs">Last Sync:</span>
                                                            <span class="text-xs text-gray-500">
                                                                {{ $account->mappedRealAccount->last_sync_at ? $account->mappedRealAccount->last_sync_at->format('M d, Y H:i') : 'Never' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($account->mapping_description)
                                                    <div class="mt-2 pt-2 border-t border-gray-200">
                                                        <p class="text-xs text-gray-600">{{ $account->mapping_description }}</p>
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
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Mapped Accounts Found</h3>
                            <p class="text-gray-600">There are no mapped accounts configured yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-shell>
