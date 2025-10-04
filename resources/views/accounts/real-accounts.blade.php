<x-app-shell title="Real Accounts" header="Real Accounts">
    <div class="space-y-6">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Real Accounts</h1>
                    <p class="text-gray-600 mt-1">External system integration (MNO/Bank) with real-time balance sync</p>
                </div>
                <a href="{{ route('organization-settings.mapped-account-balances') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Add Real Account
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Total Real Accounts</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalRealAccounts }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Total Balance</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalBalance, 2) }} TZS</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Provider Types</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $providerStats->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real Accounts List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">External Integration Accounts</h3>
            </div>

            @if($realAccounts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider Info</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance & Sync</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mapped Accounts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($realAccounts as $realAccount)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $realAccount->external_account_name }}</div>
                                            <div class="text-sm text-gray-500 font-mono">{{ $realAccount->external_account_id }}</div>
                                            @if($realAccount->mapping_description)
                                                <div class="text-xs text-gray-400 mt-1">{{ Str::limit($realAccount->mapping_description, 50) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm text-gray-900">{{ $realAccount->provider_name }}</div>
                                            <div class="text-sm text-gray-500">{{ ucfirst($realAccount->provider_type) }}</div>
                                            @if($realAccount->api_endpoint)
                                                <div class="text-xs text-gray-400 mt-1">{{ Str::limit($realAccount->api_endpoint, 30) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm text-gray-900">{{ number_format($realAccount->last_balance, 2) }} TZS</div>
                                            <div class="text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $realAccount->sync_status === 'success' ? 'bg-green-100 text-green-800' : ($realAccount->sync_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($realAccount->sync_status) }}
                                                </span>
                                            </div>
                                            @if($realAccount->last_sync_at)
                                                <div class="text-xs text-gray-500 mt-1">{{ $realAccount->last_sync_at->format('M d, Y H:i') }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            @foreach($realAccount->mappedAccounts as $mappedAccount)
                                                <div class="text-sm">
                                                    <div class="text-gray-900">{{ $mappedAccount->name }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $mappedAccount->accountType->name }}
                                                        @if($mappedAccount->branch)
                                                            • {{ $mappedAccount->branch->name }}
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if($realAccount->sync_status !== 'success')
                                                <form action="{{ route('accounts.real.sync', $realAccount) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md text-xs font-medium transition-colors">
                                                        Sync
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <a href="{{ route('organization-settings.real-accounts.edit', $realAccount) }}" 
                                               class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md text-xs font-medium transition-colors">
                                                Edit
                                            </a>
                                            
                                            <form method="POST" action="{{ route('organization-settings.real-accounts.destroy', $realAccount) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md text-xs font-medium transition-colors"
                                                        onclick="return confirm('Are you sure you want to delete this real account?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mt-2">No real accounts found</h3>
                    <p class="text-sm mt-1">Get started by adding your first external account integration.</p>
                    <div class="mt-4">
                        <a href="{{ route('organization-settings.mapped-account-balances') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Add Real Account
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-shell>

