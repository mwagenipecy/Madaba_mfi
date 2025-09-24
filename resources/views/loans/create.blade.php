<x-app-shell title="Create Loan" header="Create New Loan">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Create New Loan</h1>
                        <a href="{{ route('loans.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Back to Loans
                        </a>
                    </div>
                    
                    <form method="POST" action="{{ route('loans.store') }}" class="space-y-6">
                        @csrf
                        <!-- Client Selection -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Client Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">Select Client *</label>
                                    <select name="client_id" id="client_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" required>
                                        <option value="">Choose a client...</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->first_name }} {{ $client->last_name }} ({{ $client->client_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="loan_officer_id" class="block text-sm font-medium text-gray-700 mb-1">Loan Officer</label>
                                    <select name="loan_officer_id" id="loan_officer_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="">Select loan officer...</option>
                                        @foreach($loanOfficers as $officer)
                                            <option value="{{ $officer->id }}" {{ old('loan_officer_id') == $officer->id ? 'selected' : '' }}>
                                                {{ $officer->first_name }} {{ $officer->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('loan_officer_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization and Branch Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization & Branch</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                                    <div class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900">
                                        {{ $userOrganization->name }}
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Loan will be created under your organization</p>
                                </div>
                                <div>
                                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                    <select name="branch_id" id="branch_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 {{ $errors->has('branch_id') ? 'border-red-500' : '' }}">
                                        <option value="">Select branch (optional)</option>
                                        @forelse($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }} - {{ $branch->city }}
                                            </option>
                                        @empty
                                            <option value="" disabled>No branches available for your organization</option>
                                        @endforelse
                                    </select>
                                    @error('branch_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @if($branches->isEmpty())
                                        <p class="mt-1 text-xs text-amber-600">No branches are set up for your organization yet</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Loan Details -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="loan_product_id" class="block text-sm font-medium text-gray-700 mb-1">Loan Product *</label>
                                    <select name="loan_product_id" id="loan_product_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" required>
                                        <option value="">Select loan product...</option>
                                        @foreach($loanProducts as $product)
                                            <option value="{{ $product->id }}" {{ old('loan_product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->interest_rate }}% - {{ $product->min_tenure_months }}-{{ $product->max_tenure_months }} months)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('loan_product_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="loan_amount" class="block text-sm font-medium text-gray-700 mb-1">Loan Amount (TZS) *</label>
                                    <input type="number" name="loan_amount" id="loan_amount" step="0.01" min="0" 
                                           value="{{ old('loan_amount') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                           placeholder="0.00" required>
                                    @error('loan_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="interest_rate" class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%)</label>
                                    <input type="number" name="interest_rate" id="interest_rate" step="0.01" min="0" max="100" 
                                           value="{{ old('interest_rate') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                           placeholder="0.00" readonly>
                                </div>
                                <div>
                                    <label for="loan_tenure_months" class="block text-sm font-medium text-gray-700 mb-1">Loan Tenure (Months)</label>
                                    <input type="number" name="loan_tenure_months" id="loan_tenure_months" min="1" 
                                           value="{{ old('loan_tenure_months') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                           placeholder="12" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fees and Charges -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Fees and Charges</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="processing_fee" class="block text-sm font-medium text-gray-700 mb-1">Processing Fee (TZS)</label>
                                    <input type="number" name="processing_fee" id="processing_fee" step="0.01" min="0" 
                                           value="{{ old('processing_fee') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                           placeholder="0.00">
                                </div>
                                <div>
                                    <label for="insurance_fee" class="block text-sm font-medium text-gray-700 mb-1">Insurance Fee (TZS)</label>
                                    <input type="number" name="insurance_fee" id="insurance_fee" step="0.01" min="0" 
                                           value="{{ old('insurance_fee') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                           placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Loan Purpose</label>
                                    <textarea name="purpose" id="purpose" rows="3" 
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                              placeholder="Describe the purpose of this loan...">{{ old('purpose') }}</textarea>
                                    @error('purpose')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="requires_collateral" id="requires_collateral" value="1" 
                                           {{ old('requires_collateral') ? 'checked' : '' }}
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                    <label for="requires_collateral" class="ml-2 block text-sm text-gray-900">Requires Collateral</label>
                                </div>
                                <div id="collateral_details" class="hidden space-y-4">
                                    <div>
                                        <label for="collateral_description" class="block text-sm font-medium text-gray-700 mb-1">Collateral Description</label>
                                        <textarea name="collateral_description" id="collateral_description" rows="2" 
                                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                                  placeholder="Describe the collateral...">{{ old('collateral_description') }}</textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="collateral_value" class="block text-sm font-medium text-gray-700 mb-1">Collateral Value (TZS)</label>
                                            <input type="number" name="collateral_value" id="collateral_value" step="0.01" min="0" 
                                                   value="{{ old('collateral_value') }}"
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                                   placeholder="0.00">
                                        </div>
                                        <div>
                                            <label for="collateral_location" class="block text-sm font-medium text-gray-700 mb-1">Collateral Location</label>
                                            <input type="text" name="collateral_location" id="collateral_location" 
                                                   value="{{ old('collateral_location') }}"
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                                   placeholder="Location of collateral">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('loans.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                Create Loan Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Loan product data
        const loanProducts = @json($loanProducts);
        
        // Handle collateral checkbox
        document.getElementById('requires_collateral').addEventListener('change', function() {
            const collateralDetails = document.getElementById('collateral_details');
            if (this.checked) {
                collateralDetails.classList.remove('hidden');
            } else {
                collateralDetails.classList.add('hidden');
            }
        });
        
        // Handle loan product selection
        document.getElementById('loan_product_id').addEventListener('change', function() {
            const productId = this.value;
            const product = loanProducts.find(p => p.id == productId);
            
            if (product) {
                // Auto-populate interest rate and tenure
                document.getElementById('interest_rate').value = product.interest_rate;
                document.getElementById('loan_tenure_months').value = product.min_tenure_months;
                
                // Set min/max values for amount
                document.getElementById('loan_amount').min = product.min_amount;
                document.getElementById('loan_amount').max = product.max_amount;
                
                // Set min/max values for tenure
                document.getElementById('loan_tenure_months').min = product.min_tenure_months;
                document.getElementById('loan_tenure_months').max = product.max_tenure_months;
                
                // Auto-populate processing fee if available
                if (product.processing_fee) {
                    document.getElementById('processing_fee').value = product.processing_fee;
                }
            } else {
                // Clear fields if no product selected
                document.getElementById('interest_rate').value = '';
                document.getElementById('loan_tenure_months').value = '';
                document.getElementById('loan_amount').min = 0;
                document.getElementById('loan_amount').max = '';
                document.getElementById('loan_tenure_months').min = 1;
                document.getElementById('loan_tenure_months').max = '';
            }
        });
        
        // Initialize collateral section if checkbox is checked
        if (document.getElementById('requires_collateral').checked) {
            document.getElementById('collateral_details').classList.remove('hidden');
        }
    </script>
</x-app-shell>

