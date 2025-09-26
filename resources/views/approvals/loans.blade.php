<x-app-shell title="Loan Approvals" header="Loan Approvals">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Loan Approvals</h1>
                        <div class="flex space-x-2">
                            <span class="bg-yellow-100 text-yellow-800 text-sm font-medium px-3 py-1 rounded-full">
                                {{ $pendingLoans->count() }} Pending Loan Approvals
                            </span>
                        </div>
                    </div>
                    
                    <!-- Pending Loan Approvals Table -->
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (TZS)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Application Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if($pendingLoans->count() > 0)
                                        @foreach($pendingLoans as $loan)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $loan->loan_number }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $loan->client->first_name }} {{ $loan->client->last_name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($loan->loan_amount, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $loan->loan_purpose ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $loan->created_at->format('M d, Y H:i') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <!-- Approve Button with Modal -->
                                                        <button onclick="openApproveModal({{ $loan->id }}, {{ $loan->loan_amount }})" 
                                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                            Approve
                                                        </button>
                                                        
                                                        <!-- Reject Button with Modal -->
                                                        <button onclick="openRejectModal({{ $loan->id }})" 
                                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            Reject
                                                        </button>
                                                        
                                                        <!-- View Details Button -->
                                                        <a href="{{ route('loans.show', $loan->id) }}" 
                                                           class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            View
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                                No pending loan approvals at this time.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Recent Approvals Section -->
                    @if($recentApprovals->count() > 0)
                        <div class="mt-8">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Loan Approvals</h2>
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Number</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved Amount (TZS)</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved By</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($recentApprovals as $loan)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $loan->loan_number }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $loan->client->first_name }} {{ $loan->client->last_name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ number_format($loan->approved_amount, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $loan->approvedBy->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $loan->approval_date ? $loan->approval_date->format('M d, Y H:i') : 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('loans.show', $loan->id) }}" 
                                                           class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-shell>

<!-- Approval Modal -->
<div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Approve Loan</h3>
                <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="approveForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="approved_amount" class="block text-sm font-medium text-gray-700 mb-2">Approved Amount (TZS)</label>
                    <input type="number" id="approved_amount" name="approved_amount" step="0.01" min="0" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div class="mb-4">
                    <label for="approval_notes" class="block text-sm font-medium text-gray-700 mb-2">Approval Notes (Optional)</label>
                    <textarea id="approval_notes" name="approval_notes" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                              placeholder="Add any notes about this approval..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeApproveModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md">
                        Approve Loan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Reject Loan</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason *</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                              placeholder="Please provide a reason for rejecting this loan application..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">
                        Reject Loan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openApproveModal(loanId, loanAmount) {
    document.getElementById('approveForm').action = `/approvals/approve/${loanId}`;
    document.getElementById('approved_amount').value = loanAmount;
    document.getElementById('approveModal').classList.remove('hidden');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
    document.getElementById('approveForm').reset();
}

function openRejectModal(loanId) {
    document.getElementById('rejectForm').action = `/approvals/reject/${loanId}`;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectForm').reset();
}

// Close modals when clicking outside
document.getElementById('approveModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApproveModal();
    }
});

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>

