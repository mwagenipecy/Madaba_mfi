<x-app-shell title="Edit Loan - {{ $loan->loan_number }}" header="Edit Loan">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Edit Loan - {{ $loan->loan_number }}</h1>
                        <a href="{{ route('loans.show', $loan) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Back to Loan Details
                        </a>
                    </div>

                    <form method="POST" action="{{ route('loans.update', $loan) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="loan_amount" class="block text-sm font-medium text-gray-700 mb-2">Loan Amount</label>
                                    <input type="number" step="0.01" name="loan_amount" id="loan_amount" 
                                           value="{{ old('loan_amount', $loan->loan_amount) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           required>
                                    @error('loan_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="interest_rate" class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (%)</label>
                                    <input type="number" step="0.01" name="interest_rate" id="interest_rate" 
                                           value="{{ old('interest_rate', $loan->interest_rate) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           required>
                                    @error('interest_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="loan_tenure_months" class="block text-sm font-medium text-gray-700 mb-2">Loan Tenure (Months)</label>
                                    <input type="number" name="loan_tenure_months" id="loan_tenure_months" 
                                           value="{{ old('loan_tenure_months', $loan->loan_tenure_months) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           required>
                                    @error('loan_tenure_months')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="repayment_frequency" class="block text-sm font-medium text-gray-700 mb-2">Repayment Frequency</label>
                                    <select name="repayment_frequency" id="repayment_frequency" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            required>
                                        <option value="daily" {{ old('repayment_frequency', $loan->repayment_frequency) == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ old('repayment_frequency', $loan->repayment_frequency) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('repayment_frequency', $loan->repayment_frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="quarterly" {{ old('repayment_frequency', $loan->repayment_frequency) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    </select>
                                    @error('repayment_frequency')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="interest_calculation_method" class="block text-sm font-medium text-gray-700 mb-2">Interest Calculation Method</label>
                                    <select name="interest_calculation_method" id="interest_calculation_method" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            required>
                                        <option value="flat" {{ old('interest_calculation_method', $loan->interest_calculation_method) == 'flat' ? 'selected' : '' }}>Flat Rate</option>
                                        <option value="reducing_balance" {{ old('interest_calculation_method', $loan->interest_calculation_method) == 'reducing_balance' ? 'selected' : '' }}>Reducing Balance</option>
                                    </select>
                                    @error('interest_calculation_method')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select name="status" id="status" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            required>
                                        <option value="pending" {{ old('status', $loan->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="under_review" {{ old('status', $loan->status) == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                        <option value="approved" {{ old('status', $loan->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ old('status', $loan->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="disbursed" {{ old('status', $loan->status) == 'disbursed' ? 'selected' : '' }}>Disbursed</option>
                                        <option value="active" {{ old('status', $loan->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="overdue" {{ old('status', $loan->status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                        <option value="completed" {{ old('status', $loan->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status', $loan->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
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
                                           value="{{ old('processing_fee', $loan->processing_fee) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('processing_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="insurance_fee" class="block text-sm font-medium text-gray-700 mb-2">Insurance Fee</label>
                                    <input type="number" step="0.01" name="insurance_fee" id="insurance_fee" 
                                           value="{{ old('insurance_fee', $loan->insurance_fee) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('insurance_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="late_fee" class="block text-sm font-medium text-gray-700 mb-2">Late Fee</label>
                                    <input type="number" step="0.01" name="late_fee" id="late_fee" 
                                           value="{{ old('late_fee', $loan->late_fee) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('late_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="penalty_fee" class="block text-sm font-medium text-gray-700 mb-2">Penalty Fee</label>
                                    <input type="number" step="0.01" name="penalty_fee" id="penalty_fee" 
                                           value="{{ old('penalty_fee', $loan->penalty_fee) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('penalty_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Collateral Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Collateral Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="requires_collateral" value="1" 
                                               {{ old('requires_collateral', $loan->requires_collateral) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Requires Collateral</span>
                                    </label>
                                </div>

                                <div>
                                    <label for="collateral_description" class="block text-sm font-medium text-gray-700 mb-2">Collateral Description</label>
                                    <textarea name="collateral_description" id="collateral_description" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('collateral_description', $loan->collateral_description) }}</textarea>
                                    @error('collateral_description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="collateral_value" class="block text-sm font-medium text-gray-700 mb-2">Collateral Value</label>
                                    <input type="number" step="0.01" name="collateral_value" id="collateral_value" 
                                           value="{{ old('collateral_value', $loan->collateral_value) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('collateral_value')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="collateral_location" class="block text-sm font-medium text-gray-700 mb-2">Collateral Location</label>
                                    <input type="text" name="collateral_location" id="collateral_location" 
                                           value="{{ old('collateral_location', $loan->collateral_location) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('collateral_location')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="loan_purpose" class="block text-sm font-medium text-gray-700 mb-2">Loan Purpose</label>
                                    <textarea name="loan_purpose" id="loan_purpose" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('loan_purpose', $loan->loan_purpose) }}</textarea>
                                    @error('loan_purpose')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                    <textarea name="notes" id="notes" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('notes', $loan->notes) }}</textarea>
                                    @error('notes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('loans.show', $loan) }}" 
                               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                Update Loan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>
