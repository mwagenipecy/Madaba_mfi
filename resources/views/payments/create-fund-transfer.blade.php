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
                                        <option value="{{ $account->id }}" 
                                                data-balance="{{ $account->calculated_balance }}"
                                                {{ old('from_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->accountType->name }})
                                            @if($account->branch)
                                                - {{ $account->branch->name }}
                                            @endif
                                            - Balance: TZS {{ number_format($account->calculated_balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="from_account_balance" class="mt-1 text-sm text-gray-600 hidden">
                                    Available Balance: <span id="from_balance_amount">TZS 0.00</span>
                                </div>
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
                                <div id="amount_validation" class="mt-1 text-sm hidden">
                                    <span id="amount_error" class="text-red-600"></span>
                                </div>
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
                            <button type="submit" id="submit_button" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                                Submit Transfer Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedFromBalance = 0;
        let isValidAmount = false;

        // Prevent selecting the same account for both from and to
        document.getElementById('from_account_id').addEventListener('change', function() {
            const toSelect = document.getElementById('to_account_id');
            const fromValue = this.value;
            const fromBalanceDiv = document.getElementById('from_account_balance');
            const fromBalanceAmount = document.getElementById('from_balance_amount');
            
            // Update balance display
            if (fromValue) {
                const selectedOption = this.options[this.selectedIndex];
                selectedFromBalance = parseFloat(selectedOption.getAttribute('data-balance')) || 0;
                fromBalanceAmount.textContent = 'TZS ' + selectedFromBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                fromBalanceDiv.classList.remove('hidden');
            } else {
                fromBalanceDiv.classList.add('hidden');
                selectedFromBalance = 0;
            }
            
            // Prevent selecting same account
            Array.from(toSelect.options).forEach(option => {
                if (option.value === fromValue) {
                    option.disabled = true;
                    option.style.display = 'none';
                } else {
                    option.disabled = false;
                    option.style.display = 'block';
                }
            });

            // Re-validate amount
            validateAmount();
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

        // Real-time amount validation
        document.getElementById('amount').addEventListener('input', function() {
            validateAmount();
        });

        function validateAmount() {
            const amountInput = document.getElementById('amount');
            const amountValue = parseFloat(amountInput.value) || 0;
            const validationDiv = document.getElementById('amount_validation');
            const errorSpan = document.getElementById('amount_error');
            const submitButton = document.getElementById('submit_button');

            if (amountValue <= 0) {
                showError('Amount must be greater than 0');
                isValidAmount = false;
            } else if (amountValue > selectedFromBalance) {
                showError(`Insufficient balance. Available: TZS ${selectedFromBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
                isValidAmount = false;
            } else {
                hideError();
                isValidAmount = true;
            }

            // Enable/disable submit button
            submitButton.disabled = !isValidAmount || !document.getElementById('from_account_id').value || !document.getElementById('to_account_id').value;
        }

        function showError(message) {
            const validationDiv = document.getElementById('amount_validation');
            const errorSpan = document.getElementById('amount_error');
            errorSpan.textContent = message;
            validationDiv.classList.remove('hidden');
        }

        function hideError() {
            const validationDiv = document.getElementById('amount_validation');
            validationDiv.classList.add('hidden');
        }

        // Initial validation
        validateAmount();
    </script>
</x-app-shell>
