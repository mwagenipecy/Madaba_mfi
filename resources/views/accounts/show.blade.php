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
                    @if($account->realAccount)
                        <div class="bg-blue-50 rounded-lg p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">External Integration</h3>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Provider Type</dt>
                                    <dd class="text-sm text-gray-900">{{ ucfirst($account->realAccount->provider_type) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Provider Name</dt>
                                    <dd class="text-sm text-gray-900">{{ $account->realAccount->provider_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">External Account ID</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $account->realAccount->external_account_id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Sync Status</dt>
                                    <dd class="text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $account->realAccount->sync_status === 'success' ? 'bg-green-100 text-green-800' : ($account->realAccount->sync_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($account->realAccount->sync_status) }}
                                        </span>
                                    </dd>
                                </div>
                                @if($account->realAccount->last_balance)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-600">Last Synced Balance</dt>
                                        <dd class="text-sm text-gray-900">TZS {{ number_format($account->realAccount->last_balance, 2) }}</dd>
                                    </div>
                                @endif
                                @if($account->realAccount->last_sync_at)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-600">Last Sync Date</dt>
                                        <dd class="text-sm text-gray-900">{{ $account->realAccount->last_sync_at->format('M d, Y H:i:s') }}</dd>
                                    </div>
                                @endif
                            </dl>
                            
                            @if($account->realAccount->sync_status !== 'success')
                                <div class="mt-4">
                                    <form action="{{ route('accounts.real.sync', $account->realAccount) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                            Sync Balance
                                        </button>
                                    </form>
                                </div>
                            @endif
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
                            <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-500 text-center py-8">Recent transactions will be displayed here</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>



