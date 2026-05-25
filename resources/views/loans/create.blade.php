<x-app-shell title="Create Loan" header="Create New Loan">
    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Create New Loan</h1>
                        <a href="{{ route('loans.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Back to Loans
                        </a>
                    </div>

                    {{-- Wizard Steps --}}
                    <nav class="mb-8" aria-label="Loan application steps">
                        <ol class="flex items-center w-full text-sm font-medium">
                            @foreach([
                                1 => 'Client & Terms',
                                2 => 'Credit Score',
                                3 => 'Fees & Summary',
                                4 => 'Review',
                            ] as $stepNum => $stepLabel)
                                <li class="flex items-center {{ $stepNum < 4 ? 'flex-1' : '' }}">
                                    <span class="wizard-step-indicator flex items-center justify-center w-8 h-8 rounded-full border-2 shrink-0 {{ $stepNum === 1 ? 'border-green-600 bg-green-600 text-white' : 'border-gray-300 text-gray-500' }}" data-step-indicator="{{ $stepNum }}">
                                        {{ $stepNum }}
                                    </span>
                                    <span class="ml-2 hidden sm:inline {{ $stepNum === 1 ? 'text-green-700 font-semibold' : 'text-gray-500' }} wizard-step-label" data-step-label="{{ $stepNum }}">{{ $stepLabel }}</span>
                                    @if($stepNum < 4)
                                        <div class="flex-1 h-0.5 mx-3 bg-gray-200 wizard-step-line" data-step-line="{{ $stepNum }}"></div>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                    
                    <form method="POST" action="{{ route('loans.store') }}" id="loan_form" class="space-y-6">
                        @csrf

                        {{-- Step 1: Client, product & loan terms --}}
                        <div class="wizard-step space-y-6" data-step="1">
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
                        
                        <!-- Product Reference -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Reference</h3>
                            <div>
                                <label for="loan_product_id" class="block text-sm font-medium text-gray-700 mb-1">Loan Product *</label>
                                <select name="loan_product_id" id="loan_product_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" required>
                                    <option value="">Select loan product...</option>
                                    @foreach($loanProducts as $product)
                                        <option value="{{ $product->id }}" {{ old('loan_product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ ucfirst($product->repayment_frequency) }} &middot; {{ $product->interest_rate }}% &middot; {{ $product->min_tenure_months }}-{{ $product->max_tenure_months }} months)
                                        </option>
                                    @endforeach
                                </select>
                                @error('loan_product_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Product Info Card (appears after product selection) -->
                            <div id="product_info_card" class="hidden mt-4 p-4 bg-white border border-gray-200 rounded-lg">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                    <div>
                                        <span class="text-gray-500">Frequency:</span>
                                        <span class="font-medium text-gray-900 ml-1" id="info_frequency">-</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Interest:</span>
                                        <span class="font-medium text-gray-900 ml-1" id="info_interest">-</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Amount Range:</span>
                                        <span class="font-medium text-gray-900 ml-1" id="info_amount_range">-</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Tenure:</span>
                                        <span class="font-medium text-gray-900 ml-1" id="info_tenure_range">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Custom Loan Terms -->
                        <div class="bg-blue-50 rounded-lg p-6 border border-blue-200" id="custom_loan_section">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Loan Terms</h3>
                                    <p class="text-sm text-gray-500 mt-1">Customize the loan terms or use product defaults</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="custom_mode_toggle" class="sr-only peer" {{ old('custom_mode') ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-700">Custom Mode</span>
                                </label>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Loan Amount -->
                                <div>
                                    <label for="loan_amount" class="block text-sm font-medium text-gray-700 mb-1">Loan Amount (TZS) *</label>
                                    <input type="number" name="loan_amount" id="loan_amount" step="0.01" min="0" 
                                           value="{{ old('loan_amount') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="e.g. 500000" required>
                                    <p class="mt-1 text-xs text-gray-500" id="amount_hint"></p>
                                    @error('loan_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Repayment Frequency -->
                                <div>
                                    <label for="repayment_frequency_select" class="block text-sm font-medium text-gray-700 mb-1">Repayment Frequency *</label>
                                    <select id="repayment_frequency_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500" disabled>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly" selected>Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                    </select>
                                    <input type="hidden" name="repayment_frequency" id="repayment_frequency" value="{{ old('repayment_frequency', 'monthly') }}">
                                    <p class="mt-1 text-xs text-gray-500" id="frequency_hint">Set by product</p>
                                </div>
                                
                                <!-- Tenure -->
                                <div>
                                    <label for="loan_tenure_value" id="loan_tenure_label" class="block text-sm font-medium text-gray-700 mb-1">Loan Tenure *</label>
                                    <input type="number" name="loan_tenure_value" id="loan_tenure_value" min="1" 
                                           value="{{ old('loan_tenure_value') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="e.g. 30" required>
                                    <input type="hidden" name="loan_tenure_months" id="loan_tenure_months" value="{{ old('loan_tenure_months') }}">
                                    <input type="hidden" name="tenure_unit" id="tenure_unit" value="months">
                                    <p class="mt-1 text-xs text-gray-500" id="tenure_hint"></p>
                                </div>
                                
                                <!-- Interest Rate -->
                                <div>
                                    <label for="interest_rate" id="interest_rate_label" class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (% per annum)</label>
                                    <input type="number" name="interest_rate" id="interest_rate" step="0.01" min="0" max="100" 
                                           value="{{ old('interest_rate') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500" 
                                           placeholder="0.00" readonly>
                                    <p class="mt-1 text-xs text-gray-500" id="interest_hint">From product</p>
                                </div>
                                
                                <!-- Interest Calculation Method -->
                                <div>
                                    <label for="interest_calc_select" class="block text-sm font-medium text-gray-700 mb-1">Interest Calculation</label>
                                    <select id="interest_calc_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500" disabled>
                                        <option value="flat">Flat Rate</option>
                                        <option value="reducing">Reducing Balance</option>
                                    </select>
                                    <input type="hidden" name="interest_calculation_method" id="interest_calculation_method" value="{{ old('interest_calculation_method', 'flat') }}">
                                    <p class="mt-1 text-xs text-gray-500" id="calc_method_hint">Set by product</p>
                                </div>
                                
                                <!-- Custom Repayment Amount Per Period -->
                                <div>
                                    <label for="custom_repayment_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                        Repayment Per <span id="period_label">Period</span> (TZS)
                                    </label>
                                    <input type="number" name="custom_repayment_amount" id="custom_repayment_amount" step="0.01" min="0" 
                                           value="{{ old('custom_repayment_amount') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500" 
                                           placeholder="e.g. 20000" disabled>
                                    <p class="mt-1 text-xs text-gray-500" id="repayment_hint">Enable custom mode to set manually</p>
                                </div>
                            </div>
                        </div>
                        </div>

                        @include('loans.partials.create-credit-assessment')

                        {{-- Step 3: Fees & summary --}}
                        <div class="wizard-step hidden space-y-6" data-step="3">
                        <!-- Custom Charges -->
                        <div class="bg-amber-50 rounded-lg p-6 border border-amber-200" id="charges_section">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Fees & Charges</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Processing Fee from Product -->
                                <div>
                                    <label for="processing_fee" id="processing_fee_label" class="block text-sm font-medium text-gray-700 mb-1">Processing Fee</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none" id="processing_fee_prefix">
                                            <span class="text-gray-500 sm:text-sm">TZS</span>
                                        </div>
                                        <input type="number" name="processing_fee" id="processing_fee" step="0.01" min="0" max="100"
                                               value="{{ old('processing_fee') }}"
                                               class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500" 
                                               placeholder="0.00">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none hidden" id="processing_fee_suffix">
                                            <span class="text-gray-500 sm:text-sm">%</span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500" id="processing_fee_hint"></p>
                                    <input type="hidden" name="processing_fee_amount" id="processing_fee_amount" value="0">
                                </div>
                                
                                <!-- Insurance Fee -->
                                <div>
                                    <label for="insurance_fee" class="block text-sm font-medium text-gray-700 mb-1">Insurance Fee (TZS)</label>
                                    <input type="number" name="insurance_fee" id="insurance_fee" step="0.01" min="0" 
                                           value="{{ old('insurance_fee') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500" 
                                           placeholder="0.00">
                                </div>
                            </div>
                            
                            <!-- Custom Charge -->
                            <div class="mt-4 pt-4 border-t border-amber-200">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-sm font-medium text-gray-700">Custom Loan Charge</label>
                                    <span class="text-xs text-amber-600">This adds an extra charge on top of interest</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="custom_charge_type" class="block text-xs font-medium text-gray-500 mb-1">Charge Type</label>
                                        <select name="custom_charge_type" id="custom_charge_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                                            <option value="">No extra charge</option>
                                            <option value="percent" {{ old('custom_charge_type') == 'percent' ? 'selected' : '' }}>Percentage of Loan</option>
                                            <option value="fixed" {{ old('custom_charge_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount (TZS)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="custom_charge_value" class="block text-xs font-medium text-gray-500 mb-1">
                                            <span id="charge_value_label">Charge Value</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" name="custom_charge_value" id="custom_charge_value" step="0.01" min="0" 
                                                   value="{{ old('custom_charge_value') }}"
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm disabled:bg-gray-100" 
                                                   placeholder="0.00" disabled>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Calculated Charge</label>
                                        <div class="w-full px-3 py-2 bg-amber-100 border border-amber-300 rounded-lg text-sm font-medium text-amber-900" id="calculated_charge_display">
                                            TZS 0.00
                                        </div>
                                        <input type="hidden" name="custom_charge_amount" id="custom_charge_amount" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Live Calculation Summary -->
                        <div class="bg-green-50 rounded-lg p-6 border border-green-200" id="summary_section">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Loan Summary
                                <span class="text-sm font-normal text-gray-500 ml-2">(auto-calculated)</span>
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-white rounded-lg p-4 border border-green-200 text-center">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Loan Amount</p>
                                    <p class="text-lg font-bold text-gray-900 mt-1" id="summary_loan_amount">TZS 0</p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-green-200 text-center">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Total Interest + Charges</p>
                                    <p class="text-lg font-bold text-amber-700 mt-1" id="summary_total_charges">TZS 0</p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-green-200 text-center">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Total Repayment</p>
                                    <p class="text-lg font-bold text-green-700 mt-1" id="summary_total_repayment">TZS 0</p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-green-200 text-center">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider" id="summary_installment_label">Per Installment</p>
                                    <p class="text-lg font-bold text-blue-700 mt-1" id="summary_per_installment">TZS 0</p>
                                    <p class="text-xs text-gray-500 mt-1" id="summary_installment_count">0 installments</p>
                                </div>
                            </div>
                            
                            <!-- Breakdown row -->
                            <div class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                                <div class="bg-white rounded p-2 border border-green-100">
                                    <span class="text-gray-500">Installments:</span>
                                    <span class="font-medium" id="detail_installments">0</span>
                                </div>
                                <div class="bg-white rounded p-2 border border-green-100">
                                    <span class="text-gray-500">Interest:</span>
                                    <span class="font-medium" id="detail_interest">TZS 0</span>
                                </div>
                                <div class="bg-white rounded p-2 border border-green-100">
                                    <span class="text-gray-500">Custom Charge:</span>
                                    <span class="font-medium" id="detail_custom_charge">TZS 0</span>
                                </div>
                                <div class="bg-white rounded p-2 border border-green-100">
                                    <span class="text-gray-500">Processing Fee:</span>
                                    <span class="font-medium" id="detail_processing_fee">TZS 0</span>
                                </div>
                                <div class="bg-white rounded p-2 border border-green-100">
                                    <span class="text-gray-500">Insurance:</span>
                                    <span class="font-medium" id="detail_insurance">TZS 0</span>
                                </div>
                            </div>
                            </div>
                        </div>
                        </div>

                        {{-- Step 4: Review & submit --}}
                        <div class="wizard-step hidden space-y-6" data-step="4">

                        <div id="review_score_summary" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-blue-900 mb-2">Credit Assessment Summary</h4>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                                <div><span class="text-gray-500">Score:</span> <span class="font-semibold" id="review_score">-</span></div>
                                <div><span class="text-gray-500">Band:</span> <span class="font-semibold" id="review_band">-</span></div>
                                <div><span class="text-gray-500">Requested:</span> <span class="font-semibold" id="review_requested_amount">-</span></div>
                                <div><span class="text-gray-500">Recommended max:</span> <span class="font-semibold" id="review_max">-</span></div>
                                <div><span class="text-gray-500">Status:</span> <span class="font-semibold" id="review_eligible">-</span></div>
                            </div>
                            <p id="review_collateral_boost" class="hidden mt-2 text-sm text-green-800"></p>
                        </div>

                        <div id="review_collateral_summary" class="hidden bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-green-900 mb-2">Pledged Collateral</h4>
                            <p class="text-sm text-green-800" id="review_collateral_text">—</p>
                            <p class="text-xs text-green-700 mt-1">This collateral will be marked as pledged and cannot be reused until released.</p>
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
                            </div>
                        </div>
                        </div>
                        
                        <!-- Hidden field for custom mode -->
                        <input type="hidden" name="custom_mode" id="custom_mode" value="{{ old('custom_mode', '0') }}">
                        
                        <!-- Wizard Navigation -->
                        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                            <button type="button" id="wizard_back" class="hidden bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                Back
                            </button>
                            <div class="flex-1"></div>
                            <div class="flex space-x-4">
                                <a href="{{ route('loans.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium transition-colors">
                                    Cancel
                                </a>
                                <button type="button" id="wizard_next" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                    Next
                                </button>
                                <button type="submit" id="wizard_submit" class="hidden bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                    Create Loan Application
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Client data for search
        const clients = @json($clients);
        const loanProducts = @json($loanProducts);
        
        // State
        let customMode = {{ old('custom_mode') ? 'true' : 'false' }};
        let selectedProduct = null;
        let currentWizardStep = 1;
        let clientScoreData = null;
        let selectedClientUuid = null;
        let clientCollaterals = [];
        const clientScoreUrlTemplate = @json(route('loans.client-score', ['client' => 'CLIENT_UUID']));
        const clientCollateralsUrlTemplate = @json(route('collaterals.client-available', ['client' => 'CLIENT_UUID']));
        const totalWizardSteps = 4;

        // ===== WIZARD =====
        function showWizardStep(step) {
            currentWizardStep = step;
            document.querySelectorAll('.wizard-step').forEach(el => {
                el.classList.toggle('hidden', parseInt(el.dataset.step) !== step);
            });

            document.querySelectorAll('[data-step-indicator]').forEach(el => {
                const s = parseInt(el.dataset.stepIndicator);
                el.classList.remove('border-green-600', 'bg-green-600', 'text-white', 'border-gray-300', 'text-gray-500', 'bg-green-100', 'text-green-700');
                if (s < step) {
                    el.classList.add('border-green-600', 'bg-green-100', 'text-green-700');
                } else if (s === step) {
                    el.classList.add('border-green-600', 'bg-green-600', 'text-white');
                } else {
                    el.classList.add('border-gray-300', 'text-gray-500');
                }
            });

            document.querySelectorAll('[data-step-label]').forEach(el => {
                const s = parseInt(el.dataset.stepLabel);
                el.classList.toggle('text-green-700', s === step);
                el.classList.toggle('font-semibold', s === step);
                el.classList.toggle('text-gray-500', s !== step);
            });

            document.querySelectorAll('[data-step-line]').forEach(el => {
                const s = parseInt(el.dataset.stepLine);
                el.classList.toggle('bg-green-600', s < step);
                el.classList.toggle('bg-gray-200', s >= step);
            });

            document.getElementById('wizard_back').classList.toggle('hidden', step === 1);
            document.getElementById('wizard_next').classList.toggle('hidden', step === totalWizardSteps);
            document.getElementById('wizard_submit').classList.toggle('hidden', step !== totalWizardSteps);

            if (step === 4 && clientScoreData) {
                document.getElementById('review_score_summary').classList.remove('hidden');
                document.getElementById('review_score').textContent = clientScoreData.score + '/100';
                document.getElementById('review_band').textContent = clientScoreData.band_label;
                document.getElementById('review_max').textContent = clientScoreData.recommended_max_loan !== null
                    ? 'TZS ' + numberFormat(clientScoreData.recommended_max_loan) : 'N/A';
                document.getElementById('review_eligible').textContent = clientScoreData.passes ? 'Looks good (advisory)' : 'Review recommended (advisory)';
                const amt = parseFloat(document.getElementById('loan_amount').value) || 0;
                document.getElementById('review_requested_amount').textContent = amt > 0 ? 'TZS ' + numberFormat(amt) : '—';

                const boostEl = document.getElementById('review_collateral_boost');
                if (clientScoreData.collateral_boost > 0) {
                    boostEl.classList.remove('hidden');
                    boostEl.textContent = 'Includes TZS ' + numberFormat(clientScoreData.collateral_boost) + ' collateral boost from "' + (clientScoreData.collateral?.title || 'selected item') + '".';
                } else {
                    boostEl.classList.add('hidden');
                }

                updateReviewCollateralSummary();
            }

            if (step === 2 && clientIdInput.value) {
                updateTenureMonths();
                loadClientCollaterals();
                fetchClientScore();
            }
        }

        function validateWizardStep(step) {
            if (step === 1) {
                if (!clientIdInput.value) {
                    alert('Please select a client before continuing.');
                    return false;
                }
                const productId = document.getElementById('loan_product_id').value;
                if (!productId) {
                    alert('Please select a loan product.');
                    return false;
                }
                if (!selectedProduct) {
                    selectedProduct = loanProducts.find(p => p.id == productId);
                }
                const amount = parseFloat(document.getElementById('loan_amount').value) || 0;
                if (amount <= 0) {
                    alert('Please enter a valid loan amount.');
                    return false;
                }
                const tenure = parseFloat(document.getElementById('loan_tenure_value').value) || 0;
                if (tenure <= 0) {
                    alert('Please enter a valid loan tenure.');
                    return false;
                }
                updateTenureMonths();
                if (selectedProduct && (amount < selectedProduct.min_amount || amount > selectedProduct.max_amount)) {
                    alert('Loan amount must be between TZS ' + numberFormat(selectedProduct.min_amount) + ' and TZS ' + numberFormat(selectedProduct.max_amount) + '.');
                    return false;
                }
                return true;
            }
            if (step === 2) {
                return true;
            }
            if (step === 3) {
                return true;
            }
            return true;
        }

        document.getElementById('wizard_next').addEventListener('click', function() {
            if (!validateWizardStep(currentWizardStep)) return;
            if (currentWizardStep < totalWizardSteps) {
                showWizardStep(currentWizardStep + 1);
            }
        });

        document.getElementById('wizard_back').addEventListener('click', function() {
            if (currentWizardStep > 1) {
                showWizardStep(currentWizardStep - 1);
            }
        });

        async function fetchClientScore() {
            const clientUuid = selectedClientUuid || clients.find(c => c.id == clientIdInput.value)?.uuid;
            if (!clientUuid) return;

            const loading = document.getElementById('score_loading');
            const empty = document.getElementById('score_empty');
            const results = document.getElementById('score_results');

            loading.classList.remove('hidden');
            empty.classList.add('hidden');
            results.classList.add('hidden');

            const params = new URLSearchParams();
            const productId = document.getElementById('loan_product_id').value;
            const amount = document.getElementById('loan_amount').value;
            const tenureMonths = document.getElementById('loan_tenure_months').value;
            const interestRate = document.getElementById('interest_rate').value;
            const frequency = document.getElementById('repayment_frequency').value;
            const calcMethod = document.getElementById('interest_calculation_method').value;
            if (productId) params.set('loan_product_id', productId);
            if (amount) params.set('loan_amount', amount);
            if (tenureMonths) params.set('loan_tenure_months', tenureMonths);
            if (interestRate !== '') params.set('interest_rate', interestRate);
            if (frequency) params.set('repayment_frequency', frequency);
            if (calcMethod) params.set('interest_calculation_method', calcMethod);
            const collateralId = document.getElementById('collateral_id')?.value;
            if (collateralId) params.set('collateral_id', collateralId);

            const url = clientScoreUrlTemplate.replace('CLIENT_UUID', clientUuid) + (params.toString() ? '?' + params.toString() : '');

            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error('Score request failed (' + response.status + ')');
                clientScoreData = await response.json();
                renderClientScore(clientScoreData);
            } catch (e) {
                empty.classList.remove('hidden');
                empty.innerHTML = '<p class="text-red-600">Unable to load credit assessment. Please try again.</p>';
            } finally {
                loading.classList.add('hidden');
            }
        }

        function renderClientScore(data) {
            document.getElementById('score_empty').classList.add('hidden');
            document.getElementById('score_results').classList.remove('hidden');

            document.getElementById('score_value').textContent = data.score;
            document.getElementById('score_band_label').textContent = data.band_label + ' (' + data.band + ')';
            document.getElementById('score_client_name').textContent = clientSearch.value || 'Selected client';

            document.getElementById('score_circle').className = 'flex items-center justify-center w-20 h-20 rounded-full border-4 text-2xl font-bold ' + scoreCircleClass(data.band);
            document.getElementById('score_recommended_max').textContent = data.recommended_max_loan !== null
                ? 'TZS ' + numberFormat(data.recommended_max_loan) : 'N/A';

            const boostPanel = document.getElementById('collateral_boost_panel');
            if (data.collateral_boost > 0) {
                boostPanel.classList.remove('hidden');
                document.getElementById('score_base_max').textContent = data.base_recommended_max_loan !== null
                    ? 'TZS ' + numberFormat(data.base_recommended_max_loan) : 'N/A';
                document.getElementById('score_collateral_boost').textContent = '+ TZS ' + numberFormat(data.collateral_boost);
                document.getElementById('score_effective_max').textContent = 'TZS ' + numberFormat(data.recommended_max_loan);
            } else {
                boostPanel.classList.add('hidden');
            }

            renderAmountAssessment(data.amount_assessment);

            const badge = document.getElementById('score_eligibility_badge');
            badge.textContent = data.passes ? 'Advisory: within guidelines' : 'Advisory: review before approving';
            badge.className = 'mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full ' +
                (data.passes ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800');

            setFactorBar('repayment', data.factors.repayment_history);
            setFactorBar('arrears', data.factors.arrears);
            setFactorBar('profile', data.factors.profile);
            setFactorBar('portfolio', data.factors.portfolio);

            document.getElementById('hist_total').textContent = data.history.total_loans;
            document.getElementById('hist_completed').textContent = data.history.completed_loans;
            document.getElementById('hist_overdue').textContent = data.history.overdue_loans;
            document.getElementById('hist_written_off').textContent = data.history.written_off_loans;

            const flagsContainer = document.getElementById('score_flags_container');
            const flagsList = document.getElementById('score_flags');
            flagsList.innerHTML = '';
            if (data.flags && data.flags.length > 0) {
                flagsContainer.classList.remove('hidden');
                data.flags.forEach(flag => {
                    const colors = { danger: 'text-red-700 bg-red-50 border-red-200', warning: 'text-amber-700 bg-amber-50 border-amber-200', info: 'text-blue-700 bg-blue-50 border-blue-200' };
                    const li = document.createElement('li');
                    li.className = 'p-2 rounded border ' + (colors[flag.type] || colors.info);
                    li.textContent = flag.message;
                    flagsList.appendChild(li);
                });
            } else {
                flagsContainer.classList.add('hidden');
            }

            const checksList = document.getElementById('score_checks');
            checksList.innerHTML = '';
            if (data.eligibility && data.eligibility.checks) {
                document.getElementById('score_checks_container').classList.remove('hidden');
                data.eligibility.checks.forEach(check => {
                    const li = document.createElement('li');
                    li.className = check.passed ? 'text-green-700' : 'text-red-700';
                    li.textContent = (check.passed ? '✓ ' : '✗ ') + check.label + (check.message ? ' — ' + check.message : '');
                    checksList.appendChild(li);
                });
            }

            const reasonsList = document.getElementById('score_reasons');
            reasonsList.innerHTML = '';
            (data.reasons || []).forEach(reason => {
                const li = document.createElement('li');
                li.textContent = reason;
                reasonsList.appendChild(li);
            });
        }

        function renderAmountAssessment(assessment) {
            const panel = document.getElementById('amount_assessment_panel');
            if (!panel) return;
            if (!assessment) {
                panel.classList.add('hidden');
                return;
            }
            panel.classList.remove('hidden');
            const outlookColors = { good: 'text-green-700 bg-green-50 border-green-200', fair: 'text-amber-700 bg-amber-50 border-amber-200', poor: 'text-red-700 bg-red-50 border-red-200' };
            const outlookLabels = { good: 'Good repayment outlook', fair: 'Moderate risk', poor: 'High repayment risk' };
            document.getElementById('assess_requested').textContent = 'TZS ' + numberFormat(assessment.requested_amount);
            document.getElementById('assess_recommended').textContent = 'TZS ' + numberFormat(assessment.recommended_max_loan);
            document.getElementById('assess_affordable').textContent = assessment.affordable ? 'Yes — within limit (advisory)' : 'Above limit (advisory)';
            document.getElementById('assess_affordable').className = 'font-semibold ' + (assessment.affordable ? 'text-green-700' : 'text-amber-700');
            const outlookEl = document.getElementById('assess_outlook');
            outlookEl.textContent = outlookLabels[assessment.repayment_outlook] || assessment.repayment_outlook;
            outlookEl.className = 'inline-flex px-2 py-1 text-xs font-semibold rounded-full border ' + (outlookColors[assessment.repayment_outlook] || outlookColors.fair);
            document.getElementById('assess_monthly').textContent = assessment.estimated_monthly_payment > 0
                ? '~TZS ' + numberFormat(assessment.estimated_monthly_payment) + '/installment' : '—';
            document.getElementById('assess_max_monthly').textContent = assessment.max_monthly_payment > 0
                ? 'TZS ' + numberFormat(assessment.max_monthly_payment) : 'N/A (no income on file)';
            const alertsList = document.getElementById('amount_assessment_alerts');
            alertsList.innerHTML = '';
            (assessment.alerts || []).forEach(alert => {
                const colors = { danger: 'text-red-700 bg-red-50 border-red-200', warning: 'text-amber-700 bg-amber-50 border-amber-200', info: 'text-blue-700 bg-blue-50 border-blue-200' };
                const li = document.createElement('li');
                li.className = 'p-3 rounded-lg border text-sm ' + (colors[alert.type] || colors.info);
                li.textContent = alert.message;
                alertsList.appendChild(li);
            });
        }

        function setFactorBar(key, value) {
            document.getElementById('factor_' + key).textContent = value + '/100';
            document.getElementById('bar_' + key).style.width = value + '%';
        }

        function scoreCircleClass(band) {
            const map = { excellent: 'border-green-600 text-green-700', good: 'border-blue-600 text-blue-700', fair: 'border-yellow-500 text-yellow-700', poor: 'border-orange-500 text-orange-700', critical: 'border-red-600 text-red-700' };
            return map[band] || map.fair;
        }

        function resetClientScore() {
            clientScoreData = null;
            document.getElementById('score_results')?.classList.add('hidden');
            document.getElementById('score_loading')?.classList.add('hidden');
            const empty = document.getElementById('score_empty');
            if (empty) {
                empty.classList.remove('hidden');
                empty.innerHTML = '<p>Complete Step 1 (client, product, and loan terms) then continue to run the credit assessment.</p>';
            }
        }

        function scheduleScoreRefresh() {
            if (currentWizardStep < 2) resetClientScore();
            if (clientIdInput.value && currentWizardStep >= 2) {
                clearTimeout(window.scoreRefreshTimer);
                window.scoreRefreshTimer = setTimeout(fetchClientScore, 400);
            }
        }
        
        // ===== CLIENT SEARCH =====
        const clientSearch = document.getElementById('client_search');
        const clientDropdown = document.getElementById('client_dropdown');
        const clientIdInput = document.getElementById('client_id');
        const selectedClientDisplay = document.getElementById('selected_client_display');
        const selectedClientName = document.getElementById('selected_client_name');
        
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
                return name.includes(searchLower) || phone.includes(searchLower) || clientNumber.includes(searchLower) || email.includes(searchLower);
            });
            displayClientOptions(filtered);
        }
        
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
                html += `<div class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0 client-option" data-id="${client.id}" data-uuid="${client.uuid || ''}" data-name="${displayName}">
                    <div class="font-medium text-gray-900">${displayName}</div>
                    <div class="text-xs text-gray-500">${clientInfo}</div>
                </div>`;
            });
            clientDropdown.innerHTML = html;
            clientDropdown.classList.remove('hidden');
            document.querySelectorAll('.client-option').forEach(option => {
                option.addEventListener('click', function() {
                    selectClient(this.getAttribute('data-id'), this.getAttribute('data-name'), this.getAttribute('data-uuid'));
                });
            });
        }
        
        function selectClient(clientId, clientName, clientUuid) {
            clientIdInput.value = clientId;
            selectedClientUuid = clientUuid || clients.find(c => c.id == clientId)?.uuid || null;
            clientSearch.value = clientName;
            selectedClientName.textContent = clientName;
            selectedClientDisplay.classList.remove('hidden');
            clientDropdown.classList.add('hidden');
            resetClientScore();
            loadClientCollaterals();
        }
        
        function clearClientSelection() {
            clientIdInput.value = '';
            selectedClientUuid = null;
            clientSearch.value = '';
            selectedClientDisplay.classList.add('hidden');
            resetClientScore();
            resetCollateralSelect();
        }

        async function loadClientCollaterals() {
            const select = document.getElementById('collateral_id');
            if (!select) return;

            select.innerHTML = '<option value="">No collateral</option>';
            clientCollaterals = [];
            resetCollateralPreview();

            const clientUuid = selectedClientUuid || clients.find(c => c.id == clientIdInput.value)?.uuid;
            if (!clientUuid) return;

            try {
                const url = clientCollateralsUrlTemplate.replace('CLIENT_UUID', clientUuid);
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error('Failed to load collaterals');
                clientCollaterals = await response.json();
                clientCollaterals.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.title + ' (' + item.reference_number + ') — capacity TZS ' + numberFormat(item.lending_capacity);
                    select.appendChild(opt);
                });
                @if(old('collateral_id'))
                    select.value = '{{ old('collateral_id') }}';
                    updateCollateralPreview();
                @endif
            } catch (e) {
                console.error(e);
            }
        }

        function resetCollateralSelect() {
            const select = document.getElementById('collateral_id');
            if (select) {
                select.innerHTML = '<option value="">No collateral</option>';
            }
            clientCollaterals = [];
            resetCollateralPreview();
            document.getElementById('review_collateral_summary')?.classList.add('hidden');
        }

        function resetCollateralPreview() {
            document.getElementById('collateral_preview')?.classList.add('hidden');
        }

        function updateCollateralPreview() {
            const select = document.getElementById('collateral_id');
            const preview = document.getElementById('collateral_preview');
            if (!select || !preview) return;

            const item = clientCollaterals.find(c => c.id == select.value);
            if (!item) {
                resetCollateralPreview();
                return;
            }

            preview.classList.remove('hidden');
            document.getElementById('collateral_preview_title').textContent = item.title + ' (' + item.type + ')';
            document.getElementById('collateral_preview_value').textContent = 'TZS ' + numberFormat(item.estimated_value);
            document.getElementById('collateral_preview_capacity').textContent = 'TZS ' + numberFormat(item.lending_capacity);
            document.getElementById('collateral_preview_ref').textContent = item.reference_number;
        }

        function updateReviewCollateralSummary() {
            const select = document.getElementById('collateral_id');
            const panel = document.getElementById('review_collateral_summary');
            const text = document.getElementById('review_collateral_text');
            if (!select || !panel || !text) return;

            const item = clientCollaterals.find(c => c.id == select.value);
            if (!item) {
                panel.classList.add('hidden');
                return;
            }

            panel.classList.remove('hidden');
            text.textContent = item.title + ' (' + item.reference_number + ') — value TZS ' + numberFormat(item.estimated_value)
                + ', lending capacity TZS ' + numberFormat(item.lending_capacity) + '.';
        }
        
        clientSearch.addEventListener('input', function() { filterClients(this.value); });
        clientSearch.addEventListener('focus', function() { if (this.value) filterClients(this.value); });
        document.addEventListener('click', function(e) {
            if (!clientSearch.contains(e.target) && !clientDropdown.contains(e.target)) {
                clientDropdown.classList.add('hidden');
            }
        });
        
        @if(old('client_id'))
            const oldClient = clients.find(c => c.id == {{ old('client_id') }});
            if (oldClient) {
                selectClient(oldClient.id, `${oldClient.first_name || ''} ${oldClient.last_name || ''}`.trim(), oldClient.uuid);
            }
        @endif

        @if(request('client_id'))
            const preselectedClient = clients.find(c => c.id == {{ (int) request('client_id') }});
            if (preselectedClient) {
                const preName = preselectedClient.client_type === 'individual'
                    ? `${preselectedClient.first_name || ''} ${preselectedClient.last_name || ''}`.trim()
                    : (preselectedClient.business_name || 'Client');
                selectClient(preselectedClient.id, preName, preselectedClient.uuid);
            }
        @endif
        
        document.getElementById('collateral_id')?.addEventListener('change', function() {
            updateCollateralPreview();
            scheduleScoreRefresh();
        });
        
        // ===== CUSTOM MODE TOGGLE =====
        const customToggle = document.getElementById('custom_mode_toggle');
        
        function setCustomMode(enabled) {
            customMode = enabled;
            document.getElementById('custom_mode').value = enabled ? '1' : '0';
            
            const interestInput = document.getElementById('interest_rate');
            const freqSelect = document.getElementById('repayment_frequency_select');
            const calcSelect = document.getElementById('interest_calc_select');
            const repaymentInput = document.getElementById('custom_repayment_amount');
            
            if (enabled) {
                interestInput.removeAttribute('readonly');
                interestInput.classList.remove('disabled:bg-gray-100', 'disabled:text-gray-500');
                freqSelect.disabled = false;
                freqSelect.classList.remove('disabled:bg-gray-100', 'disabled:text-gray-500');
                calcSelect.disabled = false;
                calcSelect.classList.remove('disabled:bg-gray-100', 'disabled:text-gray-500');
                repaymentInput.disabled = false;
                repaymentInput.classList.remove('disabled:bg-gray-100', 'disabled:text-gray-500');
                
                document.getElementById('interest_hint').textContent = 'You can set a custom rate';
                document.getElementById('frequency_hint').textContent = 'Choose repayment frequency';
                document.getElementById('calc_method_hint').textContent = 'Choose calculation method';
                document.getElementById('repayment_hint').textContent = 'Set custom amount per installment';
            } else {
                interestInput.setAttribute('readonly', true);
                freqSelect.disabled = true;
                calcSelect.disabled = true;
                repaymentInput.disabled = true;
                repaymentInput.value = '';
                
                document.getElementById('interest_hint').textContent = 'From product';
                document.getElementById('frequency_hint').textContent = 'Set by product';
                document.getElementById('calc_method_hint').textContent = 'Set by product';
                document.getElementById('repayment_hint').textContent = 'Enable custom mode to set manually';
                
                // Restore product values if product selected
                if (selectedProduct) {
                    applyProductDefaults(selectedProduct);
                }
            }
            
            updateSummary();
            scheduleScoreRefresh();
        }
        
        customToggle.addEventListener('change', function() {
            setCustomMode(this.checked);
        });
        
        // ===== PRODUCT SELECTION =====
        document.getElementById('loan_product_id').addEventListener('change', function() {
            const productId = this.value;
            selectedProduct = loanProducts.find(p => p.id == productId);
            
            if (selectedProduct) {
                document.getElementById('product_info_card').classList.remove('hidden');
                
                // Show product info
                document.getElementById('info_frequency').textContent = capitalize(selectedProduct.repayment_frequency || 'monthly');
                const prodFreq = selectedProduct.repayment_frequency || 'monthly';
                const interestSuffix = (prodFreq === 'daily' || prodFreq === 'weekly') ? '% for loan period' : '% p.a.';
                document.getElementById('info_interest').textContent = selectedProduct.interest_rate + interestSuffix;
                document.getElementById('info_amount_range').textContent = `TZS ${numberFormat(selectedProduct.min_amount)} - ${numberFormat(selectedProduct.max_amount)}`;
                document.getElementById('info_tenure_range').textContent = `${selectedProduct.min_tenure_months} - ${selectedProduct.max_tenure_months} months`;
                
                // Apply product defaults (unless custom mode)
                if (!customMode) {
                    applyProductDefaults(selectedProduct);
                }
                
                // Always set amount constraints
                document.getElementById('loan_amount').min = selectedProduct.min_amount;
                document.getElementById('loan_amount').max = selectedProduct.max_amount;
                document.getElementById('amount_hint').textContent = `Range: TZS ${numberFormat(selectedProduct.min_amount)} - TZS ${numberFormat(selectedProduct.max_amount)}`;
                
                updateProcessingFeeDisplay(selectedProduct);
            } else {
                document.getElementById('product_info_card').classList.add('hidden');
                document.getElementById('amount_hint').textContent = '';
            }
            
            updateSummary();
            scheduleScoreRefresh();
        });
        
        function applyProductDefaults(product) {
            // Interest rate
            document.getElementById('interest_rate').value = product.interest_rate;
            
            // Frequency
            const freq = product.repayment_frequency || 'monthly';
            document.getElementById('repayment_frequency_select').value = freq;
            document.getElementById('repayment_frequency').value = freq;
            
            // Interest calculation method
            const calcMethod = product.interest_calculation_method || 'flat';
            document.getElementById('interest_calc_select').value = calcMethod;
            document.getElementById('interest_calculation_method').value = calcMethod;
            
            // Update tenure labels and ranges
            updateTenureForFrequency(freq, product);
        }
        
        function updateTenureForFrequency(frequency, product) {
            const tenureValueInput = document.getElementById('loan_tenure_value');
            const tenureLabel = document.getElementById('loan_tenure_label');
            const tenureHint = document.getElementById('tenure_hint');
            const tenureUnitInput = document.getElementById('tenure_unit');
            
            let minTenure, maxTenure, unit, label;
            
            if (frequency === 'daily') {
                minTenure = product ? product.min_tenure_months * 30 : 1;
                maxTenure = product ? product.max_tenure_months * 30 : 3650;
                unit = 'days';
                label = 'Loan Tenure (Days) *';
            } else if (frequency === 'weekly') {
                minTenure = product ? product.min_tenure_months * 4 : 1;
                maxTenure = product ? product.max_tenure_months * 4 : 520;
                unit = 'weeks';
                label = 'Loan Tenure (Weeks) *';
            } else if (frequency === 'quarterly') {
                minTenure = product ? Math.ceil(product.min_tenure_months / 3) : 1;
                maxTenure = product ? Math.floor(product.max_tenure_months / 3) : 40;
                unit = 'quarters';
                label = 'Loan Tenure (Quarters) *';
            } else {
                minTenure = product ? product.min_tenure_months : 1;
                maxTenure = product ? product.max_tenure_months : 360;
                unit = 'months';
                label = 'Loan Tenure (Months) *';
            }
            
            tenureValueInput.min = minTenure;
            tenureValueInput.max = maxTenure;
            if (!tenureValueInput.value || parseFloat(tenureValueInput.value) < minTenure) {
                tenureValueInput.value = minTenure;
            }
            tenureValueInput.placeholder = minTenure.toString();
            tenureLabel.textContent = label;
            tenureHint.textContent = `Range: ${minTenure} - ${maxTenure} ${unit}`;
            tenureUnitInput.value = unit;
            
            // Update period label for repayment amount
            const periodLabels = { 'daily': 'Day', 'weekly': 'Week', 'monthly': 'Month', 'quarterly': 'Quarter' };
            document.getElementById('period_label').textContent = periodLabels[frequency] || 'Period';
            document.getElementById('summary_installment_label').textContent = `Per ${periodLabels[frequency] || 'Installment'}`;
            
            // Update interest rate label based on frequency
            const interestRateLabel = document.getElementById('interest_rate_label');
            if (frequency === 'daily' || frequency === 'weekly') {
                interestRateLabel.textContent = 'Interest Rate (% for loan period)';
            } else {
                interestRateLabel.textContent = 'Interest Rate (% per annum)';
            }
            
            updateTenureMonths();
            updateSummary();
        }
        
        // Frequency select change (custom mode)
        document.getElementById('repayment_frequency_select').addEventListener('change', function() {
            const freq = this.value;
            document.getElementById('repayment_frequency').value = freq;
            updateTenureForFrequency(freq, selectedProduct);
            scheduleScoreRefresh();
        });
        
        // Interest calc method change (custom mode)
        document.getElementById('interest_calc_select').addEventListener('change', function() {
            document.getElementById('interest_calculation_method').value = this.value;
            updateSummary();
            scheduleScoreRefresh();
        });
        
        // ===== TENURE CALCULATION =====
        function updateTenureMonths() {
            const tenureValueInput = document.getElementById('loan_tenure_value');
            const tenureMonthsInput = document.getElementById('loan_tenure_months');
            const tenureUnitInput = document.getElementById('tenure_unit');
            
            const value = parseFloat(tenureValueInput.value) || 0;
            let monthsValue = value;
            
            if (tenureUnitInput.value === 'days') {
                monthsValue = value / 30;
            } else if (tenureUnitInput.value === 'weeks') {
                monthsValue = value / 4;
            } else if (tenureUnitInput.value === 'quarters') {
                monthsValue = value * 3;
            }
            
            tenureMonthsInput.value = Math.round(monthsValue * 100) / 100;
        }
        
        document.getElementById('loan_tenure_value').addEventListener('input', function() {
            updateTenureMonths();
            updateSummary();
            scheduleScoreRefresh();
        });
        
        // ===== CUSTOM CHARGES =====
        document.getElementById('custom_charge_type').addEventListener('change', function() {
            const chargeInput = document.getElementById('custom_charge_value');
            const chargeLabel = document.getElementById('charge_value_label');
            
            if (this.value) {
                chargeInput.disabled = false;
                chargeInput.classList.remove('disabled:bg-gray-100');
                chargeLabel.textContent = this.value === 'percent' ? 'Percentage (%)' : 'Amount (TZS)';
                chargeInput.placeholder = this.value === 'percent' ? 'e.g. 10' : 'e.g. 50000';
                chargeInput.max = this.value === 'percent' ? 100 : '';
            } else {
                chargeInput.disabled = true;
                chargeInput.classList.add('disabled:bg-gray-100');
                chargeInput.value = '';
                chargeLabel.textContent = 'Charge Value';
            }
            
            updateCustomCharge();
            updateSummary();
        });
        
        document.getElementById('custom_charge_value').addEventListener('input', function() {
            updateCustomCharge();
            updateSummary();
        });
        
        function updateCustomCharge() {
            const chargeType = document.getElementById('custom_charge_type').value;
            const chargeValue = parseFloat(document.getElementById('custom_charge_value').value) || 0;
            const loanAmount = parseFloat(document.getElementById('loan_amount').value) || 0;
            let chargeAmount = 0;
            
            if (chargeType === 'percent') {
                chargeAmount = (loanAmount * chargeValue) / 100;
            } else if (chargeType === 'fixed') {
                chargeAmount = chargeValue;
            }
            
            document.getElementById('calculated_charge_display').textContent = 'TZS ' + numberFormat(chargeAmount);
            document.getElementById('custom_charge_amount').value = chargeAmount.toFixed(2);
        }
        
        // ===== PROCESSING FEE =====
        function updateProcessingFeeDisplay(product) {
            const feeInput = document.getElementById('processing_fee');
            const feeLabel = document.getElementById('processing_fee_label');
            const feePrefix = document.getElementById('processing_fee_prefix');
            const feeSuffix = document.getElementById('processing_fee_suffix');
            const feeHint = document.getElementById('processing_fee_hint');
            
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
                feeHint.textContent = 'Percentage of loan amount';
            } else {
                feeLabel.textContent = 'Processing Fee (TZS)';
                feePrefix.classList.remove('hidden');
                feeSuffix.classList.add('hidden');
                feeInput.classList.remove('pr-8');
                feeInput.classList.add('pl-12');
                feeInput.removeAttribute('max');
                feeInput.value = feeValue;
                feeHint.textContent = 'Fixed amount';
            }
            
            calculateProcessingFee();
        }
        
        function calculateProcessingFee() {
            const feeInput = document.getElementById('processing_fee');
            const feeAmountInput = document.getElementById('processing_fee_amount');
            const loanAmount = parseFloat(document.getElementById('loan_amount').value) || 0;
            const feeValue = parseFloat(feeInput.value) || 0;
            
            if (selectedProduct && loanAmount > 0) {
                const feeType = selectedProduct.processing_fee_type || 'fixed';
                let feeAmount = feeType === 'percent' ? (loanAmount * feeValue) / 100 : feeValue;
                feeAmountInput.value = feeAmount.toFixed(2);
            } else {
                feeAmountInput.value = '0';
            }
            
            updateSummary();
        }
        
        document.getElementById('loan_amount').addEventListener('input', function() {
            calculateProcessingFee();
            updateCustomCharge();
            updateSummary();
            scheduleScoreRefresh();
        });
        document.getElementById('processing_fee').addEventListener('input', function() {
            calculateProcessingFee();
            updateSummary();
        });
        document.getElementById('interest_rate').addEventListener('input', function() {
            updateSummary();
            scheduleScoreRefresh();
        });
        document.getElementById('custom_repayment_amount').addEventListener('input', updateSummary);
        document.getElementById('insurance_fee').addEventListener('input', updateSummary);
        
        // ===== LIVE SUMMARY CALCULATION =====
        function updateSummary() {
            const loanAmount = parseFloat(document.getElementById('loan_amount').value) || 0;
            const interestRate = parseFloat(document.getElementById('interest_rate').value) || 0;
            const tenureValue = parseFloat(document.getElementById('loan_tenure_value').value) || 0;
            const tenureUnit = document.getElementById('tenure_unit').value;
            const calcMethod = document.getElementById('interest_calculation_method').value;
            const customRepayment = parseFloat(document.getElementById('custom_repayment_amount').value) || 0;
            const customChargeAmount = parseFloat(document.getElementById('custom_charge_amount').value) || 0;
            const processingFeeAmount = parseFloat(document.getElementById('processing_fee_amount').value) || 0;
            const insuranceFee = parseFloat(document.getElementById('insurance_fee').value) || 0;
            const frequency = document.getElementById('repayment_frequency').value;
            
            // Calculate number of installments
            let tenureInMonths = parseFloat(document.getElementById('loan_tenure_months').value) || 0;
            let numInstallments = tenureValue; // For daily/weekly, tenureValue = number of installments
            
            if (tenureUnit === 'months') {
                numInstallments = tenureValue;
            } else if (tenureUnit === 'quarters') {
                numInstallments = tenureValue;
            }
            
            // Calculate interest
            let totalInterest = 0;
            let perInstallment = 0;
            
            if (loanAmount > 0 && numInstallments > 0) {
                if (customMode && customRepayment > 0) {
                    // Custom repayment mode: user defines per-installment amount
                    perInstallment = customRepayment;
                    let totalRepayment = customRepayment * numInstallments;
                    totalInterest = Math.max(0, totalRepayment - loanAmount - customChargeAmount);
                } else {
                    // Standard calculation
                    const rate = interestRate / 100;
                    
                    if (calcMethod === 'flat') {
                        // For daily/weekly: rate is total % for the loan period
                        // For monthly/quarterly: rate is per annum
                        if (frequency === 'daily' || frequency === 'weekly') {
                            totalInterest = loanAmount * rate;
                        } else {
                            totalInterest = loanAmount * rate * (tenureInMonths / 12);
                        }
                        perInstallment = (loanAmount + totalInterest + customChargeAmount) / numInstallments;
                    } else {
                        // Reducing balance
                        let periodicRate;
                        // For daily/weekly: rate is total % for the loan period, divide by installments
                        if (frequency === 'daily' || frequency === 'weekly') {
                            periodicRate = rate / numInstallments;
                        } else if (frequency === 'quarterly') {
                            periodicRate = rate / 4;
                        } else {
                            periodicRate = rate / 12;
                        }
                        
                        if (periodicRate > 0) {
                            perInstallment = loanAmount * (periodicRate * Math.pow(1 + periodicRate, numInstallments)) / (Math.pow(1 + periodicRate, numInstallments) - 1);
                            totalInterest = (perInstallment * numInstallments) - loanAmount;
                            perInstallment = (perInstallment * numInstallments + customChargeAmount) / numInstallments;
                        } else {
                            perInstallment = (loanAmount + customChargeAmount) / numInstallments;
                        }
                    }
                }
            }
            
            const totalCharges = totalInterest + customChargeAmount;
            const totalRepayment = loanAmount + totalCharges;
            
            // Update display
            document.getElementById('summary_loan_amount').textContent = 'TZS ' + numberFormat(loanAmount);
            document.getElementById('summary_total_charges').textContent = 'TZS ' + numberFormat(totalCharges);
            document.getElementById('summary_total_repayment').textContent = 'TZS ' + numberFormat(totalRepayment);
            document.getElementById('summary_per_installment').textContent = 'TZS ' + numberFormat(perInstallment);
            document.getElementById('summary_installment_count').textContent = numInstallments + ' installments';
            
            // Detail row
            document.getElementById('detail_installments').textContent = numInstallments;
            document.getElementById('detail_interest').textContent = 'TZS ' + numberFormat(totalInterest);
            document.getElementById('detail_custom_charge').textContent = 'TZS ' + numberFormat(customChargeAmount);
            document.getElementById('detail_processing_fee').textContent = 'TZS ' + numberFormat(processingFeeAmount);
            document.getElementById('detail_insurance').textContent = 'TZS ' + numberFormat(insuranceFee);
        }
        
        // ===== FORM SUBMISSION =====
        document.getElementById('loan_form').addEventListener('submit', function(e) {
            if (!validateWizardStep(3)) {
                e.preventDefault();
                showWizardStep(3);
                return;
            }
            updateTenureMonths();
            calculateProcessingFee();
            updateCustomCharge();
        });
        
        // ===== HELPERS =====
        function numberFormat(num) {
            return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        
        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
        
        // ===== INIT =====
        showWizardStep(1);

        @if(old('loan_product_id'))
            document.getElementById('loan_product_id').dispatchEvent(new Event('change'));
        @endif
        
        if (customToggle.checked) {
            setCustomMode(true);
        } else {
            setCustomMode(false);
        }
        
        updateSummary();
    </script>
</x-app-shell>
