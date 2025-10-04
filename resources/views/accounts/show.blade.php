<x-app-shell title="Account Details" header="Account Details">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Account Details</h1>
                        <div class="flex space-x-3">
                            <a href="{{ route('accounts.edit', $account) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Edit Account
                            </a>
                            <a href="{{ route('accounts.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Back to Accounts
                            </a>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Account Name</dt>
                                    <dd class="text-sm text-gray-900">{{ $account->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Account Number</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $account->account_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Account Type</dt>
                                    <dd class="text-sm text-gray-900">{{ $account->accountType->name ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Currency</dt>
                                    <dd class="text-sm text-gray-900">{{ $account->currency }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Status</dt>
                                    <dd class="text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $account->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($account->status) }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Current Balance</dt>
                                    <dd class="text-lg font-semibold text-gray-900">TZS {{ number_format($account->balance, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Opening Balance</dt>
                                    <dd class="text-sm text-gray-900">TZS {{ number_format($account->opening_balance, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Opening Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $account->opening_date ? $account->opening_date->format('M d, Y') : 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Organization</dt>
                                    <dd class="text-sm text-gray-900">{{ $account->organization->name ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Branch</dt>
                                    <dd class="text-sm text-gray-900">{{ $account->branch->name ?? 'Main Account' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($account->description)
                        <div class="bg-gray-50 rounded-lg p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                            <p class="text-gray-700">{{ $account->description }}</p>
                        </div>
                    @endif

                    <!-- Real Account Mapping -->
                    <div class="bg-blue-50 rounded-lg p-6 mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Real Account Mapping</h3>
                            <a href="{{ route('accounts.map-real', $account) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Map Real Account
                            </a>
                        </div>
                        
                        @if($account->mappedRealAccount)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Mapping Status</dt>
                                    <dd class="text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $account->mapping_status_badge_color }}">
                                            {{ $account->mapping_status }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Provider</dt>
                                    <dd class="text-sm text-gray-900">{{ $account->mappedRealAccount->provider_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">External Account</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $account->mappedRealAccount->external_account_name ?? $account->mappedRealAccount->external_account_id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Real Account Balance</dt>
                                    <dd class="text-sm font-bold {{ $account->mappedRealAccount->last_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        TZS {{ number_format($account->mappedRealAccount->last_balance, 2) }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Last Sync</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $account->mappedRealAccount->last_sync_at ? $account->mappedRealAccount->last_sync_at->format('M d, Y H:i') : 'Never' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Sync Status</dt>
                                    <dd class="text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $account->mappedRealAccount->sync_status_badge_color }}">
                                            {{ ucfirst($account->mappedRealAccount->sync_status) }}
                                        </span>
                                    </dd>
                                </div>
                            </div>
                            
                            @if($account->mapping_description)
                                <div class="mt-4 pt-4 border-t border-blue-200">
                                    <dt class="text-sm font-medium text-gray-600 mb-2">Mapping Description</dt>
                                    <dd class="text-sm text-gray-700">{{ $account->mapping_description }}</dd>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <div class="text-gray-400 text-4xl mb-2">🏦</div>
                                <p class="text-gray-600 mb-4">This account is not mapped to any real bank account.</p>
                                <p class="text-sm text-gray-500">Map this account to a real bank account to track real balances and enable synchronization.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Real Account Information -->
                    @if($account->mappedRealAccounts->count() > 0)
                        <div class="bg-blue-50 rounded-lg p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">External Integration</h3>
                            @foreach($account->mappedRealAccounts as $realAccount)
                                <div class="border border-blue-200 rounded-lg p-4 mb-4 {{ !$loop->last ? 'mb-4' : '' }}">
                                    <h4 class="text-md font-medium text-gray-900 mb-3">{{ $realAccount->external_account_name }}</h4>
                                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-600">Provider Type</dt>
                                            <dd class="text-sm text-gray-900">{{ ucfirst($realAccount->provider_type) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-600">Provider Name</dt>
                                            <dd class="text-sm text-gray-900">{{ $realAccount->provider_name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-600">External Account ID</dt>
                                            <dd class="text-sm text-gray-900 font-mono">{{ $realAccount->external_account_id }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-600">Sync Status</dt>
                                            <dd class="text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $realAccount->sync_status === 'success' ? 'bg-green-100 text-green-800' : ($realAccount->sync_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($realAccount->sync_status) }}
                                                </span>
                                            </dd>
                                        </div>
                                        @if($realAccount->last_balance)
                                            <div>
                                                <dt class="text-sm font-medium text-gray-600">Last Synced Balance</dt>
                                                <dd class="text-sm text-gray-900">TZS {{ number_format($realAccount->last_balance, 2) }}</dd>
                                            </div>
                                        @endif
                                        @if($realAccount->last_sync_at)
                                            <div>
                                                <dt class="text-sm font-medium text-gray-600">Last Sync Date</dt>
                                                <dd class="text-sm text-gray-900">{{ $realAccount->last_sync_at->format('M d, Y H:i:s') }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                    
                                    @if($realAccount->sync_status !== 'success')
                                        <div class="mt-4">
                                            <form action="{{ route('accounts.real.sync', $realAccount) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                                    Sync Balance
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">External Integration</h3>
                            <p class="text-gray-600 mb-4">This account is not connected to any external system.</p>
                            <a href="{{ route('accounts.real.create', $account) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Connect External Account
                            </a>
                        </div>
                    @endif

                    <!-- Recent Transactions -->
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                                <a href="{{ route('accounts.general-ledger') }}?account_id={{ $account->id }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    View All Transactions
                                </a>
                            </div>
                        </div>
                        
                        @if($recentTransactions->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($recentTransactions as $transaction)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $transaction->transaction_date->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                                    {{ Str::limit($transaction->transaction_id, 20) }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    {{ Str::limit($transaction->description, 40) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->transaction_type === 'debit' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                        {{ ucfirst($transaction->transaction_type) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($transaction->balance_after, 2) }} {{ $transaction->currency }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-6 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h3m4-16H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mt-2">No transactions found</h3>
                                <p class="text-sm mt-1">This account has no transactions yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>



