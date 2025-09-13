<x-app-shell>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Expense Request Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Header with status and actions -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $expense->request_number }}</h1>
                            <p class="text-sm text-gray-600">Created on {{ $expense->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $expense->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($expense->status === 'approved' ? 'bg-green-100 text-green-800' : ($expense->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) }}">
                                {{ ucfirst($expense->status) }}
                            </span>
                            @if($expense->status === 'approved' && !$expense->completed_at)
                                <button onclick="openCompletionModal({{ $expense->id }})" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
                                    Complete Expense
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Expense Details -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <!-- Basic Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Expense Information</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Amount:</span>
                                    <span class="font-semibold text-lg text-green-600">TZS {{ number_format($expense->amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Description:</span>
                                    <span class="text-right">{{ $expense->description }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Method:</span>
                                    <span class="capitalize">{{ str_replace('_', ' ', $expense->payment_method) }}</span>
                                </div>
                                @if($expense->reference_number)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Reference Number:</span>
                                    <span>{{ $expense->reference_number }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Expense Date:</span>
                                    <span>{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}</span>
                                </div>
                                @if($expense->notes)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Notes:</span>
                                    <span class="text-right">{{ $expense->notes }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Account Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-gray-600 block">Expense Account:</span>
                                    <span class="font-medium">{{ $expense->expenseAccount->name }}</span>
                                    <span class="text-sm text-gray-500">({{ $expense->expenseAccount->accountType->name }})</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 block">Payment Account:</span>
                                    <span class="font-medium">{{ $expense->paymentAccount->name }}</span>
                                    <span class="text-sm text-gray-500">({{ $expense->paymentAccount->accountType->name }})</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 block">Organization:</span>
                                    <span class="font-medium">{{ $expense->organization->name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 block">Branch:</span>
                                    <span class="font-medium">{{ $expense->branch->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Information -->
                    @if($approval)
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-semibold mb-4">Approval Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-gray-600 block">Approval Number:</span>
                                <span class="font-medium">{{ $approval->approval_number }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600 block">Status:</span>
                                <span class="px-2 py-1 rounded text-sm {{ $approval->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($approval->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($approval->status) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600 block">Requested By:</span>
                                <span class="font-medium">{{ $expense->requester->first_name }} {{ $expense->requester->last_name }}</span>
                            </div>
                            @if($approval->approver)
                            <div>
                                <span class="text-gray-600 block">Approved By:</span>
                                <span class="font-medium">{{ $approval->approver->first_name }} {{ $approval->approver->last_name }}</span>
                            </div>
                            @endif
                            @if($approval->approved_at)
                            <div>
                                <span class="text-gray-600 block">Approved At:</span>
                                <span class="font-medium">{{ $approval->approved_at->format('M d, Y \a\t g:i A') }}</span>
                            </div>
                            @endif
                            @if($approval->approval_notes)
                            <div class="md:col-span-2">
                                <span class="text-gray-600 block">Approval Notes:</span>
                                <span class="font-medium">{{ $approval->approval_notes }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Receipt Section -->
                    @if($expense->receipt_path)
                    <div class="bg-green-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-semibold mb-4">Receipt</h3>
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-2">Receipt uploaded on {{ $expense->completed_at->format('M d, Y \a\t g:i A') }}</p>
                                <p class="font-medium">{{ $expense->receipt_filename }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="previewReceipt('{{ route('expenses.receipt', $expense) }}')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                                    Preview
                                </button>
                                <a href="{{ route('expenses.receipt', $expense) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700" download>
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- General Ledger Entries -->
                    @if($ledgerEntries->count() > 0)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Account Transactions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($ledgerEntries as $entry)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $entry->transaction_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium">{{ $entry->account->name }}</div>
                                                <div class="text-gray-500 text-xs">{{ $entry->account->accountType->name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $entry->transaction_type === 'debit' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                {{ ucfirst($entry->transaction_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            TZS {{ number_format($entry->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $entry->description }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Transaction Summary for Completed Expenses -->
                    @if($expense->status === 'completed' && $ledgerEntries->count() > 0)
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-semibold mb-4">Transaction Summary</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Debit Summary -->
                            <div class="bg-white p-4 rounded-lg">
                                <h4 class="font-semibold text-gray-900 mb-3">Debit Entries</h4>
                                @php
                                    $debitEntries = $ledgerEntries->where('transaction_type', 'debit');
                                    $totalDebit = $debitEntries->sum('amount');
                                @endphp
                                @foreach($debitEntries as $entry)
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                    <div>
                                        <div class="font-medium text-sm">{{ $entry->account->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $entry->account->accountType->name }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-red-600">TZS {{ number_format($entry->amount, 2) }}</div>
                                        <div class="text-xs text-gray-500">{{ $entry->transaction_date->format('M d, Y') }}</div>
                                    </div>
                                </div>
                                @endforeach
                                @if($debitEntries->count() > 1)
                                <div class="flex justify-between items-center py-2 border-t-2 border-gray-300 mt-2">
                                    <div class="font-bold text-gray-900">Total Debit:</div>
                                    <div class="font-bold text-red-600">TZS {{ number_format($totalDebit, 2) }}</div>
                                </div>
                                @endif
                            </div>

                            <!-- Credit Summary -->
                            <div class="bg-white p-4 rounded-lg">
                                <h4 class="font-semibold text-gray-900 mb-3">Credit Entries</h4>
                                @php
                                    $creditEntries = $ledgerEntries->where('transaction_type', 'credit');
                                    $totalCredit = $creditEntries->sum('amount');
                                @endphp
                                @foreach($creditEntries as $entry)
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                    <div>
                                        <div class="font-medium text-sm">{{ $entry->account->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $entry->account->accountType->name }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-green-600">TZS {{ number_format($entry->amount, 2) }}</div>
                                        <div class="text-xs text-gray-500">{{ $entry->transaction_date->format('M d, Y') }}</div>
                                    </div>
                                </div>
                                @endforeach
                                @if($creditEntries->count() > 1)
                                <div class="flex justify-between items-center py-2 border-t-2 border-gray-300 mt-2">
                                    <div class="font-bold text-gray-900">Total Credit:</div>
                                    <div class="font-bold text-green-600">TZS {{ number_format($totalCredit, 2) }}</div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Transaction Balance Check -->
                        <div class="mt-4 p-3 bg-gray-100 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-700">Transaction Balance:</span>
                                <span class="font-bold {{ $totalDebit == $totalCredit ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $totalDebit == $totalCredit ? 'BALANCED' : 'UNBALANCED' }}
                                </span>
                            </div>
                            @if($totalDebit != $totalCredit)
                            <div class="text-sm text-red-600 mt-1">
                                Debit Total: TZS {{ number_format($totalDebit, 2) }} | 
                                Credit Total: TZS {{ number_format($totalCredit, 2) }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Back Button -->
                    <div class="mt-6">
                        <a href="{{ url()->previous() }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">
                            ← Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Preview Modal -->
    <div id="receiptModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Receipt Preview</h3>
                    <button onclick="closeReceiptModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="text-center">
                    <iframe id="receiptFrame" src="" width="100%" height="500px" class="border rounded"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewReceipt(url) {
            document.getElementById('receiptFrame').src = url;
            document.getElementById('receiptModal').classList.remove('hidden');
        }

        function closeReceiptModal() {
            document.getElementById('receiptModal').classList.add('hidden');
            document.getElementById('receiptFrame').src = '';
        }

        // Close modal when clicking outside
        document.getElementById('receiptModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReceiptModal();
            }
        });
    </script>

    <!-- Completion Modal -->
    <div id="completionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Complete Expense Request</h3>
                    <button onclick="closeCompletionModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="completionForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="receipt" class="block text-sm font-medium text-gray-700 mb-2">Upload Receipt</label>
                        <input type="file" id="receipt" name="receipt" accept=".pdf,.jpg,.jpeg,.png" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Accepted formats: PDF, JPG, JPEG, PNG (Max 10MB)</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCompletionModal()" 
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
                            Complete Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCompletionModal(expenseId) {
            document.getElementById('completionModal').classList.remove('hidden');
            document.getElementById('completionForm').action = `/expenses/${expenseId}/complete`;
        }
        
        function closeCompletionModal() {
            document.getElementById('completionModal').classList.add('hidden');
            document.getElementById('completionForm').reset();
        }

        // Close modal when clicking outside
        document.getElementById('completionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCompletionModal();
            }
        });
    </script>
</x-app-shell>
