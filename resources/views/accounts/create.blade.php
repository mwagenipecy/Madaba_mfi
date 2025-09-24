<x-app-shell title="Create Account" header="Create Account">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Account</h1>
                    <p class="text-gray-600 mt-1">Create a new account for the organization or specific branch</p>
                </div>
                <a href="{{ route('accounts.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Back to Accounts
                </a>
            </div>
        </div>

        <!-- Create Account Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ route('accounts.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Account Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Account Name</label>
                        <input type="text" id="name" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter account name">
                    </div>

                    <!-- Account Type -->
                    <div>
                        <label for="account_type_id" class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
                        <select id="account_type_id" name="account_type_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select account type</option>
                            @foreach($accountTypes as $accountType)
                                <option value="{{ $accountType->id }}">{{ $accountType->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Organization (default to user's organization) -->
                    <div>
                        <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-2">Organization</label>
                        <select id="organization_id" name="organization_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            @foreach($organizations as $organization)
                                <option value="{{ $organization->id }}" {{ $organizationId == $organization->id ? 'selected' : '' }}>{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Branch (Optional, only branches in user's organization) -->
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch (Optional)</label>
                        <select id="branch_id" name="branch_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select branch (leave empty for main account)</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Parent Account (for sub-accounts) -->
                    <div>
                        <label for="parent_account_id" class="block text-sm font-medium text-gray-700 mb-2">Parent Account (for Sub-Account)</label>
                        <select id="parent_account_id" name="parent_account_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">None (Create main category account)</option>
                            @foreach($parentAccounts as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->account_number }})</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Selecting a parent makes this a sub-account for reporting.</p>
                    </div>

                    <!-- Opening Balance -->
                    <div>
                        <label for="opening_balance" class="block text-sm font-medium text-gray-700 mb-2">Opening Balance</label>
                        <input type="number" id="opening_balance" name="opening_balance" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="0.00">
                    </div>

                    <!-- Currency -->
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                        <select id="currency" name="currency" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="TZS" selected>TZS (Tanzanian Shilling)</option>
                            <option value="TZS">TZS (Tanzanian Shilling)</option>
                            <option value="EUR">EUR (Euro)</option>
                            <option value="GBP">GBP (British Pound)</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                              placeholder="Enter account description"></textarea>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('accounts.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-shell>
