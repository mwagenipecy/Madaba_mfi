<x-app-shell title="Create Account Recharge" header="Create Account Recharge">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Capital Injection</h1>
                        <p class="text-gray-600 mt-1">Inject capital into the system from external giver accounts to main capital accounts</p>
                    </div>

                    <form method="POST" action="{{ route('payments.account-recharge.store') }}">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Source Giver Account -->
                            <div>
                                <label for="giver_account_id" class="block text-sm font-medium text-gray-700 mb-2">Source Giver Account</label>
                                
                                
                                <select name="giver_account_id" id="giver_account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Select giver account (money coming into system)</option>
                                    @if($giverAccounts && $giverAccounts->count() > 0)
                                        @foreach($giverAccounts as $account)
                                            <option value="{{ $account->id }}" 
                                                    data-balance="{{ $account->calculated_balance }}"
                                                    {{ old('giver_account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} ({{ $account->accountType->name }})
                                                - Balance: TZS {{ number_format($account->calculated_balance, 2) }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No giver accounts available</option>
                                    @endif
                                </select>
                                <div id="giver_account_balance" class="mt-1 text-sm text-gray-600 hidden">
                                    Available Balance: <span id="giver_balance_amount">TZS 0.00</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Giver accounts represent external sources that provide money to the system (should have negative/credit balance)
                                </p>
                                @error('giver_account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Destination Capital Account -->
                            <div>
                                <label for="capital_account_id" class="block text-sm font-medium text-gray-700 mb-2">Destination Capital Account</label>
                                
                                
                                <select name="capital_account_id" id="capital_account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Select capital account to receive funds</option>
                                    @if($capitalAccounts && $capitalAccounts->count() > 0)
                                        @foreach($capitalAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('capital_account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} ({{ $account->accountType->name }})
                                                - Balance: TZS {{ number_format($account->calculated_balance, 2) }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No capital accounts available</option>
                                    @endif
                                </select>
                                <p class="mt-1 text-sm text-gray-500">
                                    Capital accounts hold the organization's equity and capital (main organization accounts)
                                </p>
                                @error('capital_account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Capital Injection Amount -->
                            <div>
                                <label for="recharge_amount" class="block text-sm font-medium text-gray-700 mb-2">Capital Injection Amount (TZS)</label>
                                <input type="number" name="recharge_amount" id="recharge_amount" step="0.01" min="0.01" required 
                                       value="{{ old('recharge_amount') }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Enter capital injection amount">
                                <div id="amount_validation" class="mt-1 text-sm hidden">
                                    <span id="amount_error" class="text-red-600"></span>
                                </div>
                                @error('recharge_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" id="description" rows="3" required
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                          placeholder="Enter capital injection description (e.g., Initial capital injection, Additional funding, etc.)">{{ old('description') }}</textarea>
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
                                Submit Capital Injection Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedGiverBalance = 0;
        let isValidAmount = false;

        // Handle giver account selection
        document.getElementById('giver_account_id').addEventListener('change', function() {
            const giverValue = this.value;
            const giverBalanceDiv = document.getElementById('giver_account_balance');
            const giverBalanceAmount = document.getElementById('giver_balance_amount');
            
            // Update balance display
            if (giverValue) {
                const selectedOption = this.options[this.selectedIndex];
                selectedGiverBalance = parseFloat(selectedOption.getAttribute('data-balance')) || 0;
                giverBalanceAmount.textContent = 'TZS ' + selectedGiverBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                giverBalanceDiv.classList.remove('hidden');
            } else {
                giverBalanceDiv.classList.add('hidden');
                selectedGiverBalance = 0;
            }

            // Re-validate amount
            validateAmount();
        });

        // Handle capital account selection
        document.getElementById('capital_account_id').addEventListener('change', function() {
            validateAmount();
        });

        // Real-time amount validation
        document.getElementById('recharge_amount').addEventListener('input', function() {
            validateAmount();
        });

        function validateAmount() {
            const amountInput = document.getElementById('recharge_amount');
            const amountValue = parseFloat(amountInput.value) || 0;
            const validationDiv = document.getElementById('amount_validation');
            const errorSpan = document.getElementById('amount_error');
            const submitButton = document.getElementById('submit_button');

            const giverAccountId = document.getElementById('giver_account_id').value;
            const capitalAccountId = document.getElementById('capital_account_id').value;

            if (amountValue <= 0) {
                showError('Amount must be greater than 0');
                isValidAmount = false;
            } else if (giverAccountId && capitalAccountId && giverAccountId === capitalAccountId) {
                showError('Source and destination accounts cannot be the same');
                isValidAmount = false;
            } else {
                hideError();
                isValidAmount = true;
            }

            // Enable/disable submit button
            const allFieldsFilled = giverAccountId && capitalAccountId && amountValue > 0;
            submitButton.disabled = !isValidAmount || !allFieldsFilled;
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
