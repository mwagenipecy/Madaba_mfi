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
                            @if(in_array(auth()->user()->role, ['admin', 'manager', 'super_admin']))
                                <button onclick="openApprovalModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Approve Loan
                                </button>
                                <button onclick="openRejectionModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Reject Loan
                                </button>
                            @endif
                            @if(in_array(auth()->user()->role, ['admin', 'manager', 'super_admin', 'loan_officer']))
                                <form method="POST" action="{{ route('loans.under-review', $loan) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Put Under Review
                                    </button>
                                </form>
                            @endif
                        @endif
                        
                        @if($loan->status === 'under_review')
                            @if(in_array(auth()->user()->role, ['admin', 'manager', 'super_admin']))
                                <button onclick="openApprovalModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Approve Loan
                                </button>
                                <button onclick="openRejectionModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Reject Loan
                                </button>
                            @endif
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
                                @if(($loan->status === 'pending' || $loan->status === 'under_review') && in_array(auth()->user()->role, ['admin', 'manager', 'super_admin']))
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <button onclick="openReturnModal()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Return to Loan Officer
                                    </button>
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
                
                <form action="{{ route('loans.approve', $loan) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="approved_amount" class="block text-sm font-medium text-gray-700 mb-1">Approved Amount (TZS)</label>
                            <input type="number" name="approved_amount" id="approved_amount" step="0.01" min="0" 
                                   value="{{ $loan->loan_amount }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
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
                                Approve Loan
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
    </script>
</x-app-shell>
