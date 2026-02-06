<x-app-shell title="Create Account Recharge" header="Create Account Recharge">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Capital Injection</h1>
                        <p class="text-gray-600 mt-1">Inject capital into the system from external giver accounts to main capital accounts</p>
                    </div>

                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex">
                                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex">
                                <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-red-800">{{ session('error') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($giverAccounts && $giverAccounts->count() == 0)
                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm text-yellow-800 font-medium">No Giver Accounts Available</p>
                                    <p class="text-sm text-yellow-700 mt-1">You need to create external accounts with "giver" type before you can create account recharges.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($capitalAccounts && $capitalAccounts->count() == 0)
                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm text-yellow-800 font-medium">No Capital Accounts Available</p>
                                    <p class="text-sm text-yellow-700 mt-1">You need to create main organization accounts (with main_category metadata) before you can create account recharges.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('payments.account-recharge.store') }}" id="rechargeForm">
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
                            <button type="submit" id="submit_button" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed" 
                                    @if(!$giverAccounts || $giverAccounts->count() == 0 || !$capitalAccounts || $capitalAccounts->count() == 0) disabled @endif>
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

        // Description validation
        const descriptionInput = document.getElementById('description');
        if (descriptionInput) {
            descriptionInput.addEventListener('input', function() {
                validateAmount();
            });
        }

        function validateAmount() {
            const amountInput = document.getElementById('recharge_amount');
            const amountValue = parseFloat(amountInput.value) || 0;
            const validationDiv = document.getElementById('amount_validation');
            const errorSpan = document.getElementById('amount_error');
            const submitButton = document.getElementById('submit_button');
            const descriptionInput = document.getElementById('description');

            const giverAccountId = document.getElementById('giver_account_id').value;
            const capitalAccountId = document.getElementById('capital_account_id').value;
            const descriptionValue = descriptionInput ? descriptionInput.value.trim() : '';

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

            // Enable/disable submit button - check all required fields
            const allFieldsFilled = giverAccountId && capitalAccountId && amountValue > 0 && descriptionValue.length > 0;
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

        // Form submission handler - allow server-side validation to catch errors
        document.getElementById('rechargeForm').addEventListener('submit', function(e) {
            const submitButton = document.getElementById('submit_button');
            const giverAccountId = document.getElementById('giver_account_id').value;
            const capitalAccountId = document.getElementById('capital_account_id').value;
            const amountValue = parseFloat(document.getElementById('recharge_amount').value) || 0;
            const descriptionValue = document.getElementById('description').value.trim();

            // Basic client-side check - but don't prevent if JavaScript validation fails
            // Server-side validation will catch any issues
            if (!giverAccountId || !capitalAccountId || amountValue <= 0 || !descriptionValue) {
                // Show error but let server handle it
                console.warn('Form validation warning - server will validate');
            }

            // Don't prevent submission - let server handle validation
            // This ensures form can submit even if JS validation has issues
        });
    </script>
</x-app-shell>
