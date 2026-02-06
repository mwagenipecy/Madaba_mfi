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
                    
                    <form method="POST" action="{{ route('loans.store') }}" id="loan_form" class="space-y-6">
                        @csrf
                        <!-- Client Selection -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Client Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">Select Client *</label>
                                    <div class="relative">
                                        <input type="text" id="client_search" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                               placeholder="Search client by name, phone, or client number..."
                                               autocomplete="off">
                                        <div id="client_dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                            <!-- Client options will be populated here -->
                                        </div>
                                        <input type="hidden" name="client_id" id="client_id" value="{{ old('client_id') }}" required>
                                        <div id="selected_client_display" class="mt-2 p-2 bg-green-50 border border-green-200 rounded-lg hidden">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-900" id="selected_client_name"></span>
                                                <button type="button" onclick="clearClientSelection()" class="text-red-600 hover:text-red-800 text-sm">Clear</button>
                                            </div>
                                        </div>
                                    </div>
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
                                    <label for="loan_tenure" id="loan_tenure_label" class="block text-sm font-medium text-gray-700 mb-1">Loan Tenure</label>
                                    <input type="number" name="loan_tenure_value" id="loan_tenure_value" min="1" 
                                           value="{{ old('loan_tenure_value') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                           placeholder="12" required>
                                    <input type="hidden" name="loan_tenure_months" id="loan_tenure_months" value="{{ old('loan_tenure_months') }}">
                                    <input type="hidden" name="tenure_unit" id="tenure_unit" value="months">
                                    <p class="mt-1 text-xs text-gray-500" id="tenure_hint"></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fees and Charges -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Fees and Charges</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="processing_fee" id="processing_fee_label" class="block text-sm font-medium text-gray-700 mb-1">Processing Fee</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none" id="processing_fee_prefix">
                                            <span class="text-gray-500 sm:text-sm">TZS</span>
                                        </div>
                                        <input type="number" name="processing_fee" id="processing_fee" step="0.01" min="0" max="100"
                                               value="{{ old('processing_fee') }}"
                                               class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
                                               placeholder="0.00">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none hidden" id="processing_fee_suffix">
                                            <span class="text-gray-500 sm:text-sm">%</span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500" id="processing_fee_hint"></p>
                                    <input type="hidden" name="processing_fee_amount" id="processing_fee_amount" value="0">
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
        // Client data for search
        const clients = @json($clients);
        
        // Loan product data
        const loanProducts = @json($loanProducts);
        
        // Client search functionality
        const clientSearch = document.getElementById('client_search');
        const clientDropdown = document.getElementById('client_dropdown');
        const clientIdInput = document.getElementById('client_id');
        const selectedClientDisplay = document.getElementById('selected_client_display');
        const selectedClientName = document.getElementById('selected_client_name');
        
        // Filter clients based on search
        function filterClients(searchTerm) {
            if (!searchTerm || searchTerm.length < 1) {
                clientDropdown.classList.add('hidden');
                return;
            }
            
            const filtered = clients.filter(client => {
                const searchLower = searchTerm.toLowerCase();
                const name = `${client.first_name || ''} ${client.last_name || ''} ${client.middle_name || ''}`.toLowerCase();
                const phone = (client.phone_number || '').toLowerCase();
                const clientNumber = (client.client_number || '').toLowerCase();
                const email = (client.email || '').toLowerCase();
                
                return name.includes(searchLower) || 
                       phone.includes(searchLower) || 
                       clientNumber.includes(searchLower) ||
                       email.includes(searchLower);
            });
            
            displayClientOptions(filtered);
        }
        
        // Display client options in dropdown
        function displayClientOptions(filteredClients) {
            if (filteredClients.length === 0) {
                clientDropdown.innerHTML = '<div class="p-3 text-sm text-gray-500">No clients found</div>';
                clientDropdown.classList.remove('hidden');
                return;
            }
            
            let html = '';
            filteredClients.forEach(client => {
                const displayName = `${client.first_name || ''} ${client.last_name || ''}${client.middle_name ? ' ' + client.middle_name : ''}`.trim();
                const clientInfo = `${client.client_number || ''}${client.phone_number ? ' • ' + client.phone_number : ''}`;
                html += `
                    <div class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0 client-option" 
                         data-id="${client.id}" 
                         data-name="${displayName}">
                        <div class="font-medium text-gray-900">${displayName}</div>
                        <div class="text-xs text-gray-500">${clientInfo}</div>
                    </div>
                `;
            });
            
            clientDropdown.innerHTML = html;
            clientDropdown.classList.remove('hidden');
            
            // Add click handlers
            document.querySelectorAll('.client-option').forEach(option => {
                option.addEventListener('click', function() {
                    const clientId = this.getAttribute('data-id');
                    const clientName = this.getAttribute('data-name');
                    selectClient(clientId, clientName);
                });
            });
        }
        
        // Select a client
        function selectClient(clientId, clientName) {
            clientIdInput.value = clientId;
            clientSearch.value = clientName;
            selectedClientName.textContent = clientName;
            selectedClientDisplay.classList.remove('hidden');
            clientDropdown.classList.add('hidden');
        }
        
        // Clear client selection
        function clearClientSelection() {
            clientIdInput.value = '';
            clientSearch.value = '';
            selectedClientDisplay.classList.add('hidden');
        }
        
        // Client search event listeners
        clientSearch.addEventListener('input', function() {
            filterClients(this.value);
        });
        
        clientSearch.addEventListener('focus', function() {
            if (this.value) {
                filterClients(this.value);
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!clientSearch.contains(e.target) && !clientDropdown.contains(e.target)) {
                clientDropdown.classList.add('hidden');
            }
        });
        
        // Initialize selected client if old value exists
        @if(old('client_id'))
            const oldClient = clients.find(c => c.id == {{ old('client_id') }});
            if (oldClient) {
                const displayName = `${oldClient.first_name || ''} ${oldClient.last_name || ''}`.trim();
                selectClient(oldClient.id, displayName);
            }
        @endif
        
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
            const tenureInput = document.getElementById('loan_tenure_months');
            const tenureLabel = document.getElementById('loan_tenure_label');
            const tenureHint = document.getElementById('tenure_hint');
            
            if (product) {
                // Auto-populate interest rate
                document.getElementById('interest_rate').value = product.interest_rate;
                
                // Set min/max values for amount
                document.getElementById('loan_amount').min = product.min_amount;
                document.getElementById('loan_amount').max = product.max_amount;
                
                // Update tenure based on repayment frequency
                const frequency = product.repayment_frequency || 'monthly';
                const tenureValueInput = document.getElementById('loan_tenure_value');
                const tenureMonthsInput = document.getElementById('loan_tenure_months');
                const tenureUnitInput = document.getElementById('tenure_unit');
                let minTenure, maxTenure, unit, label;
                
                if (frequency === 'daily') {
                    // Convert months to days (approximate: 1 month = 30 days)
                    minTenure = product.min_tenure_months * 30;
                    maxTenure = product.max_tenure_months * 30;
                    unit = 'days';
                    label = 'Loan Tenure (Days)';
                } else if (frequency === 'weekly') {
                    // Convert months to weeks (approximate: 1 month = 4 weeks)
                    minTenure = product.min_tenure_months * 4;
                    maxTenure = product.max_tenure_months * 4;
                    unit = 'weeks';
                    label = 'Loan Tenure (Weeks)';
                } else {
                    // Monthly (default)
                    minTenure = product.min_tenure_months;
                    maxTenure = product.max_tenure_months;
                    unit = 'months';
                    label = 'Loan Tenure (Months)';
                }
                
                tenureValueInput.min = minTenure;
                tenureValueInput.max = maxTenure;
                tenureValueInput.value = minTenure;
                tenureValueInput.placeholder = minTenure.toString();
                tenureLabel.textContent = label;
                tenureHint.textContent = `Range: ${minTenure} - ${maxTenure} ${unit}`;
                tenureUnitInput.value = unit;
                
                // Calculate and set the months value
                updateTenureMonths();
                
                // Update months value when user changes tenure
                tenureValueInput.addEventListener('input', updateTenureMonths);
                
                function updateTenureMonths() {
                    const value = parseFloat(tenureValueInput.value) || 0;
                    let monthsValue = value;
                    
                    if (unit === 'days') {
                        monthsValue = value / 30; // Convert days to months
                    } else if (unit === 'weeks') {
                        monthsValue = value / 4; // Convert weeks to months
                    }
                    // For months, value is already in months
                    
                    tenureMonthsInput.value = Math.round(monthsValue * 100) / 100; // Round to 2 decimal places
                }
                
                // Update processing fee display based on product settings
                updateProcessingFeeDisplay(product);
            } else {
                // Clear fields if no product selected
                document.getElementById('interest_rate').value = '';
                document.getElementById('loan_tenure_value').value = '';
                document.getElementById('loan_tenure_months').value = '';
                document.getElementById('loan_amount').min = 0;
                document.getElementById('loan_amount').max = '';
                document.getElementById('loan_tenure_value').min = 1;
                document.getElementById('loan_tenure_value').max = '';
                tenureLabel.textContent = 'Loan Tenure';
                tenureHint.textContent = '';
            }
        });
        
        // Initialize collateral section if checkbox is checked
        if (document.getElementById('requires_collateral').checked) {
            document.getElementById('collateral_details').classList.remove('hidden');
        }
        
        // Initialize loan product if old value exists
        @if(old('loan_product_id'))
            document.getElementById('loan_product_id').dispatchEvent(new Event('change'));
        @endif
        
        // Update processing fee display based on product
        function updateProcessingFeeDisplay(product) {
            const feeInput = document.getElementById('processing_fee');
            const feeLabel = document.getElementById('processing_fee_label');
            const feePrefix = document.getElementById('processing_fee_prefix');
            const feeSuffix = document.getElementById('processing_fee_suffix');
            const feeHint = document.getElementById('processing_fee_hint');
            const feeAmountInput = document.getElementById('processing_fee_amount');
            const loanAmountInput = document.getElementById('loan_amount');
            
            const feeType = product.processing_fee_type || 'fixed';
            const feeValue = product.processing_fee || 0;
            
            if (feeType === 'percent') {
                feeLabel.textContent = 'Processing Fee (%)';
                feePrefix.classList.add('hidden');
                feeSuffix.classList.remove('hidden');
                feeInput.classList.remove('pl-12');
                feeInput.classList.add('pr-8');
                feeInput.max = 100;
                feeInput.value = feeValue;
                feeInput.placeholder = '0.00';
                feeHint.textContent = 'Percentage of loan amount';
            } else {
                feeLabel.textContent = 'Processing Fee (TZS)';
                feePrefix.classList.remove('hidden');
                feeSuffix.classList.add('hidden');
                feeInput.classList.remove('pr-8');
                feeInput.classList.add('pl-12');
                feeInput.removeAttribute('max');
                feeInput.value = feeValue;
                feeInput.placeholder = '0.00';
                feeHint.textContent = 'Fixed amount';
            }
            
            // Calculate fee amount
            calculateProcessingFee();
        }
        
        // Calculate processing fee amount based on type
        function calculateProcessingFee() {
            const feeInput = document.getElementById('processing_fee');
            const feeAmountInput = document.getElementById('processing_fee_amount');
            const loanAmountInput = document.getElementById('loan_amount');
            const loanProductSelect = document.getElementById('loan_product_id');
            
            const loanAmount = parseFloat(loanAmountInput.value) || 0;
            const feeValue = parseFloat(feeInput.value) || 0;
            const product = loanProducts.find(p => p.id == loanProductSelect.value);
            
            if (product && loanAmount > 0) {
                const feeType = product.processing_fee_type || 'fixed';
                let feeAmount = 0;
                
                if (feeType === 'percent') {
                    feeAmount = (loanAmount * feeValue) / 100;
                } else {
                    feeAmount = feeValue;
                }
                
                feeAmountInput.value = feeAmount.toFixed(2);
            } else {
                feeAmountInput.value = '0';
            }
        }
        
        // Update processing fee when loan amount changes
        document.getElementById('loan_amount').addEventListener('input', calculateProcessingFee);
        document.getElementById('processing_fee').addEventListener('input', calculateProcessingFee);
        
        // Ensure tenure is converted to months before form submission
        document.getElementById('loan_form').addEventListener('submit', function(e) {
            const tenureValueInput = document.getElementById('loan_tenure_value');
            const tenureMonthsInput = document.getElementById('loan_tenure_months');
            const tenureUnitInput = document.getElementById('tenure_unit');
            
            if (tenureValueInput.value && tenureUnitInput.value) {
                const value = parseFloat(tenureValueInput.value) || 0;
                let monthsValue = value;
                
                if (tenureUnitInput.value === 'days') {
                    monthsValue = value / 30;
                } else if (tenureUnitInput.value === 'weeks') {
                    monthsValue = value / 4;
                }
                
                tenureMonthsInput.value = Math.round(monthsValue * 100) / 100;
            }
        });
    </script>
</x-app-shell>

