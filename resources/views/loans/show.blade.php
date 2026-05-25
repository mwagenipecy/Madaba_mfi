<x-app-shell title="Loan Details - {{ $loan->loan_number }}" header="Loan Details">
    @php
        $loanShowTabs = [
            1 => 'Application',
            2 => 'Review & Assessment',
            3 => 'Assessment & Approval',
            4 => 'Schedule & Activity',
        ];
        $loanShowDefaultTab = match ($loan->status) {
            'pending' => 1,
            'under_review' => 2,
            'assessed' => 3,
            'active', 'overdue', 'approved', 'disbursed' => 4,
            'completed' => 4,
            'rejected' => 1,
            default => 1,
        };
        $loanShowTabHints = [
            1 => 'Loan terms and client profile',
            2 => 'Collateral, documents, and schedule adjustments',
            3 => 'Workflow progress, notes, and approval readiness',
            4 => 'Repayment schedule and transaction history',
        ];
    @endphp
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $loan->loan_number }}</h1>
                            <p class="text-gray-600">{{ $loan->client->display_name ?? 'N/A' }}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $loan->status_badge_color }}">
                                {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
                            </span>
                            @if($loan->approval_status)
                                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $loan->approval_status_badge_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $loan->approval_status)) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('loans.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Back to Loans
                        </a>
                        
                        {{-- STEP 1: Pending → Start Review --}}
                        @if($loan->status === 'pending')
                            <form method="POST" action="{{ route('loans.under-review', $loan) }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    <span>Start Review</span>
                                </button>
                            </form>
                            @if(auth()->user()->role === 'admin')
                                <button onclick="openRejectionModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Reject Loan
                                </button>
                            @endif
                        @endif
                        
                        {{-- STEP 2: Under Review → Upload docs, assess, then Complete Assessment --}}
                        @if($loan->status === 'under_review')
                            <button onclick="openAssessmentModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Assessment Completed</span>
                            </button>
                            @if(auth()->user()->role === 'admin')
                                <button onclick="openRejectionModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Reject Loan
                                </button>
                            @endif
                        @endif
                        
                        {{-- STEP 3: Assessed → Approve (generates schedule & activates) --}}
                        @if($loan->status === 'assessed')
                            @if(auth()->user()->role === 'admin')
                                <button onclick="openApprovalModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Approve Loan</span>
                                </button>
                                <button onclick="openRejectionModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Reject Loan
                                </button>
                            @endif
                        @endif
                        
                        {{-- Active/Overdue → Repayment + Close Loan --}}
                        @if(in_array($loan->status, ['active', 'overdue']))
                            <a href="{{ route('loans.repayments') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <span>Process Repayment</span>
                            </a>
                            @if(in_array(auth()->user()->role, ['admin', 'manager', 'super_admin']))
                                <button onclick="openCloseLoanModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <span>Close Loan</span>
                                </button>
                            @endif
                        @endif
                        
                        {{-- Approved (legacy) → Disbursement --}}
                        @if($loan->status === 'approved')
                            <a href="{{ route('loans.disbursements') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Process Disbursement
                            </a>
                        @endif
                    </div>
                    
                    @include('loans.partials.show-tabs-nav')
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="loan-show-tab-panel space-y-6" id="loan-tab-panel-1" data-loan-tab="1" role="tabpanel">
                    <!-- Loan Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Loan Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Loan Amount</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->formatted_loan_amount }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Approved Amount</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->formatted_approved_amount }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Total Interest Rate</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ number_format($totalInterestPercentage, 2) }}% of loan amount</p>
                                    @if(in_array($loan->repayment_frequency, ['daily', 'weekly']))
                                        <p class="mt-1 text-xs text-gray-500">({{ $loan->interest_rate }}% for loan period)</p>
                                    @else
                                        <p class="mt-1 text-xs text-gray-500">({{ $loan->interest_rate }}% per annum)</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Interest Calculation</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $loan->interest_calculation_method)) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Loan Tenure</label>
                                    @php
                                        $frequency = $loan->repayment_frequency ?? 'monthly';
                                        $tenureMonths = $loan->loan_tenure_months;
                                        if ($frequency === 'daily') {
                                            $tenureDisplay = ($tenureMonths * 30) . ' days';
                                        } elseif ($frequency === 'weekly') {
                                            $tenureDisplay = ($tenureMonths * 4) . ' weeks';
                                        } else {
                                            $tenureDisplay = $tenureMonths . ' months';
                                        }
                                    @endphp
                                    <p class="mt-1 text-sm text-gray-900">{{ $tenureDisplay }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Repayment Frequency</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst($loan->repayment_frequency) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Total Interest</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->formatted_total_amount }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        @if(($loan->repayment_frequency ?? 'monthly') === 'daily')
                                            Daily Payment
                                        @elseif(($loan->repayment_frequency ?? 'monthly') === 'weekly')
                                            Weekly Payment
                                        @elseif(($loan->repayment_frequency ?? 'monthly') === 'quarterly')
                                            Quarterly Payment
                                        @else
                                            Monthly Payment
                                        @endif
                                    </label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->formatted_monthly_payment }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Client Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Client Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->client->display_name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client Number</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->client->client_number ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->client->phone ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->client->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $clientScoring = is_array($loan->metadata) ? ($loan->metadata['client_scoring'] ?? null) : null;
                    @endphp
                    @if($clientScoring)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-indigo-400">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Credit Assessment</h2>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-500">Score</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $clientScoring['score'] ?? '—' }}/100</p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-500">Band</p>
                                    <p class="font-semibold text-gray-900">{{ $clientScoring['band_label'] ?? '—' }}</p>
                                </div>
                                <div class="p-3 bg-green-50 rounded-lg">
                                    <p class="text-xs text-green-700">Recommended max</p>
                                    <p class="font-semibold text-green-800">TZS {{ isset($clientScoring['recommended_max_loan']) ? number_format($clientScoring['recommended_max_loan'], 2) : '—' }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-500">Requested</p>
                                    <p class="font-semibold text-gray-900">TZS {{ number_format($loan->loan_amount, 2) }}</p>
                                </div>
                            </div>
                            @if(!empty($clientScoring['collateral_boost']))
                                <p class="mt-3 text-sm text-green-700">Includes TZS {{ number_format($clientScoring['collateral_boost'], 2) }} collateral boost.</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    </div>

                    <div class="loan-show-tab-panel hidden space-y-6" id="loan-tab-panel-2" data-loan-tab="2" role="tabpanel">
                    <!-- Collateral -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 {{ $loan->pledgedCollateral || $loan->requires_collateral ? 'border-green-500' : 'border-gray-300' }}">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Collateral</h2>
                                    <p class="text-sm text-gray-500 mt-1">Optional — attach a registered asset to boost eligibility, or record manual details during assessment.</p>
                                </div>
                                @if($loan->pledgedCollateral)
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Pledged</span>
                                @elseif($loan->requires_collateral)
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">On file</span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">None</span>
                                @endif
                            </div>

                            @if($loan->pledgedCollateral)
                                @php $col = $loan->pledgedCollateral; @endphp
                                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm space-y-2">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="font-semibold text-blue-900">{{ $col->title }}</p>
                                        <span class="text-xs font-mono text-blue-700">{{ $col->reference_number }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-blue-800">
                                        <div><span class="text-blue-600">Type:</span> {{ $col->typeLabel() }}</div>
                                        <div><span class="text-blue-600">Value:</span> TZS {{ number_format($col->estimated_value, 2) }}</div>
                                        <div><span class="text-blue-600">Boost capacity:</span> TZS {{ number_format($col->lendingCapacity(), 2) }}</div>
                                    </div>
                                    @if($col->location)
                                        <div class="text-blue-800"><span class="text-blue-600">Location:</span> {{ $col->location }}</div>
                                    @endif
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-blue-200 text-blue-800">
                                        <div>
                                            <span class="text-blue-600">Registered by:</span>
                                            <span class="font-medium">{{ $col->creator?->name ?? 'Unknown' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-blue-600">Registered on:</span>
                                            {{ $col->created_at?->format('d M Y H:i') ?? '—' }}
                                        </div>
                                        @if($col->pledged_at)
                                            <div>
                                                <span class="text-blue-600">Attached on:</span>
                                                {{ $col->pledged_at->format('d M Y H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ route('collaterals.show', $col) }}" class="inline-block text-sm text-blue-700 hover:underline">View collateral record</a>
                                </div>
                            @elseif($loan->requires_collateral)
                                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm space-y-2">
                                    @if($loan->collateral_description)
                                        <p class="text-green-900">{{ $loan->collateral_description }}</p>
                                    @endif
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-green-800">
                                        @if($loan->collateral_value)
                                            <div><span class="text-green-600">Value:</span> TZS {{ number_format($loan->collateral_value, 2) }}</div>
                                        @endif
                                        @if($loan->collateral_location)
                                            <div><span class="text-green-600">Location:</span> {{ $loan->collateral_location }}</div>
                                        @endif
                                    </div>
                                </div>
                            @elseif($loan->status === 'under_review')
                                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
                                    <p>No collateral attached yet. When you complete the assessment, you can choose to:</p>
                                    <ul class="list-disc list-inside mt-2 space-y-1">
                                        <li>Skip collateral (no boost)</li>
                                        <li>Attach a registered item from the client&apos;s portfolio</li>
                                        <li>Enter manual collateral details</li>
                                    </ul>
                                    @if($availableCollaterals->isNotEmpty())
                                        <p class="mt-3 text-green-700 font-medium">{{ $availableCollaterals->count() }} registered item(s) available for this client.</p>
                                    @endif
                                    <p class="mt-3">
                                        <a href="{{ route('collaterals.create') }}?client_id={{ $loan->client_id }}&loan_id={{ $loan->loan_number }}" class="inline-flex items-center text-green-700 hover:underline font-medium">
                                            Register and attach collateral to this loan
                                        </a>
                                    </p>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No collateral recorded for this loan.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Adjust Schedule Section -->
                    @if($scheduleAdjustment['can_adjust'])
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Adjust Schedule</h2>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Current: <span class="font-medium text-gray-700">{{ $scheduleAdjustment['current_value'] }} {{ $scheduleAdjustment['unit_label'] }}</span>
                                        &middot; <span class="font-medium text-gray-700">{{ $scheduleAdjustment['total_installments'] }} installments</span>
                                        &middot; Frequency: <span class="font-medium text-gray-700">{{ ucfirst($scheduleAdjustment['frequency']) }}</span>
                                    </p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($scheduleAdjustment['frequency']) }}
                                </span>
                            </div>
                            
                            <form action="{{ route('loans.adjust-schedule', $loan) }}" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="tenure_value" class="block text-sm font-medium text-gray-700 mb-1">
                                            Number of {{ $scheduleAdjustment['unit_label'] }}
                                        </label>
                                        <input type="number" 
                                               name="tenure_value" 
                                               id="tenure_value" 
                                               value="{{ old('tenure_value', $scheduleAdjustment['current_value']) }}" 
                                               min="{{ $scheduleAdjustment['min_value'] }}" 
                                               max="{{ $scheduleAdjustment['max_value'] }}" 
                                               step="1"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               required>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Range: {{ $scheduleAdjustment['min_value'] }} - {{ $scheduleAdjustment['max_value'] }} {{ strtolower($scheduleAdjustment['unit_label']) }}
                                        </p>
                                    </div>
                                    <div>
                                        <label for="first_payment_date" class="block text-sm font-medium text-gray-700 mb-1">
                                            First Payment Date
                                        </label>
                                        <input type="date" 
                                               name="first_payment_date" 
                                               id="first_payment_date" 
                                               value="{{ old('first_payment_date', $loan->first_payment_date ? \Carbon\Carbon::parse($loan->first_payment_date)->format('Y-m-d') : '') }}"
                                               min="{{ now()->format('Y-m-d') }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">
                                            Leave blank to auto-calculate (30 days from now)
                                        </p>
                                    </div>
                                    <div class="flex items-end">
                                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <span>Recalculate Schedule</span>
                                        </button>
                                    </div>
                                </div>
                                
                                @if($scheduleAdjustment['frequency'] === 'daily')
                                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div class="text-sm text-blue-700">
                                            <p class="font-medium">Daily Repayment Schedule</p>
                                            <p class="mt-1">
                                                This loan uses daily repayments. Adjusting the number of days will recalculate the schedule with 
                                                <span id="preview_installments" class="font-semibold">{{ $scheduleAdjustment['current_value'] }}</span> daily installments.
                                                @if($loan->loan_amount > 0)
                                                    Each installment will be approximately 
                                                    <span id="preview_amount" class="font-semibold">TZS {{ number_format(($loan->loan_amount) / $scheduleAdjustment['current_value'], 2) }}</span>
                                                    (principal only).
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Documents Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Documents</h2>
                                <button onclick="openDocumentModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Upload Document
                                </button>
                            </div>
                            
                            @if($loan->documents && count($loan->documents) > 0)
                                <div class="space-y-3">
                                    @foreach($loan->documents as $document)
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                            <div class="flex items-center space-x-3">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $document['name'] ?? 'Document' }}</p>
                                                    <p class="text-sm text-gray-500">{{ $document['type'] ?? 'Unknown Type' }}</p>
                                                    @if(isset($document['description']) && $document['description'])
                                                        <p class="text-xs text-gray-400">{{ $document['description'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $document['status'] === 'approved' ? 'bg-green-100 text-green-800' : ($document['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($document['status'] ?? 'pending') }}
                                                </span>
                                                <a href="{{ route('loans.download-document', [$loan, $document['id']]) }}" class="text-blue-600 hover:text-blue-700 text-sm">Download</a>
                                                <button onclick="deleteDocument('{{ $document['id'] }}')" class="text-red-600 hover:text-red-700 text-sm">Delete</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-gray-500">No documents uploaded yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    </div>

                    <div class="loan-show-tab-panel hidden space-y-6" id="loan-tab-panel-3" data-loan-tab="3" role="tabpanel">
                    <!-- Comments Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Comments & Notes</h2>
                                <button onclick="openCommentModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Add Comment
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                @if($loan->returned_at)
                                    @php
                                        $returnNote = null;
                                        $marker = 'Returned to loan officer:';
                                        if (is_string($loan->notes)) {
                                            $pos = strrpos($loan->notes, $marker);
                                            if ($pos !== false) {
                                                $returnNote = trim(substr($loan->notes, $pos + strlen($marker)));
                                            }
                                        }
                                    @endphp
                                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 110-16 8 8 0 010 16z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-yellow-900">Returned to Loan Officer</p>
                                                <p class="text-xs text-yellow-700 mt-1">
                                                    by {{ $loan->returnedBy->name ?? 'Manager' }}
                                                    • {{ optional($loan->returned_at)->diffForHumans() }}
                                                </p>
                                                @if($returnNote)
                                                    <p class="text-sm text-yellow-800 mt-2">{{ $returnNote }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($loan->approval_notes)
                                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-blue-900">Approval Notes</p>
                                                <p class="text-sm text-blue-700 mt-1">{{ $loan->approval_notes }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($loan->rejection_reason)
                                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-red-900">Rejection Reason</p>
                                                <p class="text-sm text-red-700 mt-1">{{ $loan->rejection_reason }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Dynamic comments -->
                                @if($loan->comments && count($loan->comments) > 0)
                                    <div class="space-y-3">
                                        @foreach($loan->comments as $comment)
                                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                                <div class="flex items-start space-x-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                                            <span class="text-sm font-medium text-gray-700">{{ substr($comment['user_role'] ?? 'User', 0, 2) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="flex items-center space-x-2">
                                                            <p class="text-sm font-medium text-gray-900">{{ $comment['user_name'] ?? 'Unknown User' }}</p>
                                                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($comment['created_at'])->diffForHumans() }}</span>
                                                            @if(isset($comment['comment_type']) && $comment['comment_type'] !== 'general')
                                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                                    {{ ucfirst(str_replace('_', ' ', $comment['comment_type'])) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="text-sm text-gray-700 mt-1">{{ $comment['comment'] }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        <p class="text-gray-500">No comments yet</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Approval Workflow Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Loan Workflow</h2>
                            
                            @php
                                $statusOrder = ['pending', 'under_review', 'assessed', 'active'];
                                $currentIndex = array_search($loan->status, $statusOrder);
                                if ($currentIndex === false) $currentIndex = -1;
                                $isRejected = $loan->status === 'rejected';
                                $isCompleted = $loan->status === 'completed';
                            @endphp
                            
                            <div class="space-y-0">
                                {{-- Step 1: Application Submitted --}}
                                <div class="flex items-start space-x-4 relative">
                                    <div class="flex-shrink-0 flex flex-col items-center">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center z-10">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <div class="w-0.5 h-6 {{ $currentIndex >= 1 || $isRejected || $isCompleted ? 'bg-green-300' : 'bg-gray-200' }}"></div>
                                    </div>
                                    <div class="flex-1 pb-4">
                                        <p class="text-sm font-medium text-gray-900">Application Submitted</p>
                                        <p class="text-xs text-gray-500">{{ $loan->application_date->format('M d, Y') }} &middot; {{ $loan->client->display_name ?? 'Client' }}</p>
                                    </div>
                                </div>

                                {{-- Step 2: Under Review --}}
                                <div class="flex items-start space-x-4 relative">
                                    <div class="flex-shrink-0 flex flex-col items-center">
                                        @if($currentIndex >= 1 || $isRejected || $isCompleted)
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center z-10">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        @elseif($loan->status === 'under_review')
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center z-10 animate-pulse">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center z-10">
                                                <span class="text-xs font-medium text-gray-400">2</span>
                                            </div>
                                        @endif
                                        <div class="w-0.5 h-6 {{ $currentIndex >= 2 || $isRejected || $isCompleted ? 'bg-green-300' : 'bg-gray-200' }}"></div>
                                    </div>
                                    <div class="flex-1 pb-4">
                                        <p class="text-sm font-medium {{ $currentIndex >= 1 || $loan->status === 'under_review' ? 'text-gray-900' : 'text-gray-400' }}">Review & Assessment</p>
                                        @if($loan->status === 'under_review')
                                            <p class="text-xs text-blue-600">In progress — upload documents & verify information</p>
                                        @elseif($currentIndex >= 1 || $isCompleted)
                                            <p class="text-xs text-gray-500">Review completed</p>
                                        @else
                                            <p class="text-xs text-gray-400">Waiting to start</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 3: Assessment Completed --}}
                                <div class="flex items-start space-x-4 relative">
                                    <div class="flex-shrink-0 flex flex-col items-center">
                                        @if($currentIndex >= 2 || $isCompleted)
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center z-10">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        @elseif($loan->status === 'assessed')
                                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center z-10 animate-pulse">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center z-10">
                                                <span class="text-xs font-medium text-gray-400">3</span>
                                            </div>
                                        @endif
                                        <div class="w-0.5 h-6 {{ $currentIndex >= 3 || $isCompleted ? 'bg-green-300' : 'bg-gray-200' }}"></div>
                                    </div>
                                    <div class="flex-1 pb-4">
                                        <p class="text-sm font-medium {{ $currentIndex >= 2 || $loan->status === 'assessed' ? 'text-gray-900' : 'text-gray-400' }}">Assessment Completed</p>
                                        @if($loan->status === 'assessed')
                                            @php
                                                $assessmentMeta = is_array($loan->metadata) ? ($loan->metadata['assessment'] ?? null) : null;
                                            @endphp
                                            <p class="text-xs text-indigo-600">Ready for approval
                                                @if($assessmentMeta)
                                                    &middot; by {{ $assessmentMeta['completed_by_name'] ?? 'Officer' }}
                                                @endif
                                            </p>
                                        @elseif($currentIndex >= 2 || $isCompleted)
                                            @php
                                                $assessmentMeta = is_array($loan->metadata) ? ($loan->metadata['assessment'] ?? null) : null;
                                            @endphp
                                            <p class="text-xs text-gray-500">Completed
                                                @if($assessmentMeta)
                                                    by {{ $assessmentMeta['completed_by_name'] ?? 'Officer' }}
                                                @endif
                                            </p>
                                        @else
                                            <p class="text-xs text-gray-400">Pending assessment</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 4: Approved & Active --}}
                                <div class="flex items-start space-x-4 relative">
                                    <div class="flex-shrink-0 flex flex-col items-center">
                                        @if($currentIndex >= 3 || $isCompleted)
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center z-10">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        @elseif($isRejected)
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center z-10">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center z-10">
                                                <span class="text-xs font-medium text-gray-400">4</span>
                                            </div>
                                        @endif
                                        @if($isCompleted)
                                            <div class="w-0.5 h-6 bg-green-300"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 pb-4">
                                        @if($isRejected)
                                            <p class="text-sm font-medium text-red-700">Rejected</p>
                                            <p class="text-xs text-red-500">by {{ $loan->rejectedBy->name ?? 'Manager' }}</p>
                                        @elseif($currentIndex >= 3 || $isCompleted)
                                            <p class="text-sm font-medium text-gray-900">Approved & Active</p>
                                            <p class="text-xs text-gray-500">
                                                by {{ $loan->approvedBy->name ?? 'Manager' }}
                                                @if($loan->approval_date) on {{ $loan->approval_date->format('M d, Y') }} @endif
                                                &middot; Schedule saved
                                            </p>
                                        @else
                                            <p class="text-sm font-medium text-gray-400">Approval & Activation</p>
                                            <p class="text-xs text-gray-400">Pending</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 5: Completed/Closed (only if completed) --}}
                                @if($isCompleted)
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center z-10">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Loan Closed</p>
                                        <p class="text-xs text-gray-500">
                                            @if($loan->closure_date)
                                                on {{ $loan->closure_date->format('M d, Y') }}
                                            @endif
                                            @if($loan->closure_reason)
                                                &middot; {{ Str::limit($loan->closure_reason, 60) }}
                                            @endif
                                        </p>
                                        @php
                                            $closureMeta = is_array($loan->metadata) ? ($loan->metadata['closure'] ?? null) : null;
                                        @endphp
                                        @if($closureMeta)
                                            <div class="mt-2 p-2 bg-gray-50 rounded text-xs text-gray-600">
                                                @if(($closureMeta['return_amount'] ?? 0) > 0)
                                                    <span class="text-green-700">Returned: TZS {{ number_format($closureMeta['return_amount'], 2) }}</span>
                                                @endif
                                                @if(($closureMeta['forgiven_amount'] ?? 0) > 0)
                                                    <span class="ml-2 text-yellow-700">Forgiven: TZS {{ number_format($closureMeta['forgiven_amount'], 2) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Return to Officer button --}}
                            @if(in_array($loan->status, ['pending', 'under_review', 'assessed']) && in_array(auth()->user()->role, ['admin', 'manager', 'super_admin']))
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <button onclick="openReturnModal()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-sm">
                                    Return to Loan Officer
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    </div>

                    <div class="loan-show-tab-panel hidden space-y-6" id="loan-tab-panel-4" data-loan-tab="4" role="tabpanel">
                        @include('loans.partials.show-schedule-activity')
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Loan Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Loan Summary</h2>
                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Total Required to Pay</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $loan->formatted_total_required_repayment }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Outstanding Balance</span>
                                    <span class="text-sm font-medium text-gray-900">TZS {{ number_format($loan->calculated_outstanding_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Paid Amount</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $loan->formatted_paid_amount }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Progress</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $loan->progress_percentage }}%</span>
                                </div>
                                @if($loan->overdue_amount > 0)
                                <div class="flex justify-between">
                                    <span class="text-sm text-red-600">Overdue Amount</span>
                                    <span class="text-sm font-medium text-red-600">{{ $loan->formatted_overdue_amount }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Key Dates -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Key Dates</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Application Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->application_date->format('M d, Y') }}</p>
                                </div>
                                @if($loan->approval_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Approval Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->approval_date->format('M d, Y') }}</p>
                                </div>
                                @endif
                                @if($loan->disbursement_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Disbursement Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->disbursement_date->format('M d, Y') }}</p>
                                </div>
                                @endif
                                @if($loan->maturity_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Maturity Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->maturity_date->format('M d, Y') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Loan Officer -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Loan Officer</h2>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-900">{{ $loan->loanOfficer->name ?? 'Not assigned' }}</p>
                                @if($loan->loanOfficer)
                                <p class="text-sm text-gray-600">{{ $loan->loanOfficer->email ?? '' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Branch Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Branch</h2>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-900">{{ $loan->branch->name ?? 'Not assigned' }}</p>
                                @if($loan->branch)
                                <p class="text-sm text-gray-600">{{ $loan->branch->address ?? '' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Upload Modal -->
    <div id="documentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Upload Document</h3>
                    <button onclick="closeDocumentModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('loans.upload-document', $loan) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="document_type" class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                            <select name="document_type" id="document_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Select document type</option>
                                <option value="national_id">National ID</option>
                                <option value="passport">Passport</option>
                                <option value="driving_license">Driving License</option>
                                <option value="income_certificate">Income Certificate</option>
                                <option value="bank_statement">Bank Statement</option>
                                <option value="employment_letter">Employment Letter</option>
                                <option value="collateral_document">Collateral Document</option>
                                <option value="guarantor_document">Guarantor Document</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="document" class="block text-sm font-medium text-gray-700 mb-1">Select File</label>
                            <input type="file" name="document" id="document" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <p class="text-xs text-gray-500 mt-1">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max 10MB)</p>
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                            <textarea name="description" id="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Brief description of the document..."></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeDocumentModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                                Upload Document
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Comment Modal -->
    <div id="commentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Add Comment</h3>
                    <button onclick="closeCommentModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('loans.add-comment', $loan) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="comment_type" class="block text-sm font-medium text-gray-700 mb-1">Comment Type</label>
                            <select name="comment_type" id="comment_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="general">General</option>
                                <option value="internal">Internal Note</option>
                                <option value="client_communication">Client Communication</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                            <textarea name="comment" id="comment" required rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your comment..."></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeCommentModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                                Add Comment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Approve Loan</h3>
                    <button onclick="closeApprovalModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800">
                        Approving this loan will <strong>generate the payment schedule</strong> and <strong>set the loan to active</strong>.
                    </p>
                </div>
                
                <form action="{{ route('loans.approve', $loan) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="approved_amount" class="block text-sm font-medium text-gray-700 mb-1">Approved Amount (TZS)</label>
                            <input type="number" name="approved_amount" id="approved_amount" step="0.01" min="0" 
                                   value="{{ $loan->loan_amount }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label for="first_payment_date" class="block text-sm font-medium text-gray-700 mb-1">First Payment Date</label>
                            <input type="date" name="first_payment_date" id="approve_first_payment_date" 
                                   value="{{ $loan->first_payment_date ? \Carbon\Carbon::parse($loan->first_payment_date)->format('Y-m-d') : now()->addDay()->format('Y-m-d') }}"
                                   min="{{ now()->format('Y-m-d') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <p class="mt-1 text-xs text-gray-500">When the first repayment is due</p>
                        </div>
                        
                        <div>
                            <label for="approval_notes" class="block text-sm font-medium text-gray-700 mb-1">Approval Notes</label>
                            <textarea name="approval_notes" id="approval_notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Enter approval notes..."></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeApprovalModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                                Approve & Activate Loan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Reject Loan</h3>
                    <button onclick="closeRejectionModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('loans.reject', $loan) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason *</label>
                            <textarea name="rejection_reason" id="rejection_reason" required rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Please provide a reason for rejecting this loan..."></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeRejectionModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                Reject Loan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Return to Officer Modal -->
    <div id="returnModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Return to Loan Officer</h3>
                    <button onclick="closeReturnModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('loans.return-to-officer', $loan) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="return_notes" class="block text-sm font-medium text-gray-700 mb-1">Return Notes</label>
                            <textarea name="return_notes" id="return_notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500" placeholder="Please provide notes for returning this loan to the officer..."></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeReturnModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg transition-colors">
                                Return to Officer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assessment Complete Modal -->
    <div id="assessmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white mb-10">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Complete Assessment</h3>
                    <button onclick="closeAssessmentModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <p class="text-sm text-indigo-800">
                        By completing the assessment, you confirm that all documents have been verified and the loan is ready for approval.
                    </p>
                </div>

                @if($loan->pledgedCollateral)
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                        <p class="font-medium text-blue-900">Collateral already pledged</p>
                        <p class="text-blue-800 mt-1">{{ $loan->pledgedCollateral->title }} ({{ $loan->pledgedCollateral->reference_number }})</p>
                    </div>
                @else
                    {{-- Collateral options (optional for now) --}}
                    <div class="mb-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-gray-900">Collateral (optional)</p>
                            <a href="{{ route('collaterals.create') }}?client_id={{ $loan->client_id }}&loan_id={{ $loan->loan_number }}" target="_blank" rel="noopener" class="text-xs text-green-700 hover:underline">+ Register new</a>
                        </div>
                        <div class="space-y-2">
                            <label class="flex items-start space-x-2 cursor-pointer">
                                <input type="radio" name="collateral_option" value="none" class="mt-1 text-indigo-600 focus:ring-indigo-500" checked onchange="toggleAssessmentCollateral()">
                                <span class="text-sm text-gray-700"><strong>No collateral</strong> — proceed without attaching an asset</span>
                            </label>
                            <label class="flex items-start space-x-2 cursor-pointer {{ $availableCollaterals->isEmpty() ? 'opacity-50' : '' }}">
                                <input type="radio" name="collateral_option" value="registered" class="mt-1 text-indigo-600 focus:ring-indigo-500" onchange="toggleAssessmentCollateral()" {{ $availableCollaterals->isEmpty() ? 'disabled' : '' }}>
                                <span class="text-sm text-gray-700">
                                    <strong>Registered collateral</strong> — attach from client portfolio (single use, boosts limit)
                                    @if($availableCollaterals->isEmpty())
                                        <span class="block text-xs text-gray-500 mt-0.5">No available items — register one first</span>
                                    @endif
                                </span>
                            </label>
                            <label class="flex items-start space-x-2 cursor-pointer">
                                <input type="radio" name="collateral_option" value="manual" class="mt-1 text-indigo-600 focus:ring-indigo-500" onchange="toggleAssessmentCollateral()">
                                <span class="text-sm text-gray-700"><strong>Manual entry</strong> — describe collateral without registering</span>
                            </label>
                        </div>

                        <div id="assessment_collateral_registered" class="hidden mt-3">
                            <label for="assessment_collateral_id" class="block text-xs font-medium text-gray-600 mb-1">Select collateral</label>
                            <select name="collateral_id" id="assessment_collateral_id" form="assessmentForm" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Choose an item...</option>
                                @foreach($availableCollaterals as $col)
                                    <option value="{{ $col->id }}" data-capacity="{{ $col->lendingCapacity() }}">
                                        {{ $col->title }} ({{ $col->reference_number }}) — TZS {{ number_format($col->estimated_value, 2) }} · boost TZS {{ number_format($col->lendingCapacity(), 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="assessment_collateral_manual" class="hidden mt-3 space-y-3">
                            <div>
                                <label for="assessment_collateral_description" class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                <textarea name="collateral_description" id="assessment_collateral_description" form="assessmentForm" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Describe the collateral..."></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="assessment_collateral_value" class="block text-xs font-medium text-gray-600 mb-1">Value (TZS)</label>
                                    <input type="number" name="collateral_value" id="assessment_collateral_value" form="assessmentForm" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="0.00">
                                </div>
                                <div>
                                    <label for="assessment_collateral_location" class="block text-xs font-medium text-gray-600 mb-1">Location</label>
                                    <input type="text" name="collateral_location" id="assessment_collateral_location" form="assessmentForm" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Location">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Assessment Checklist -->
                <div class="mb-4 space-y-2">
                    <p class="text-sm font-medium text-gray-700">Assessment Checklist:</p>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="check_docs" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="updateAssessmentSubmit()">
                        <span class="text-sm text-gray-700">All documents verified</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="check_client" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="updateAssessmentSubmit()">
                        <span class="text-sm text-gray-700">Client information confirmed</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="check_capacity" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="updateAssessmentSubmit()">
                        <span class="text-sm text-gray-700">Repayment capacity assessed</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="check_collateral" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="updateAssessmentSubmit()">
                        <span class="text-sm text-gray-700">Collateral verified or confirmed not required</span>
                    </label>
                </div>
                
                <form id="assessmentForm" action="{{ route('loans.complete-assessment', $loan) }}" method="POST">
                    @csrf
                    <input type="hidden" name="collateral_option" id="assessment_collateral_option" value="none">
                    <div class="space-y-4">
                        <div>
                            <label for="assessment_notes" class="block text-sm font-medium text-gray-700 mb-1">Assessment Notes</label>
                            <textarea name="assessment_notes" id="assessment_notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Summary of your assessment findings..."></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeAssessmentModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" id="assessment_submit_btn" disabled class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Complete Assessment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Close Loan Modal -->
    <div id="closeLoanModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Close Loan</h3>
                    <button onclick="closeCloseLoanModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Loan Balance Summary -->
                <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Current Balance Summary</h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-500">Total Loan:</span>
                            <p class="font-medium text-gray-900">TZS {{ number_format($loan->total_amount ?? $loan->loan_amount, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Amount Paid:</span>
                            <p class="font-medium text-green-700">TZS {{ number_format($loan->paid_amount ?? 0, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Outstanding:</span>
                            <p class="font-medium text-red-700">TZS {{ number_format($loan->calculated_outstanding_amount ?? 0, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Overdue:</span>
                            <p class="font-medium text-red-700">TZS {{ number_format($loan->overdue_amount ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>
                
                <form action="{{ route('loans.close', $loan) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="return_amount" class="block text-sm font-medium text-gray-700 mb-1">Return Amount (TZS)</label>
                            <input type="number" name="return_amount" id="return_amount" step="0.01" min="0" value="0"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                   placeholder="Amount client is returning" oninput="updateClosureSummary()">
                            <p class="mt-1 text-xs text-gray-500">Amount the client will pay to close the loan</p>
                        </div>
                        
                        <div>
                            <label for="forgiven_amount" class="block text-sm font-medium text-gray-700 mb-1">Forgiven Amount (TZS)</label>
                            <input type="number" name="forgiven_amount" id="forgiven_amount" step="0.01" min="0" value="0"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                   placeholder="Amount to be forgiven/written off" oninput="updateClosureSummary()">
                            <p class="mt-1 text-xs text-gray-500">Amount the organization agrees to forgive</p>
                        </div>
                        
                        <!-- Closure Summary -->
                        <div class="p-3 bg-orange-50 border border-orange-200 rounded-lg" id="closure_summary">
                            <h4 class="text-sm font-semibold text-orange-900 mb-2">Closure Summary</h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-orange-700">Outstanding Balance:</span>
                                    <span class="font-medium text-orange-900" id="closure_outstanding">TZS {{ number_format($loan->calculated_outstanding_amount ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-green-700">Return Amount:</span>
                                    <span class="font-medium text-green-900" id="closure_return">TZS 0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-700">Forgiven Amount:</span>
                                    <span class="font-medium text-yellow-900" id="closure_forgiven">TZS 0.00</span>
                                </div>
                                <div class="flex justify-between border-t border-orange-300 pt-1 mt-1">
                                    <span class="text-orange-700 font-semibold">Remaining After Close:</span>
                                    <span class="font-bold text-orange-900" id="closure_remaining">TZS {{ number_format($loan->calculated_outstanding_amount ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="closure_reason" class="block text-sm font-medium text-gray-700 mb-1">Closure Reason *</label>
                            <textarea name="closure_reason" id="closure_reason" required rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Reason for closing this loan..."></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeCloseLoanModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors">
                                Close Loan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Document Modal Functions
        function openDocumentModal() {
            document.getElementById('documentModal').classList.remove('hidden');
        }

        function closeDocumentModal() {
            document.getElementById('documentModal').classList.add('hidden');
            document.getElementById('document').value = '';
            document.getElementById('description').value = '';
            document.getElementById('document_type').value = '';
        }

        // Comment Modal Functions
        function openCommentModal() {
            document.getElementById('commentModal').classList.remove('hidden');
        }

        function closeCommentModal() {
            document.getElementById('commentModal').classList.add('hidden');
            document.getElementById('comment').value = '';
            document.getElementById('comment_type').value = 'general';
        }

        // Delete Document Function
        function deleteDocument(documentId) {
            if (confirm('Are you sure you want to delete this document?')) {
                const baseUrl = "{{ route('loans.delete-document', [$loan, 'PLACEHOLDER']) }}";
                const url = baseUrl.replace('PLACEHOLDER', documentId);
                
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => {
                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('Error deleting document');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting document');
                });
            }
        }

        // Approval Modal Functions
        function openApprovalModal() {
            document.getElementById('approvalModal').classList.remove('hidden');
        }

        function closeApprovalModal() {
            document.getElementById('approvalModal').classList.add('hidden');
            document.getElementById('approval_notes').value = '';
        }

        // Rejection Modal Functions
        function openRejectionModal() {
            document.getElementById('rejectionModal').classList.remove('hidden');
        }

        function closeRejectionModal() {
            document.getElementById('rejectionModal').classList.add('hidden');
            document.getElementById('rejection_reason').value = '';
        }

        // Return Modal Functions
        function openReturnModal() {
            document.getElementById('returnModal').classList.remove('hidden');
        }

        function closeReturnModal() {
            document.getElementById('returnModal').classList.add('hidden');
            document.getElementById('return_notes').value = '';
        }

        // Assessment Modal Functions
        function openAssessmentModal() {
            document.getElementById('assessmentModal').classList.remove('hidden');
        }

        function closeAssessmentModal() {
            document.getElementById('assessmentModal').classList.add('hidden');
            document.getElementById('assessment_notes').value = '';
            document.querySelectorAll('#assessmentModal input[type="checkbox"]').forEach(cb => cb.checked = false);
            const noneRadio = document.querySelector('#assessmentModal input[name="collateral_option"][value="none"]');
            if (noneRadio) noneRadio.checked = true;
            toggleAssessmentCollateral();
            updateAssessmentSubmit();
        }

        function toggleAssessmentCollateral() {
            const selected = document.querySelector('#assessmentModal input[name="collateral_option"]:checked');
            const option = selected ? selected.value : 'none';
            const hidden = document.getElementById('assessment_collateral_option');
            if (hidden) hidden.value = option;

            const registered = document.getElementById('assessment_collateral_registered');
            const manual = document.getElementById('assessment_collateral_manual');
            if (registered) registered.classList.toggle('hidden', option !== 'registered');
            if (manual) manual.classList.toggle('hidden', option !== 'manual');
        }

        function syncAssessmentCollateralOption() {
            const selected = document.querySelector('#assessmentModal input[name="collateral_option"]:checked');
            const hidden = document.getElementById('assessment_collateral_option');
            if (selected && hidden) hidden.value = selected.value;
        }

        document.getElementById('assessmentForm')?.addEventListener('submit', function() {
            syncAssessmentCollateralOption();
        });

        function updateAssessmentSubmit() {
            const checks = document.querySelectorAll('#assessmentModal input[type="checkbox"]');
            const allChecked = [...checks].every(c => c.checked);
            document.getElementById('assessment_submit_btn').disabled = !allChecked;
        }

        // Close Loan Modal Functions
        function openCloseLoanModal() {
            document.getElementById('closeLoanModal').classList.remove('hidden');
        }

        function closeCloseLoanModal() {
            document.getElementById('closeLoanModal').classList.add('hidden');
            document.getElementById('return_amount').value = '0';
            document.getElementById('forgiven_amount').value = '0';
            document.getElementById('closure_reason').value = '';
            updateClosureSummary();
        }

        function updateClosureSummary() {
            const outstanding = {{ $loan->outstanding_balance ?? 0 }};
            const returnAmt = parseFloat(document.getElementById('return_amount').value) || 0;
            const forgivenAmt = parseFloat(document.getElementById('forgiven_amount').value) || 0;
            const remaining = Math.max(0, outstanding - returnAmt - forgivenAmt);
            
            document.getElementById('closure_return').textContent = 'TZS ' + returnAmt.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('closure_forgiven').textContent = 'TZS ' + forgivenAmt.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('closure_remaining').textContent = 'TZS ' + remaining.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Color the remaining amount
            const remainingEl = document.getElementById('closure_remaining');
            if (remaining === 0) {
                remainingEl.className = 'font-bold text-green-700';
            } else {
                remainingEl.className = 'font-bold text-orange-900';
            }
        }

        // Close modals when clicking outside
        document.getElementById('documentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDocumentModal();
            }
        });

        document.getElementById('commentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCommentModal();
            }
        });

        document.getElementById('approvalModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeApprovalModal();
            }
        });

        document.getElementById('rejectionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectionModal();
            }
        });

        document.getElementById('returnModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReturnModal();
            }
        });

        const assessmentModalEl = document.getElementById('assessmentModal');
        if (assessmentModalEl) {
            assessmentModalEl.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAssessmentModal();
                }
            });
        }

        const closeLoanModalEl = document.getElementById('closeLoanModal');
        if (closeLoanModalEl) {
            closeLoanModalEl.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeCloseLoanModal();
                }
            });
        }

        // Schedule Adjustment - Live preview update
        const tenureInput = document.getElementById('tenure_value');
        if (tenureInput) {
            const previewInstallments = document.getElementById('preview_installments');
            const previewAmount = document.getElementById('preview_amount');
            const loanAmount = {{ $loan->loan_amount ?? 0 }};
            
            tenureInput.addEventListener('input', function() {
                const days = parseInt(this.value) || 0;
                if (previewInstallments) {
                    previewInstallments.textContent = days;
                }
                if (previewAmount && days > 0) {
                    const amount = loanAmount / days;
                    previewAmount.textContent = 'TZS ' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            });
        }

        // Loan show tabs
        (function() {
            const tabHints = @json($loanShowTabHints);
            const nav = document.querySelector('[data-default-tab]');
            let currentTab = nav ? parseInt(nav.dataset.defaultTab, 10) || 1 : 1;

            const activeTabClasses = ['border-green-600', 'text-green-700'];
            const inactiveTabClasses = ['border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300'];

            function updateLoanShowTab(tab) {
                currentTab = tab;

                document.querySelectorAll('.loan-show-tab-panel').forEach(el => {
                    el.classList.toggle('hidden', parseInt(el.dataset.loanTab, 10) !== tab);
                });

                document.querySelectorAll('[data-loan-tab-btn]').forEach(btn => {
                    const isActive = parseInt(btn.dataset.loanTabBtn, 10) === tab;
                    btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    btn.classList.remove(...activeTabClasses, ...inactiveTabClasses);
                    btn.classList.add(...(isActive ? activeTabClasses : inactiveTabClasses));
                });

                const hint = document.getElementById('loan_show_tab_hint');
                if (hint && tabHints[tab]) hint.textContent = tabHints[tab];
            }

            document.querySelectorAll('[data-loan-tab-btn]').forEach(btn => {
                btn.addEventListener('click', function() {
                    updateLoanShowTab(parseInt(this.dataset.loanTabBtn, 10));
                });
            });

            updateLoanShowTab(currentTab);
        })();
    </script>
</x-app-shell>
