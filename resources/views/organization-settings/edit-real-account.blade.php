<x-app-shell title="Edit Real Account Mapping" header="Edit Real Account Mapping">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Edit Real Account Mapping</h2>
                        <p class="text-gray-600">Update the details of your real account mapping to system accounts.</p>
                    </div>
                    
                    <form method="POST" action="{{ route('organization-settings.real-accounts.update', $realAccount) }}">
                        @csrf
                        @method('PUT')
                        
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
                                        <input type="text" name="external_account_name" value="{{ old('external_account_name', $realAccount->external_account_name) }}" required
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                               placeholder="e.g., Standard Bank Business Account">
                                        @error('external_account_name')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Real Account Number</label>
                                        <input type="text" name="external_account_id" value="{{ old('external_account_id', $realAccount->external_account_id) }}" required
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                               placeholder="e.g., 034000012345678">
                                        @error('external_account_id')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Provider Name</label>
                                        <input type="text" name="provider_name" value="{{ old('provider_name', $realAccount->provider_name) }}" required
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                               placeholder="e.g., Standard Bank, Vodacom M-Pesa">
                                        @error('provider_name')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Provider Type</label>
                                        <select name="provider_type" required
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            <option value="">Select Provider Type</option>
                                            <option value="bank" {{ old('provider_type', $realAccount->provider_type) == 'bank' ? 'selected' : '' }}>Bank</option>
                                            <option value="mno" {{ old('provider_type', $realAccount->provider_type) == 'mno' ? 'selected' : '' }}>Mobile Money Operator</option>
                                            <option value="payment_gateway" {{ old('provider_type', $realAccount->provider_type) == 'payment_gateway' ? 'selected' : '' }}>Payment Gateway</option>
                                            <option value="other" {{ old('provider_type', $realAccount->provider_type) == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('provider_type')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">API Endpoint (Optional)</label>
                                    <input type="url" name="api_endpoint" value="{{ old('api_endpoint', $realAccount->api_endpoint) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           placeholder="https://api.example.com/v1/balance">
                                    @error('api_endpoint')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
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
                                    <select name="account_id" required
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="">Select System Account to Map</option>
                                        @foreach($organization->accounts as $account)
                                            <option value="{{ $account->id }}" {{ old('account_id', $mapping ? $mapping->account_id : '') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} ({{ $account->account_number }}) - {{ $account->branch ? $account->branch->name : 'HQ' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Select which internal system account this real account should map to.
                                    </p>
                                    @error('account_id')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Mapping Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mapping Description</label>
                                <textarea name="mapping_description" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                          placeholder="Optional description explaining this mapping">{{ old('mapping_description', $mapping ? $mapping->mapping_description : '') }}</textarea>
                                @error('mapping_description')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('organization-settings.mapped-account-balances') }}"
                               class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                Update Mapping
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>
