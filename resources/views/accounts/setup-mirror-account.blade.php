<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Setup Mirror Account for {{ $account->name }}
            </h2>
            <a href="{{ route('accounts.show', $account) }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                ← Back to Account
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
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

                    <!-- Setup Form -->
                    <form method="POST" action="{{ route('accounts.store-mirror', $account) }}">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Mirror Real Account Selection -->
                            <div>
                                <label for="mirror_real_account_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Mirror Real Account
                                </label>
                                <select name="mirror_real_account_id" id="mirror_real_account_id" 
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select a real account to mirror...</option>
                                    @foreach($realAccounts as $realAccount)
                                        <option value="{{ $realAccount->id }}" 
                                                {{ old('mirror_real_account_id', $account->mirror_real_account_id) == $realAccount->id ? 'selected' : '' }}>
                                            {{ $realAccount->provider_name }} - {{ $realAccount->external_account_name ?? $realAccount->external_account_id }}
                                            @if($realAccount->account)
                                                ({{ $realAccount->account->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('mirror_real_account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    Select which real bank/MNO account this system account should mirror.
                                </p>
                            </div>

                            <!-- Main Entry Account -->
                            <div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_main_entry_account" id="is_main_entry_account" value="1"
                                           {{ old('is_main_entry_account', $account->is_main_entry_account) ? 'checked' : '' }}
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="is_main_entry_account" class="ml-2 block text-sm font-medium text-gray-700">
                                        This is a main entry account
                                    </label>
                                </div>
                                @error('is_main_entry_account')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    Check this if this account should be the primary entry point for this branch's transactions.
                                    Only one main entry account is allowed per branch.
                                </p>
                            </div>

                            <!-- Mirror Description -->
                            <div>
                                <label for="mirror_description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Mirror Description
                                </label>
                                <textarea name="mirror_description" id="mirror_description" rows="3"
                                          class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                          placeholder="Describe the relationship between this system account and the real account...">{{ old('mirror_description', $account->mirror_description) }}</textarea>
                                @error('mirror_description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    Optional description explaining how this system account relates to the real bank account.
                                </p>
                            </div>
                        </div>

                        <!-- Current Mirror Account Info (if exists) -->
                        @if($account->mirrorRealAccount)
                            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <h4 class="text-sm font-semibold text-blue-900 mb-2">Current Mirror Account</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-blue-700">Provider:</span>
                                        <span class="font-medium text-blue-900">{{ $account->mirrorRealAccount->provider_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-700">Account:</span>
                                        <span class="font-medium text-blue-900">{{ $account->mirrorRealAccount->external_account_name ?? $account->mirrorRealAccount->external_account_id }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-700">Last Balance:</span>
                                        <span class="font-bold {{ $account->mirrorRealAccount->last_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format($account->mirrorRealAccount->last_balance, 2) }} TZS
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-700">Last Sync:</span>
                                        <span class="text-blue-600">
                                            {{ $account->mirrorRealAccount->last_sync_at ? $account->mirrorRealAccount->last_sync_at->format('M d, Y H:i') : 'Never' }}
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
                                Update Mirror Setup
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
