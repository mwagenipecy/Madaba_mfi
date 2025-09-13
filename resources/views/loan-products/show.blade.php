<x-app-shell title="Loan Product - {{ $loanProduct->name }}" header="Loan Product Details">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $loanProduct->name }}</h1>
                            <p class="text-gray-600">{{ $loanProduct->code }}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $loanProduct->status_badge_color }}">
                                {{ ucfirst($loanProduct->status) }}
                            </span>
                            @if($loanProduct->is_featured)
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Featured
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('loan-products.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Back to Products
                        </a>
                        <a href="{{ route('loan-products.edit', $loanProduct) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Edit Product
                        </a>
                        <a href="{{ route('loans.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Create Loan
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Product Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Product Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loanProduct->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Product Code</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loanProduct->code }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loanProduct->description ?? 'No description provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Terms -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Loan Terms</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Loan Amount Range</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loanProduct->formatted_min_amount }} - {{ $loanProduct->formatted_max_amount }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Interest Rate</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loanProduct->formatted_interest_rate }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Interest Type</label>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $loanProduct->interest_type_badge_color }}">
                                            {{ ucfirst($loanProduct->interest_type) }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Interest Calculation</label>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $loanProduct->interest_calculation_method_badge_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $loanProduct->interest_calculation_method)) }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tenure Range</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loanProduct->formatted_tenure_range }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Repayment Frequency</label>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $loanProduct->repayment_frequency_badge_color }}">
                                            {{ ucfirst($loanProduct->repayment_frequency) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fees and Charges -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Fees and Charges</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Processing Fee</label>
                                    <p class="mt-1 text-sm text-gray-900">TZS {{ number_format($loanProduct->processing_fee, 2) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Late Fee</label>
                                    <p class="mt-1 text-sm text-gray-900">TZS {{ number_format($loanProduct->late_fee, 2) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Grace Period</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loanProduct->grace_period_days }} days</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Collateral Requirements -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Collateral Requirements</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Requires Collateral</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $loanProduct->requires_collateral ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $loanProduct->requires_collateral ? 'Yes' : 'No' }}
                                        </span>
                                    </p>
                                </div>
                                @if($loanProduct->requires_collateral)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Collateral Ratio</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loanProduct->collateral_ratio }}%</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Eligibility Criteria -->
                    @if($loanProduct->eligibility_criteria && count($loanProduct->eligibility_criteria) > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Eligibility Criteria</h2>
                            <ul class="list-disc list-inside space-y-2">
                                @foreach($loanProduct->eligibility_criteria as $criteria)
                                <li class="text-sm text-gray-900">{{ $criteria }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <!-- Required Documents -->
                    @if($loanProduct->required_documents && count($loanProduct->required_documents) > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Required Documents</h2>
                            <ul class="list-disc list-inside space-y-2">
                                @foreach($loanProduct->required_documents as $document)
                                <li class="text-sm text-gray-900">{{ $document }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Product Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Summary</h2>
                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Status</span>
                                    <span class="text-sm font-medium text-gray-900">{{ ucfirst($loanProduct->status) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Interest Rate</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $loanProduct->formatted_interest_rate }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Min Amount</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $loanProduct->formatted_min_amount }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Max Amount</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $loanProduct->formatted_max_amount }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Tenure</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $loanProduct->formatted_tenure_range }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Collateral Required</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $loanProduct->requires_collateral ? 'Yes' : 'No' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                            <div class="space-y-3">
                                <a href="{{ route('loans.create') }}" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-center block">
                                    Create New Loan
                                </a>
                                <a href="{{ route('loan-products.edit', $loanProduct) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-center block">
                                    Edit Product
                                </a>
                                <a href="{{ route('loan-products.index') }}" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-center block">
                                    View All Products
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Product Statistics -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Statistics</h2>
                            <div class="space-y-4">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900">0</p>
                                    <p class="text-sm text-gray-600">Active Loans</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900">0</p>
                                    <p class="text-sm text-gray-600">Total Disbursed</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900">0</p>
                                    <p class="text-sm text-gray-600">Pending Applications</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>
