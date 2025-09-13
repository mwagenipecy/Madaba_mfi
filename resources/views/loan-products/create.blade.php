<x-app-shell title="Create Loan Product" header="Create Loan Product">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Loan Product</h1>
                    <p class="text-gray-600 mt-1">Define a new loan product for your organization</p>
                </div>
                <a href="{{ route('loan-products.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Back to Products
                </a>
            </div>
        </div>

        <!-- Create Loan Product Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ route('loan-products.store') }}" method="POST" class="space-y-8">
                @csrf
                
                <!-- Basic Information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Basic Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Product Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                            <input type="text" id="name" name="name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="Enter product name">
                        </div>

                        <!-- Product Code -->
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Product Code *</label>
                            <div class="flex">
                                <input type="text" id="code" name="code" required 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Enter product code">
                                <button type="button" onclick="generateCode()" class="ml-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                    Generate
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Enter product description"></textarea>
                    </div>
                </div>

                <!-- Loan Amount & Terms -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Loan Amount & Terms</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Minimum Amount -->
                        <div>
                            <label for="min_amount" class="block text-sm font-medium text-gray-700 mb-2">Minimum Amount *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">TZS</span>
                                </div>
                                <input type="number" id="min_amount" name="min_amount" step="0.01" min="0" required
                                       class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <!-- Maximum Amount -->
                        <div>
                            <label for="max_amount" class="block text-sm font-medium text-gray-700 mb-2">Maximum Amount *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">TZS</span>
                                </div>
                                <input type="number" id="max_amount" name="max_amount" step="0.01" min="0" required
                                       class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <!-- Interest Rate -->
                        <div>
                            <label for="interest_rate" class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (%) *</label>
                            <div class="relative">
                                <input type="number" id="interest_rate" name="interest_rate" step="0.01" min="0" max="100" required
                                       class="w-full pr-8 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Interest Type -->
                        <div>
                            <label for="interest_type" class="block text-sm font-medium text-gray-700 mb-2">Interest Type *</label>
                            <select id="interest_type" name="interest_type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select interest type</option>
                                <option value="fixed">Fixed Rate</option>
                                <option value="variable">Variable Rate</option>
                            </select>
                        </div>

                        <!-- Interest Calculation Method -->
                        <div>
                            <label for="interest_calculation_method" class="block text-sm font-medium text-gray-700 mb-2">Interest Calculation Method *</label>
                            <select id="interest_calculation_method" name="interest_calculation_method" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select calculation method</option>
                                <option value="flat">Flat Rate (Interest on original principal)</option>
                                <option value="reducing">Reducing Balance (Interest on outstanding balance)</option>
                            </select>
                        </div>

                        <!-- Minimum Tenure -->
                        <div>
                            <label for="min_tenure_months" class="block text-sm font-medium text-gray-700 mb-2">Minimum Tenure (Months) *</label>
                            <input type="number" id="min_tenure_months" name="min_tenure_months" min="1" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="1">
                        </div>

                        <!-- Maximum Tenure -->
                        <div>
                            <label for="max_tenure_months" class="block text-sm font-medium text-gray-700 mb-2">Maximum Tenure (Months) *</label>
                            <input type="number" id="max_tenure_months" name="max_tenure_months" min="1" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="12">
                        </div>
                    </div>
                </div>

                <!-- Fees & Charges -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Fees & Charges</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Processing Fee -->
                        <div>
                            <label for="processing_fee" class="block text-sm font-medium text-gray-700 mb-2">Processing Fee</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">TZS</span>
                                </div>
                                <input type="number" id="processing_fee" name="processing_fee" step="0.01" min="0"
                                       class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <!-- Late Fee -->
                        <div>
                            <label for="late_fee" class="block text-sm font-medium text-gray-700 mb-2">Late Payment Fee</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">TZS</span>
                                </div>
                                <input type="number" id="late_fee" name="late_fee" step="0.01" min="0"
                                       class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Repayment & Other Settings -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Repayment & Other Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Repayment Frequency -->
                        <div>
                            <label for="repayment_frequency" class="block text-sm font-medium text-gray-700 mb-2">Repayment Frequency *</label>
                            <select id="repayment_frequency" name="repayment_frequency" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select frequency</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                        </div>

                        <!-- Grace Period -->
                        <div>
                            <label for="grace_period_days" class="block text-sm font-medium text-gray-700 mb-2">Grace Period (Days)</label>
                            <input type="number" id="grace_period_days" name="grace_period_days" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="0">
                        </div>

                        <!-- Requires Collateral -->
                        <div class="flex items-center">
                            <input type="checkbox" id="requires_collateral" name="requires_collateral" value="1"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <label for="requires_collateral" class="ml-2 block text-sm text-gray-900">
                                Requires Collateral
                            </label>
                        </div>

                        <!-- Collateral Ratio -->
                        <div id="collateral_ratio_field" style="display: none;">
                            <label for="collateral_ratio" class="block text-sm font-medium text-gray-700 mb-2">Collateral Ratio (%)</label>
                            <div class="relative">
                                <input type="number" id="collateral_ratio" name="collateral_ratio" step="0.01" min="0" max="100"
                                       class="w-full pr-8 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Configuration -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Account Configuration</h3>
                    <p class="text-sm text-gray-600">Configure the accounts that will be used for loan disbursement, collection, and revenue tracking.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Disbursement Account -->
                        <div>
                            <label for="disbursement_account_id" class="block text-sm font-medium text-gray-700 mb-2">Disbursement Account *</label>
                            <select id="disbursement_account_id" name="disbursement_account_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select disbursement account</option>
                                @foreach($accounts as $account)
                                    @if($account->accountType->name === 'Liability')
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->branch ? $account->branch->name : 'Main' }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Collection Account -->
                        <div>
                            <label for="collection_account_id" class="block text-sm font-medium text-gray-700 mb-2">Collection Account *</label>
                            <select id="collection_account_id" name="collection_account_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select collection account</option>
                                @foreach($accounts as $account)
                                    @if($account->accountType->name === 'Assets')
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->branch ? $account->branch->name : 'Main' }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Interest Revenue Account -->
                        <div>
                            <label for="interest_revenue_account_id" class="block text-sm font-medium text-gray-700 mb-2">Interest Revenue Account *</label>
                            <select id="interest_revenue_account_id" name="interest_revenue_account_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select interest revenue account</option>
                                @foreach($accounts as $account)
                                    @if($account->accountType->name === 'Revenue')
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->branch ? $account->branch->name : 'Main' }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Principal Account -->
                        <div>
                            <label for="principal_account_id" class="block text-sm font-medium text-gray-700 mb-2">Principal Account *</label>
                            <select id="principal_account_id" name="principal_account_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select principal account</option>
                                @foreach($accounts as $account)
                                    @if($account->accountType->name === 'Assets')
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->branch ? $account->branch->name : 'Main' }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Status & Settings -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Status & Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select id="status" name="status" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                            <input type="number" id="sort_order" name="sort_order" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="0">
                        </div>
                    </div>

                    <!-- Featured Product -->
                    <div class="flex items-center">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1"
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="is_featured" class="ml-2 block text-sm text-gray-900">
                            Featured Product (will be highlighted in listings)
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('loan-products.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                        Create Loan Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle collateral ratio field
        document.getElementById('requires_collateral').addEventListener('change', function() {
            const collateralRatioField = document.getElementById('collateral_ratio_field');
            const collateralRatioInput = document.getElementById('collateral_ratio');
            
            if (this.checked) {
                collateralRatioField.style.display = 'block';
                collateralRatioInput.required = true;
            } else {
                collateralRatioField.style.display = 'none';
                collateralRatioInput.required = false;
                collateralRatioInput.value = '';
            }
        });

        // Generate product code
        function generateCode() {
            fetch('{{ route("loan-products.generate-code") }}')
                .then(response => response.text())
                .then(code => {
                    document.getElementById('code').value = code;
                })
                .catch(error => {
                    console.error('Error generating code:', error);
                    alert('Error generating product code');
                });
        }
    </script>
</x-app-shell>
