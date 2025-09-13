<x-app-shell title="Create Fund Transfer" header="Create Fund Transfer">
    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Create Fund Transfer</h1>
                        <p class="text-gray-600 mt-1">Transfer money between accounts with approval workflow</p>
                    </div>

                    <form method="POST" action="{{ route('payments.fund-transfer.store') }}">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- From Account -->
                            <div>
                                <label for="from_account_id" class="block text-sm font-medium text-gray-700 mb-2">From Account</label>
                                <select name="from_account_id" id="from_account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Select source account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('from_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->accountType->name }})
                                            @if($account->branch)
                                                - {{ $account->branch->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- To Account -->
                            <div>
                                <label for="to_account_id" class="block text-sm font-medium text-gray-700 mb-2">To Account</label>
                                <select name="to_account_id" id="to_account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Select destination account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->accountType->name }})
                                            @if($account->branch)
                                                - {{ $account->branch->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (TZS)</label>
                                <input type="number" name="amount" id="amount" step="0.01" min="0.01" required 
                                       value="{{ old('amount') }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Enter transfer amount">
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" id="description" rows="3" required
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                          placeholder="Enter transfer description">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('payments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                Submit Transfer Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prevent selecting the same account for both from and to
        document.getElementById('from_account_id').addEventListener('change', function() {
            const toSelect = document.getElementById('to_account_id');
            const fromValue = this.value;
            
            Array.from(toSelect.options).forEach(option => {
                if (option.value === fromValue) {
                    option.disabled = true;
                    option.style.display = 'none';
                } else {
                    option.disabled = false;
                    option.style.display = 'block';
                }
            });
        });

        document.getElementById('to_account_id').addEventListener('change', function() {
            const fromSelect = document.getElementById('from_account_id');
            const toValue = this.value;
            
            Array.from(fromSelect.options).forEach(option => {
                if (option.value === toValue) {
                    option.disabled = true;
                    option.style.display = 'none';
                } else {
                    option.disabled = false;
                    option.style.display = 'block';
                }
            });
        });
    </script>
</x-app-shell>
