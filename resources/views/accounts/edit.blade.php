<x-app-shell title="Edit Account" header="Edit Account">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Edit Account</h1>
                        <div class="flex space-x-3">
                            <a href="{{ route('accounts.show', $account) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                View Account
                            </a>
                            <a href="{{ route('accounts.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Back to Accounts
                            </a>
                        </div>
                    </div>

                    <!-- Edit Account Form -->
                    <form action="{{ route('accounts.update', $account) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Account Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $account->name) }}" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Enter account name">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="status" name="status" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="active" {{ old('status', $account->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $account->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status', $account->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="closed" {{ old('status', $account->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      placeholder="Enter account description">{{ old('description', $account->description) }}</textarea>
                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Read-only Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information (Read-only)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                    <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm font-mono text-gray-700">
                                        {{ $account->account_number }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                                    <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-700">
                                        {{ $account->accountType->name ?? 'N/A' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                    <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-700">
                                        {{ $account->currency }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Balance</label>
                                    <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700">
                                        TZS {{ number_format($account->balance, 2) }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                                    <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-700">
                                        {{ $account->organization->name ?? 'N/A' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                    <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-700">
                                        {{ $account->branch->name ?? 'Main Account' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('accounts.show', $account) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                Update Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>




