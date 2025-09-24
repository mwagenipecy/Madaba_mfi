<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Loans with Arrears') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('loan-charges.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    All Charges
                </a>
                <a href="{{ route('loan-charges.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Add Charge
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Card -->
            <div class="bg-red-50 border border-red-200 rounded-md p-6 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-red-800">Total Outstanding Arrears</h3>
                        <div class="mt-2 text-3xl font-bold text-red-900">
                            TZS {{ number_format($totalArrearsAmount, 2) }}
                        </div>
                        <p class="mt-1 text-sm text-red-700">
                            {{ $loans->count() }} loan(s) with outstanding penalties and late fees
                        </p>
                    </div>
                </div>
            </div>

            <!-- Arrears Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Loans with Outstanding Charges</h3>
                    
                    @if($loans->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Charges</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Overdue</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($loans as $loan)
                                        @php
                                            $outstandingCharges = $loan->transactions->whereIn('transaction_type', ['penalty_fee', 'late_fee'])->where('status', 'pending');
                                            $totalOutstanding = $outstandingCharges->sum('amount');
                                            $daysOverdue = $loan->overdue_days ?? 0;
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $loan->loan_number }}</div>
                                                <div class="text-sm text-gray-500">{{ $loan->loanProduct->name ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">TZS {{ number_format($loan->loan_amount, 2) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $loan->client->name ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ $loan->client->phone ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ $loan->client->email ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-red-600">
                                                    TZS {{ number_format($totalOutstanding, 2) }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $outstandingCharges->count() }} charge(s)
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($daysOverdue > 0)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        {{ $daysOverdue }} days
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Current
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('loans.show', $loan) }}" class="text-blue-600 hover:text-blue-900">View Loan</a>
                                                    <button onclick="openBulkPaymentModal({{ $loan->id }}, {{ $totalOutstanding }})" class="text-green-600 hover:text-green-900">Pay All</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $loans->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No arrears found</h3>
                            <p class="mt-1 text-sm text-gray-500">All loans are current with their payments.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Payment Modal -->
    <div id="bulkPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Process Bulk Payment</h3>
                <form id="bulkPaymentForm" method="POST">
                    @csrf
                    @method('POST')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Payment Amount</label>
                        <input type="number" id="bulkPaymentAmount" name="payment_amount" step="0.01" min="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select name="payment_method" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Payment Reference</label>
                        <input type="text" name="payment_reference" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeBulkPaymentModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md">
                            Process Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openBulkPaymentModal(loanId, maxAmount) {
            document.getElementById('bulkPaymentAmount').max = maxAmount;
            document.getElementById('bulkPaymentAmount').value = maxAmount;
            document.getElementById('bulkPaymentForm').action = `/loans/${loanId}/repayment`;
            document.getElementById('bulkPaymentModal').classList.remove('hidden');
        }

        function closeBulkPaymentModal() {
            document.getElementById('bulkPaymentModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
