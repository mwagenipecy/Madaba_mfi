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
                            <!-- From Branch -->
                            <div>
                                <label for="from_branch_id" class="block text-sm font-medium text-gray-700 mb-2">From Branch</label>
                                <select name="from_branch_id" id="from_branch_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Select source branch</option>
                                    <option value="hq" {{ old('from_branch_id') == 'hq' ? 'selected' : '' }}>HQ (Main Office)</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('from_branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}{{ $branch->is_hq ? ' (HQ)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_branch_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- From Account -->
                            <div>
                                <label for="from_account_id" class="block text-sm font-medium text-gray-700 mb-2">From Account</label>
                                <select name="from_account_id" id="from_account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Select source account</option>
                                </select>
                                <div id="from_account_balance" class="mt-1 text-sm text-gray-600 hidden">
                                    Available Balance: <span id="from_balance_amount">TZS 0.00</span>
                                </div>
                                @error('from_account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- To Branch -->
                            <div>
                                <label for="to_branch_id" class="block text-sm font-medium text-gray-700 mb-2">To Branch</label>
                                <select name="to_branch_id" id="to_branch_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Select destination branch</option>
                                    <option value="hq" {{ old('to_branch_id') == 'hq' ? 'selected' : '' }}>HQ (Main Office)</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}{{ $branch->is_hq ? ' (HQ)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_branch_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- To Account -->
                            <div>
                                <label for="to_account_id" class="block text-sm font-medium text-gray-700 mb-2">To Account</label>
                                <select name="to_account_id" id="to_account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Select destination account</option>
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
        
        // Accounts data from server
        const accountsByBranch = @json($accountsByBranch);

        // Handle branch selection for source accounts
        document.getElementById('from_branch_id').addEventListener('change', function() {
            populateAccounts('from', this.value);
            validateAmount();
        });

        // Handle branch selection for destination accounts
        document.getElementById('to_branch_id').addEventListener('change', function() {
            populateAccounts('to', this.value);
        });

        // Handle account selection for source
        document.getElementById('from_account_id').addEventListener('change', function() {
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

            // Re-validate amount
            validateAmount();
        });

        // Handle account selection for destination
        document.getElementById('to_account_id').addEventListener('change', function() {
            validateAmount();
        });

        // Function to populate accounts based on selected branch
        function populateAccounts(type, branchId) {
            const selectElement = document.getElementById(type + '_account_id');
            const accounts = accountsByBranch[branchId] || [];
            
            // Clear existing options
            selectElement.innerHTML = '<option value="">Select ' + (type === 'from' ? 'source' : 'destination') + ' account</option>';
            
            // Add accounts for the selected branch
            accounts.forEach(account => {
                const option = document.createElement('option');
                option.value = account.id;
                option.setAttribute('data-balance', account.calculated_balance);
                option.textContent = `${account.name} (${account.account_type.name}) - Balance: TZS ${parseFloat(account.calculated_balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                selectElement.appendChild(option);
            });
            
            // Reset balance display for from account
            if (type === 'from') {
                document.getElementById('from_account_balance').classList.add('hidden');
                selectedFromBalance = 0;
            }
        }

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

            const fromBranchId = document.getElementById('from_branch_id').value;
            const fromAccountId = document.getElementById('from_account_id').value;
            const toBranchId = document.getElementById('to_branch_id').value;
            const toAccountId = document.getElementById('to_account_id').value;

            if (amountValue <= 0) {
                showError('Amount must be greater than 0');
                isValidAmount = false;
            } else if (amountValue > selectedFromBalance) {
                showError(`Insufficient balance. Available: TZS ${selectedFromBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
                isValidAmount = false;
            } else if (fromBranchId && toBranchId && fromAccountId && toAccountId && fromBranchId === toBranchId && fromAccountId === toAccountId) {
                showError('Source and destination accounts cannot be the same');
                isValidAmount = false;
            } else {
                hideError();
                isValidAmount = true;
            }

            // Enable/disable submit button - require all fields to be filled
            const allFieldsFilled = fromBranchId && fromAccountId && toBranchId && toAccountId && amountValue > 0;
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
