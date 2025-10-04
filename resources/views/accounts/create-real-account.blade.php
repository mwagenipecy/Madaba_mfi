<x-app-shell title="Connect External Account" header="Connect External Account">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Connect External Account</h1>
                        <div class="flex space-x-3">
                            <a href="{{ route('accounts.show', $account) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Back to Account
                            </a>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                <div class="text-sm text-gray-900">{{ $account->name }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                <div class="text-sm font-mono text-gray-900">{{ $account->account_number }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Connect External Account Form -->
                    <form action="{{ route('accounts.real.store', $account) }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Provider Type -->
                            <div>
                                <label for="provider_type" class="block text-sm font-medium text-gray-700 mb-2">Provider Type</label>
                                <select id="provider_type" name="provider_type" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">Select provider type</option>
                                    <option value="mno" {{ old('provider_type') === 'mno' ? 'selected' : '' }}>Mobile Network Operator (MNO)</option>
                                    <option value="bank" {{ old('provider_type') === 'bank' ? 'selected' : '' }}>Bank</option>
                                    <option value="payment_gateway" {{ old('provider_type') === 'payment_gateway' ? 'selected' : '' }}>Payment Gateway</option>
                                </select>
                                @error('provider_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Provider Name -->
                            <div>
                                <label for="provider_name" class="block text-sm font-medium text-gray-700 mb-2">Provider Name</label>
                                <input type="text" id="provider_name" name="provider_name" value="{{ old('provider_name') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="e.g., Vodacom, CRDB Bank, etc.">
                                @error('provider_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- External Account ID -->
                            <div>
                                <label for="external_account_id" class="block text-sm font-medium text-gray-700 mb-2">External Account ID</label>
                                <input type="text" id="external_account_id" name="external_account_id" value="{{ old('external_account_id') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="External system account identifier">
                                @error('external_account_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- External Account Name -->
                            <div>
                                <label for="external_account_name" class="block text-sm font-medium text-gray-700 mb-2">External Account Name</label>
                                <input type="text" id="external_account_name" name="external_account_name" value="{{ old('external_account_name') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Account name in external system">
                                @error('external_account_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- API Endpoint -->
                            <div class="md:col-span-2">
                                <label for="api_endpoint" class="block text-sm font-medium text-gray-700 mb-2">API Endpoint</label>
                                <input type="url" id="api_endpoint" name="api_endpoint" value="{{ old('api_endpoint') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="https://api.example.com/v1/balance">
                                @error('api_endpoint') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- API Credentials -->
                        <div class="bg-blue-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">API Credentials</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="api_username" class="block text-sm font-medium text-gray-700 mb-2">Username/API Key</label>
                                    <input type="text" id="api_username" name="api_credentials[username]" value="{{ old('api_credentials.username') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           placeholder="API username or key">
                                </div>
                                <div>
                                    <label for="api_password" class="block text-sm font-medium text-gray-700 mb-2">Password/Secret</label>
                                    <input type="password" id="api_password" name="api_credentials[password]" value="{{ old('api_credentials.password') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           placeholder="API password or secret">
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">These credentials will be encrypted and stored securely.</p>
                        </div>

                        <!-- Additional Metadata -->
                        <div>
                            <label for="provider_metadata" class="block text-sm font-medium text-gray-700 mb-2">Additional Configuration (JSON)</label>
                            <textarea id="provider_metadata" name="provider_metadata" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      placeholder='{"timeout": 30, "retry_attempts": 3, "custom_field": "value"}'>{{ old('provider_metadata') }}</textarea>
                            @error('provider_metadata') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <p class="text-sm text-gray-600 mt-1">Optional JSON configuration for additional settings.</p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('accounts.show', $account) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                Connect Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>





