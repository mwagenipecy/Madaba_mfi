<x-app-shell title="Loan Repayments" header="Loan Repayments">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Loan Repayments</h1>
                        <div class="flex space-x-2">
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                {{ $activeLoans->count() }} Active Loans
                            </span>
                            @if($overdueLoans->count() > 0)
                                <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {{ $overdueLoans->count() }} Overdue
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Active Loans for Repayment -->
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Balance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Payment Due</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($activeLoans as $loan)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <a href="{{ route('loans.show', $loan) }}" class="text-green-600 hover:text-green-700">
                                                    {{ $loan->loan_number }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $loan->client->display_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $loan->formatted_outstanding_balance }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($loan->schedules->count() > 0)
                                                    {{ $loan->schedules->first()->due_date->format('M d, Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <button onclick="openPaymentModal({{ $loan->id }}, '{{ $loan->loan_number }}', {{ $loan->outstanding_balance }}, {{ $loan->schedules->first()->amount ?? 0 }})" 
                                                            class="text-green-600 hover:text-green-700 font-medium">Process Payment</button>
                                                    <a href="{{ route('loans.show', $loan) }}" class="text-blue-600 hover:text-blue-700">View</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                                No active loans for repayment at this time.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Processing Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Process Loan Payment</h3>
                    <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="paymentForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loan Number</label>
                            <input type="text" id="modalLoanNumber" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Outstanding Balance</label>
                            <input type="text" id="modalOutstandingBalance" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Payment</label>
                            <input type="text" id="modalScheduledPayment" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
                        </div>
                        
                        <div>
                            <label for="payment_amount" class="block text-sm font-medium text-gray-700 mb-1">Payment Amount (TZS) *</label>
                            <input type="number" name="payment_amount" id="payment_amount" step="0.01" min="0.01" required 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                            <select name="payment_method" id="payment_method" required 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Select payment method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="check">Check</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="payment_reference" class="block text-sm font-medium text-gray-700 mb-1">Payment Reference</label>
                            <input type="text" name="payment_reference" id="payment_reference" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   placeholder="Transaction reference or check number">
                        </div>
                        
                        <div>
                            <label for="payment_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="payment_notes" id="payment_notes" rows="3" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                      placeholder="Additional payment notes"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closePaymentModal()" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Process Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openPaymentModal(loanId, loanNumber, outstandingBalance, scheduledPayment) {
            document.getElementById('paymentModal').classList.remove('hidden');
            document.getElementById('paymentForm').action = `/loans/${loanId}/repayment`;
            document.getElementById('modalLoanNumber').value = loanNumber;
            document.getElementById('modalOutstandingBalance').value = 'TZS ' + outstandingBalance.toLocaleString();
            document.getElementById('modalScheduledPayment').value = 'TZS ' + scheduledPayment.toLocaleString();
            document.getElementById('payment_amount').value = scheduledPayment;
        }
        
        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
            document.getElementById('paymentForm').reset();
        }
        
        // Close modal when clicking outside
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentModal();
            }
        });
    </script>
</x-app-shell>
