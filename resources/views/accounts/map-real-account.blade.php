<x-app-shell title="Map Real Account" header="Map Account to Real Bank Account">
    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Account Information -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3">Account Information</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Name:</span>
                                <span class="font-medium">{{ $account->name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Account Number:</span>
                                <span class="font-mono">{{ $account->account_number }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Branch:</span>
                                <span class="font-medium">{{ $account->branch ? $account->branch->name : 'HQ' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Current Balance:</span>
                                <span class="font-bold {{ $account->balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($account->balance, 2) }} TZS
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Mapping Form -->
                    <form method="POST" action="{{ route('accounts.store-real-mapping', $account) }}">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Real Account Selection -->
                            <div>
                                <label for="real_account_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Real Bank Account
                                </label>
                                <select name="real_account_id" id="real_account_id" 
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select a real bank account to map...</option>
                                    @foreach($realAccounts as $realAccount)
                                        <option value="{{ $realAccount->id }}" 
                                                {{ old('real_account_id', $account->real_account_id) == $realAccount->id ? 'selected' : '' }}>
                                            {{ $realAccount->provider_name }} - {{ $realAccount->external_account_name ?? $realAccount->external_account_id }}
                                            @if($realAccount->account)
                                                ({{ $realAccount->account->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('real_account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    Select which real bank/MNO account this system account should be mapped to.
                                </p>
                            </div>

                            <!-- Mapping Description -->
                            <div>
                                <label for="mapping_description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Mapping Description
                                </label>
                                <textarea name="mapping_description" id="mapping_description" rows="3"
                                          class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                          placeholder="Describe the relationship between this system account and the real account...">{{ old('mapping_description', $account->mapping_description) }}</textarea>
                                @error('mapping_description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    Optional description explaining how this system account relates to the real bank account.
                                </p>
                            </div>
                        </div>

                        <!-- Current Mapping Info (if exists) -->
                        @if($account->mappedRealAccount)
                            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <h4 class="text-sm font-semibold text-blue-900 mb-2">Current Mapping</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-blue-700">Provider:</span>
                                        <span class="font-medium text-blue-900">{{ $account->mappedRealAccount->provider_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-700">Account:</span>
                                        <span class="font-medium text-blue-900">{{ $account->mappedRealAccount->external_account_name ?? $account->mappedRealAccount->external_account_id }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-700">Real Balance:</span>
                                        <span class="font-bold {{ $account->mappedRealAccount->last_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format($account->mappedRealAccount->last_balance, 2) }} TZS
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-700">Last Sync:</span>
                                        <span class="text-blue-600">
                                            {{ $account->mappedRealAccount->last_sync_at ? $account->mappedRealAccount->last_sync_at->format('M d, Y H:i') : 'Never' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3 mt-8">
                            <a href="{{ route('accounts.show', $account) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Mapping
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>
