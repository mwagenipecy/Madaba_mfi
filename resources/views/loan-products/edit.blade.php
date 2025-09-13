<x-app-shell title="Edit Loan Product - {{ $loanProduct->name }}" header="Edit Loan Product">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Edit Loan Product - {{ $loanProduct->name }}</h1>
                        <a href="{{ route('loan-products.show', $loanProduct) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Back to Product Details
                        </a>
                    </div>

                    <form method="POST" action="{{ route('loan-products.update', $loanProduct) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                                    <input type="text" name="name" id="name" 
                                           value="{{ old('name', $loanProduct->name) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           required>
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Product Code</label>
                                    <input type="text" name="code" id="code" 
                                           value="{{ old('code', $loanProduct->code) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           required>
                                    @error('code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="description" id="description" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('description', $loanProduct->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Loan Amount Range -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Loan Amount Range</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="min_amount" class="block text-sm font-medium text-gray-700 mb-2">Minimum Amount</label>
                                    <input type="number" step="0.01" name="min_amount" id="min_amount" 
                                           value="{{ old('min_amount', $loanProduct->min_amount) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           required>
                                    @error('min_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="max_amount" class="block text-sm font-medium text-gray-700 mb-2">Maximum Amount</label>
                                    <input type="number" step="0.01" name="max_amount" id="max_amount" 
                                           value="{{ old('max_amount', $loanProduct->max_amount) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           required>
                                    @error('max_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Interest Settings -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Interest Settings</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="interest_rate" class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (%)</label>
                                    <input type="number" step="0.01" name="interest_rate" id="interest_rate" 
                                           value="{{ old('interest_rate', $loanProduct->interest_rate) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           required>
                                    @error('interest_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="interest_type" class="block text-sm font-medium text-gray-700 mb-2">Interest Type</label>
                                    <select name="interest_type" id="interest_type" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            required>
                                        <option value="fixed" {{ old('interest_type', $loanProduct->interest_type) == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                        <option value="variable" {{ old('interest_type', $loanProduct->interest_type) == 'variable' ? 'selected' : '' }}>Variable</option>
                                    </select>
                                    @error('interest_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="interest_calculation_method" class="block text-sm font-medium text-gray-700 mb-2">Calculation Method</label>
                                    <select name="interest_calculation_method" id="interest_calculation_method" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            required>
                                        <option value="flat" {{ old('interest_calculation_method', $loanProduct->interest_calculation_method) == 'flat' ? 'selected' : '' }}>Flat Rate</option>
                                        <option value="reducing" {{ old('interest_calculation_method', $loanProduct->interest_calculation_method) == 'reducing' ? 'selected' : '' }}>Reducing Balance</option>
                                    </select>
                                    @error('interest_calculation_method')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Loan Tenure -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Loan Tenure</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="min_tenure_months" class="block text-sm font-medium text-gray-700 mb-2">Minimum Tenure (Months)</label>
                                    <input type="number" name="min_tenure_months" id="min_tenure_months" 
                                           value="{{ old('min_tenure_months', $loanProduct->min_tenure_months) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           required>
                                    @error('min_tenure_months')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="max_tenure_months" class="block text-sm font-medium text-gray-700 mb-2">Maximum Tenure (Months)</label>
                                    <input type="number" name="max_tenure_months" id="max_tenure_months" 
                                           value="{{ old('max_tenure_months', $loanProduct->max_tenure_months) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           required>
                                    @error('max_tenure_months')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Repayment Settings -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Repayment Settings</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="repayment_frequency" class="block text-sm font-medium text-gray-700 mb-2">Repayment Frequency</label>
                                    <select name="repayment_frequency" id="repayment_frequency" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            required>
                                        <option value="daily" {{ old('repayment_frequency', $loanProduct->repayment_frequency) == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ old('repayment_frequency', $loanProduct->repayment_frequency) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('repayment_frequency', $loanProduct->repayment_frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="quarterly" {{ old('repayment_frequency', $loanProduct->repayment_frequency) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    </select>
                                    @error('repayment_frequency')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="grace_period_days" class="block text-sm font-medium text-gray-700 mb-2">Grace Period (Days)</label>
                                    <input type="number" name="grace_period_days" id="grace_period_days" 
                                           value="{{ old('grace_period_days', $loanProduct->grace_period_days) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('grace_period_days')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Fees and Charges -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Fees and Charges</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="processing_fee" class="block text-sm font-medium text-gray-700 mb-2">Processing Fee</label>
                                    <input type="number" step="0.01" name="processing_fee" id="processing_fee" 
                                           value="{{ old('processing_fee', $loanProduct->processing_fee) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('processing_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="late_fee" class="block text-sm font-medium text-gray-700 mb-2">Late Fee</label>
                                    <input type="number" step="0.01" name="late_fee" id="late_fee" 
                                           value="{{ old('late_fee', $loanProduct->late_fee) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('late_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Collateral Requirements -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Collateral Requirements</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="requires_collateral" value="1" 
                                               {{ old('requires_collateral', $loanProduct->requires_collateral) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Requires Collateral</span>
                                    </label>
                                </div>

                                <div id="collateral-ratio-field" style="display: {{ old('requires_collateral', $loanProduct->requires_collateral) ? 'block' : 'none' }}">
                                    <label for="collateral_ratio" class="block text-sm font-medium text-gray-700 mb-2">Collateral Ratio (%)</label>
                                    <input type="number" step="0.01" name="collateral_ratio" id="collateral_ratio" 
                                           value="{{ old('collateral_ratio', $loanProduct->collateral_ratio) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('collateral_ratio')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Product Settings -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Settings</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select name="status" id="status" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            required>
                                        <option value="active" {{ old('status', $loanProduct->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $loanProduct->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ old('status', $loanProduct->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                                    <input type="number" name="sort_order" id="sort_order" 
                                           value="{{ old('sort_order', $loanProduct->sort_order) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('sort_order')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_featured" value="1" 
                                               {{ old('is_featured', $loanProduct->is_featured) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Featured Product</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('loan-products.show', $loanProduct) }}" 
                               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                Update Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show/hide collateral ratio field based on requires_collateral checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const requiresCollateralCheckbox = document.querySelector('input[name="requires_collateral"]');
            const collateralRatioField = document.getElementById('collateral-ratio-field');
            
            function toggleCollateralRatio() {
                if (requiresCollateralCheckbox.checked) {
                    collateralRatioField.style.display = 'block';
                } else {
                    collateralRatioField.style.display = 'none';
                }
            }
            
            requiresCollateralCheckbox.addEventListener('change', toggleCollateralRatio);
            
            // Initial check
            toggleCollateralRatio();
        });
    </script>
</x-app-shell>
