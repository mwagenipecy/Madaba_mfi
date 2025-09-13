<x-app-shell title="Loan Details - {{ $loan->loan_number }}" header="Loan Details">
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
                        @if($loan->status === 'pending')
                            <a href="{{ route('loans.approvals') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Review for Approval
                            </a>
                        @endif
                        @if($loan->status === 'approved')
                            <a href="{{ route('loans.disbursements') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Process Disbursement
                            </a>
                        @endif
                        @if(in_array($loan->status, ['active', 'overdue']))
                            <a href="{{ route('loans.repayments') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Process Repayment
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
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
                                    <label class="block text-sm font-medium text-gray-700">Interest Rate</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->interest_rate }}% per annum</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Interest Calculation</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $loan->interest_calculation_method)) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Loan Tenure</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $loan->loan_tenure_months }} months</p>
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
                                    <label class="block text-sm font-medium text-gray-700">Monthly Payment</label>
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

                    <!-- Loan Schedule -->
                    @if($loan->schedules->count() > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Schedule</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Installment</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Principal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($loan->schedules as $schedule)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $schedule->installment_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $schedule->due_date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($schedule->principal_amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($schedule->interest_amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($schedule->total_amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $schedule->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ ucfirst($schedule->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Recent Transactions -->
                    @if($loan->transactions->count() > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Transactions</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($loan->transactions->take(10) as $transaction)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($transaction->amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Documents Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Documents</h2>
                                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
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
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $document['status'] === 'approved' ? 'bg-green-100 text-green-800' : ($document['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($document['status'] ?? 'pending') }}
                                                </span>
                                                <a href="#" class="text-blue-600 hover:text-blue-700 text-sm">View</a>
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

                    <!-- Comments Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Comments & Notes</h2>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Add Comment
                                </button>
                            </div>
                            
                            <div class="space-y-4">
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

                                <!-- Sample comments -->
                                <div class="space-y-3">
                                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">LO</span>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <p class="text-sm font-medium text-gray-900">Loan Officer</p>
                                                    <span class="text-xs text-gray-500">2 hours ago</span>
                                                </div>
                                                <p class="text-sm text-gray-700 mt-1">Client has provided all required documents. Ready for review.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">M</span>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <p class="text-sm font-medium text-gray-900">Manager</p>
                                                    <span class="text-xs text-gray-500">1 day ago</span>
                                                </div>
                                                <p class="text-sm text-gray-700 mt-1">Please verify the client's income documentation before proceeding.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Workflow Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Approval Workflow</h2>
                            
                            <div class="space-y-4">
                                <!-- Application Submitted -->
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Application Submitted</p>
                                        <p class="text-sm text-gray-500">by {{ $loan->client->display_name }} on {{ $loan->application_date->format('M d, Y g:i A') }}</p>
                                    </div>
                                </div>

                                <!-- Under Review -->
                                @if($loan->status === 'under_review' || $loan->status === 'approved' || $loan->status === 'rejected')
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 {{ $loan->status === 'approved' ? 'bg-green-100' : ($loan->status === 'rejected' ? 'bg-red-100' : 'bg-yellow-100') }} rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 {{ $loan->status === 'approved' ? 'text-green-600' : ($loan->status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Under Review</p>
                                        <p class="text-sm text-gray-500">by {{ $loan->loanOfficer->name ?? 'Loan Officer' }}</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Approved/Rejected -->
                                @if($loan->approval_status === 'approved' && $loan->approvedBy)
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Approved</p>
                                        <p class="text-sm text-gray-500">by {{ $loan->approvedBy->name ?? 'Manager' }} on {{ $loan->approval_date->format('M d, Y g:i A') }}</p>
                                    </div>
                                </div>
                                @elseif($loan->approval_status === 'rejected' && $loan->rejectedBy)
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Rejected</p>
                                        <p class="text-sm text-gray-500">by {{ $loan->rejectedBy->name ?? 'Manager' }} on {{ $loan->rejection_date ?? 'Unknown' }}</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Disbursed -->
                                @if($loan->status === 'disbursed' && $loan->disbursement_date)
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Disbursed</p>
                                        <p class="text-sm text-gray-500">on {{ $loan->disbursement_date->format('M d, Y g:i A') }}</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Return to Loan Officer -->
                                @if($loan->status === 'pending' || $loan->status === 'under_review')
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <form method="POST" action="{{ route('loans.return-to-officer', $loan) }}" class="inline" onsubmit="return confirm('Are you sure you want to return this loan to the loan officer?')">
                                        @csrf
                                        <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                            Return to Loan Officer
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
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
                                    <span class="text-sm text-gray-600">Outstanding Balance</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $loan->formatted_outstanding_balance }}</span>
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
</x-app-shell>
