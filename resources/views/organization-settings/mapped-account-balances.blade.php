<x-app-shell title="Mapped Account Balances" header="Mapped Account Balances">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Organization Header -->
            <div class="mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ $organization->name }}</h2>
                                <p class="text-gray-600 mt-1">Real Account Mapping Management</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">Total Balance</div>
                                    <div class="text-2xl font-bold {{ $totalBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($totalBalance, 2) }} TZS
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $totalAccounts }} Mapped Accounts</div>
                                </div>
                                <button onclick="openAddRealAccountModal()" 
                                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Real Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-green-800 font-medium">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Account Type Filters -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Filter by Account Type</h3>
                                @php
                                    $receiverCount = 0;
                                    $giverCount = 0;
                                    foreach($realAccountsWithMappings->flatten() as $realAccount) {
                                        $accountType = $realAccount->mappedAccounts->first()?->external_account_type;
                                        if ($accountType === 'receiver') $receiverCount++;
                                        elseif ($accountType === 'giver') $giverCount++;
                                    }
                                @endphp
                                <p class="text-sm text-gray-600 mt-1">
                                    Total: {{ $receiverCount + $giverCount }} accounts | 
                                    <span class="text-green-600">{{ $receiverCount }} Receiver</span> | 
                                    <span class="text-orange-600">{{ $giverCount }} Giver</span>
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <button id="filter-all" onclick="filterAccounts('all')" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors">
                                    All Accounts ({{ $receiverCount + $giverCount }})
                                </button>
                                <button id="filter-receiver" onclick="filterAccounts('receiver')" 
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors hover:bg-gray-300">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    Receiver ({{ $receiverCount }})
                                </button>
                                <button id="filter-giver" onclick="filterAccounts('giver')" 
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors hover:bg-gray-300">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Giver ({{ $giverCount }})
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($realAccountsWithMappings->count() > 0)
                        @foreach($realAccountsWithMappings as $branchName => $realAccounts)
                            <div class="mb-8">
                                <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        {{ $branchName ?? 'HQ Branch' }}
                                    </h3>
                                    <div class="flex space-x-2">
                                        @php
                                            $totalBranchBalance = $realAccounts->sum(function($ra) {
                                                return $ra->mappedAccounts->sum('balance');
                                            });
                                            $totalBranchAccounts = $realAccounts->sum(function($ra) {
                                                return $ra->mappedAccounts->count();
                                            });
                                        @endphp
                                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">
                                            Branch Balance: {{ number_format($totalBranchBalance, 2) }} TZS
                                        </span>
                                        <span class="bg-gray-100 text-gray-800 text-sm font-medium px-2.5 py-0.5 rounded">
                                            {{ $totalBranchAccounts }} Mapped Accounts
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    @foreach($realAccounts as $realAccount)
                                        <div class="bg-white border border-gray-200 rounded-lg p-6 account-card" 
                                             data-account-type="{{ $realAccount->mappedAccounts->first()?->external_account_type ?? 'unknown' }}">
                                            <!-- Real Account Header -->
                                            <div class="flex justify-between items-start mb-4 pb-3 border-b border-gray-100">
                                                <div>
                                                    <h4 class="font-semibold text-gray-900 text-lg">
                                                        {{ $realAccount->provider_name }} - {{ $realAccount->external_account_name }}
                                                    </h4>
                                                    @if($realAccount->external_account_id)
                                                        <p class="text-sm text-gray-600 mt-1">
                                                            Account #: <span class="font-mono">{{ $realAccount->external_account_id }}</span>
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="text-right">
                                                    <div class="flex flex-col items-end space-y-2">
                                                        <div class="flex space-x-2">
                                                            @if($realAccount->mappedAccounts->first()?->external_account_type)
                                                                <span class="px-2 py-1 text-xs font-medium rounded {{ $realAccount->mappedAccounts->first()->external_account_type == 'receiver' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                                                    {{ $realAccount->mappedAccounts->first()->external_account_type == 'receiver' ? 'Receiver (Money In)' : 'Giver (Money Out)' }}
                                                                </span>
                                                            @endif
                                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
                                                                {{ ucfirst($realAccount->provider_type) }}
                                                            </span>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $realAccount->mappedAccounts->count() }} Mapped System Accounts
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Real Account Details -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                                @if($realAccount->last_balance !== null)
                                                    <div class="bg-gray-50 rounded-lg p-3">
                                                        <div class="text-sm text-gray-600">Real Account Balance</div>
                                                        <div class="font-bold {{ $realAccount->last_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                            {{ number_format($realAccount->last_balance, 2) }} TZS
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                <div class="bg-gray-50 rounded-lg p-3">
                                                    <div class="text-sm text-gray-600">Provider</div>
                                                    <div class="font-medium">{{ $realAccount->provider_name }}</div>
                                                    @if($realAccount->api_endpoint)
                                                        <div class="text-xs text-gray-500 mt-1">{{ $realAccount->api_endpoint }}</div>
                                                    @endif
                                                </div>
                                                
                                                <div class="bg-gray-50 rounded-lg p-3">
                                                    <div class="text-sm text-gray-600">Last Sync</div>
                                                    <div class="font-medium">
                                                        {{ $realAccount->last_sync_at ? $realAccount->last_sync_at->format('M d, Y H:i') : 'Never' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex justify-end space-x-2 mb-4">
                                                <a href="{{ route('organization-settings.real-accounts.edit', $realAccount) }}" 
                                                   class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    Edit
                                                </a>
                                                <button onclick="confirmDelete({{ $realAccount->id }})" 
                                                        class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </div>

                                            <!-- Mapped System Accounts -->
                                            <div class="border-t border-gray-200 pt-4">
                                                <h5 class="text-sm font-semibold text-gray-700 mb-3">Mapped System Accounts:</h5>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    @foreach($realAccount->mappedAccounts as $account)
                                                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                                                            <div class="flex justify-between items-start mb-2">
                                                                <div>
                                                                    <div class="font-medium text-gray-900">{{ $account->name }}</div>
                                                                    <div class="text-sm text-gray-600">#{{ $account->account_number }}</div>
                                                                </div>
                                                                <span class="text-xs text-gray-500">{{ $account->accountType->name ?? 'N/A' }}</span>
                                                            </div>
                                                            
                                                            <div class="flex justify-between items-center mt-2">
                                                                <span class="text-sm text-gray-600">System Balance:</span>
                                                                <span class="font-bold {{ $account->balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                    {{ number_format($account->balance, 2) }} TZS
                                                                </span>
                                                            </div>
                                                            
                                                            @if($account->mapping_description)
                                                                <div class="mt-2 pt-2 border-t border-gray-200">
                                                                    <p class="text-xs text-gray-600">{{ $account->mapping_description }}</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
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
                            <p class="text-gray-600">This organization has no mapped accounts configured yet.</p>
                            <p class="text-gray-500 text-sm mt-2 mb-6">
                                Click "Add Real Account" to map your external accounts to system accounts.
                            </p>
                            <button onclick="openAddRealAccountModal()" 
                                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Map Your First Account
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Real Account Modal -->
    <div id="addRealAccountModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-90vh overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Map Real Account to System Account</h3>
                    
                    <form id="addRealAccountForm" method="POST" action="{{ route('organization-settings.real-accounts.store') }}">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Real Account Details -->
                            <div class="border-b border-gray-200 pb-6">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Real Account Information
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Real Account Name</label>
                                        <input type="text" name="external_account_name" required
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                               placeholder="e.g., Standard Bank Business Account">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Real Account Number</label>
                                        <input type="text" name="external_account_id" required
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                               placeholder="e.g., 034000012345678">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Provider Name</label>
                                        <input type="text" name="provider_name" required
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                               placeholder="e.g., Standard Bank, Vodacom M-Pesa">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Provider Type</label>
                                        <select name="provider_type" required
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            <option value="">Select Provider Type</option>
                                            <option value="bank">Bank</option>
                                            <option value="mno">Mobile Money Operator</option>
                                            <option value="payment_gateway">Payment Gateway</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">API Endpoint (Optional)</label>
                                    <input type="url" name="api_endpoint"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           placeholder="https://api.example.com/v1/balance">
                                </div>
                            </div>

                            <!-- System Account Mapping -->
                            <div class="border-b border-gray-200 pb-6">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    Map to System Account
                                </h4>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">System Account</label>
                                    @if($externalAccounts->count() > 0)
                                        <select name="account_id" required
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            <option value="">Select External System Account to Map</option>
                                            @foreach($externalAccounts as $account)
                                                <option value="{{ $account->id }}">
                                                    {{ $account->name }} ({{ $account->account_number }}) - {{ $account->external_account_type == 'receiver' ? 'Receiver' : 'Giver' }} - {{ $account->branch ? $account->branch->name : 'HQ' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-sm text-gray-500 mt-2">
                                            Select which external system account (receiver or giver) this real account should map to.
                                        </p>
                                    @else
                                        <div class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-500">
                                            No external accounts available for mapping
                                        </div>
                                        <p class="text-sm text-red-500 mt-2">
                                            You need to create external accounts first. <a href="{{ route('accounts.create') }}" class="text-blue-600 hover:underline">Create External Account</a>
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Mapping Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mapping Description</label>
                                <textarea name="mapping_description" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                          placeholder="Optional description explaining this mapping (e.g., Main business account for loan disbursements)"></textarea>
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                            <button type="button" onclick="closeAddRealAccountModal()"
                                    class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                Cancel
                            </button>
                            @if($externalAccounts->count() > 0)
                                <button type="submit"
                                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                    Create Mapping
                                </button>
                            @else
                                <button type="button" disabled
                                        class="px-6 py-2 bg-gray-400 text-gray-200 font-medium rounded-lg cursor-not-allowed">
                                    Create Mapping
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openAddRealAccountModal() {
            document.getElementById('addRealAccountModal').classList.remove('hidden');
        }

        function closeAddRealAccountModal() {
            document.getElementById('addRealAccountModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('addRealAccountModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddRealAccountModal();
            }
        });

        // Delete confirmation function
        function confirmDelete(realAccountId) {
            if (confirm('Are you sure you want to delete this real account mapping? This action cannot be undone.')) {
                // Create form to submit DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/organization-settings/real-accounts/${realAccountId}`;
                form.style.display = 'none';
                
                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                // Add method spoofing for DELETE
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Account filtering function
        function filterAccounts(type) {
            const accountCards = document.querySelectorAll('.account-card');
            const filterButtons = document.querySelectorAll('[id^="filter-"]');
            let visibleCount = 0;
            
            // Reset all button styles
            filterButtons.forEach(button => {
                button.classList.remove('bg-blue-600', 'text-white');
                button.classList.add('bg-gray-200', 'text-gray-700');
            });
            
            // Set active button style
            const activeButton = document.getElementById(`filter-${type}`);
            if (activeButton) {
                activeButton.classList.remove('bg-gray-200', 'text-gray-700');
                activeButton.classList.add('bg-blue-600', 'text-white');
            }
            
            // Show/hide account cards based on filter
            accountCards.forEach(card => {
                const accountType = card.getAttribute('data-account-type');
                
                if (type === 'all' || accountType === type) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Update branch sections visibility
            updateBranchSectionsVisibility();
            
            // Show/hide no results message
            showNoResultsMessage(type, visibleCount);
        }

        // Show no results message when filter returns no accounts
        function showNoResultsMessage(type, visibleCount) {
            let noResultsDiv = document.getElementById('no-results-message');
            
            if (visibleCount === 0 && type !== 'all') {
                if (!noResultsDiv) {
                    noResultsDiv = document.createElement('div');
                    noResultsDiv.id = 'no-results-message';
                    noResultsDiv.className = 'bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center mb-6';
                    
                    const typeLabel = type === 'receiver' ? 'Receiver' : 'Giver';
                    noResultsDiv.innerHTML = `
                        <div class="text-yellow-600 text-4xl mb-3">🔍</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No ${typeLabel} Accounts Found</h3>
                        <p class="text-gray-600">No mapped accounts found for ${typeLabel.toLowerCase()} accounts.</p>
                        <p class="text-gray-500 text-sm mt-2">Try selecting "All Accounts" or create new ${typeLabel.toLowerCase()} accounts.</p>
                    `;
                    
                    // Insert after the filter section
                    const filterSection = document.querySelector('.mb-6');
                    filterSection.parentNode.insertBefore(noResultsDiv, filterSection.nextSibling);
                }
                noResultsDiv.style.display = 'block';
            } else if (noResultsDiv) {
                noResultsDiv.style.display = 'none';
            }
        }

        // Update branch sections visibility based on visible accounts
        function updateBranchSectionsVisibility() {
            const branchSections = document.querySelectorAll('.mb-8');
            
            branchSections.forEach(section => {
                const visibleCards = section.querySelectorAll('.account-card[style*="block"], .account-card:not([style*="none"])');
                const hasVisibleAccounts = Array.from(visibleCards).some(card => 
                    card.style.display !== 'none' && getComputedStyle(card).display !== 'none'
                );
                
                if (hasVisibleAccounts) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });
        }

        // Initialize with all accounts visible
        document.addEventListener('DOMContentLoaded', function() {
            filterAccounts('all');
        });
    </script>
</x-app-shell>
