<x-app-shell title="All Organization Accounts" header="All Organization Accounts">
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
                    <h1 class="text-2xl font-bold text-gray-900">All Organization Accounts</h1>
                    <p class="text-gray-600 mt-1">View and manage all accounts across your organization branches</p>
                </div>
                <a href="{{ route('accounts.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Create Account
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Accounts</h3>
            
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="min-w-48">
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch Filter</label>
                    <select name="branch_id" id="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">All Accounts</option>
                        <option value="main" {{ $selectedBranchId === 'main' ? 'selected' : '' }}>Organization Level (Main)</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Filter
                </button>
                
                @if($selectedBranchId)
                    <a href="{{ route('accounts.branch-accounts') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Clear Filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h3m4-16H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Total Accounts</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalAccounts }}</p>
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
                        <p class="text-sm font-medium text-gray-600">Account Types</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $accountTypeStats->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    @if($selectedBranchId)
                        @if($selectedBranchId === 'main')
                            Organization Level Accounts
                        @else
                            {{ $branches->firstWhere('id', $selectedBranchId)?->name ?? 'Selected Branch' }} Accounts
                        @endif
                    @else
                        All Organization Accounts
                    @endif
                </h3>
            </div>

            @if($accounts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type & Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($accounts as $account)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $account->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $account->account_number }}</div>
                                            @if($account->description)
                                                <div class="text-xs text-gray-400 mt-1">{{ Str::limit($account->description, 50) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm text-gray-900">{{ $account->accountType->name }}</div>
                                            <div class="text-sm text-gray-500">
                                                @if($account->branch)
                                                    {{ $account->branch->name }}
                                                @else
                                                    <span class="text-gray-400">Organization Level</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($account->balance, 2) }} {{ $account->currency }}</div>
                                        @if($account->opening_balance != $account->balance)
                                            <div class="text-xs text-gray-500">Opening: {{ number_format($account->opening_balance, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $account->status === 'active' ? 'bg-green-100 text-green-800' : 
                                               ($account->status === 'inactive' ? 'bg-gray-100 text-gray-800' : 
                                               ($account->status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                            {{ ucfirst($account->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $account->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if($account->status === 'active')
                                                <form method="POST" action="{{ route('accounts.status-change', $account) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="action" value="status_change">
                                                    <button type="button" 
                                                            class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md text-xs font-medium transition-colors"
                                                            onclick="openDisableModal({{ $account->id }}, '{{ $account->name }}')">
                                                        Disable
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('accounts.enable', $account) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md text-xs font-medium transition-colors"
                                                            onclick="return confirm('Are you sure you want to enable this account?')">
                                                        Enable
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <a href="{{ route('accounts.show', $account) }}" 
                                               class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md text-xs font-medium transition-colors">
                                                View
                                            </a>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h3m4-16H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                    </svg>
                    @if($selectedBranchId)
                        <h3 class="text-lg font-medium text-gray-900 mt-2">
                            @if($selectedBranchId === 'main')
                                No organization-level accounts found
                            @else
                                No accounts found for this branch
                            @endif
                        </h3>
                        <p class="text-sm mt-1">Try selecting a different branch or create new accounts.</p>
                    @else
                        <h3 class="text-lg font-medium text-gray-900 mt-2">No accounts found</h3>
                        <p class="text-sm mt-1">Get started by creating your first account.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Disable Account Modal -->
    <div id="disableModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-4">Disable Account</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to disable the account <span id="accountName" class="font-medium text-gray-900"></span>?
                        This will change the account status to inactive.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <form id="disableForm" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="action" value="status_change">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Disable
                        </button>
                    </form>
                    <button onclick="closeDisableModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDisableModal(accountId, accountName) {
            document.getElementById('accountName').textContent = accountName;
            document.getElementById('disableForm').action = '{{ route("accounts.status-change", ":id") }}'.replace(':id', accountId);
            document.getElementById('disableModal').classList.remove('hidden');
        }

        function closeDisableModal() {
            document.getElementById('disableModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('disableModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDisableModal();
            }
        });
    </script>
</x-app-shell>

