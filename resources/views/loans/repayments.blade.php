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

    <!-- Payment Receipt Modal -->
    <div id="receiptModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full hidden z-50 flex items-start justify-center pt-10">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 no-print">
                <h3 class="text-lg font-semibold text-gray-900">Payment Receipt</h3>
                <div class="flex items-center space-x-2">
                    <button onclick="printReceipt()" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                    <button onclick="closeReceiptModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="receiptContent" class="px-6 py-5"></div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-between no-print">
                <button onclick="closeReceiptModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">Close</button>
                <button onclick="printReceipt()" class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors inline-flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Receipt
                </button>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * { visibility: hidden; }
            #receiptContent, #receiptContent * { visibility: visible; }
            #receiptContent { position: absolute; left: 0; top: 0; width: 100%; padding: 20px; }
            .no-print { display: none !important; }
        }
    </style>

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

        function closeReceiptModal() {
            document.getElementById('receiptModal').classList.add('hidden');
        }

        function printReceipt() {
            window.print();
        }

        document.getElementById('receiptModal').addEventListener('click', function(e) {
            if (e.target === this) closeReceiptModal();
        });

        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }

        function showReceipt(receipt) {
            const org = receipt.organization;
            const client = receipt.client;
            const loan = receipt.loan;
            const payment = receipt.payment;

            document.getElementById('receiptContent').innerHTML = `
                <div style="text-align: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #059669;">
                    <h2 style="font-size: 20px; font-weight: 700; color: #111827; margin: 0;">${org.name}</h2>
                    ${org.address ? `<p style="font-size: 12px; color: #6b7280; margin: 2px 0;">${org.address}${org.city ? ', ' + org.city : ''}</p>` : ''}
                    ${org.phone ? `<p style="font-size: 12px; color: #6b7280; margin: 2px 0;">Tel: ${org.phone}${org.email ? ' | ' + org.email : ''}</p>` : ''}
                </div>
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="display: inline-block; background: linear-gradient(135deg, #059669, #047857); color: white; padding: 6px 20px; border-radius: 20px; font-size: 13px; font-weight: 600;">PAYMENT RECEIPT</div>
                    <p style="font-size: 13px; color: #6b7280; margin-top: 8px;">Receipt No: <span style="font-weight: 700; color: #111827;">${receipt.receipt_number}</span></p>
                    <p style="font-size: 13px; color: #6b7280;">${receipt.date} at ${receipt.time}</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Received From</div>
                    <div style="background: #f9fafb; border-radius: 8px; padding: 12px;">
                        <div style="font-weight: 600; font-size: 15px; color: #111827;">${client.name}</div>
                        <div style="font-size: 13px; color: #6b7280;">Client #: ${client.client_number} | Phone: ${client.phone}</div>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Payment Details</div>
                    <div style="border: 2px solid #059669; border-radius: 12px; padding: 16px; background: linear-gradient(135deg, #ecfdf5, #f0fdf4);">
                        <div style="text-align: center; margin-bottom: 12px;">
                            <div style="font-size: 12px; color: #059669; font-weight: 500;">Amount Paid</div>
                            <div style="font-size: 28px; font-weight: 800; color: #059669;">TZS ${formatNumber(payment.amount)}</div>
                        </div>
                        <div style="border-top: 1px dashed #a7f3d0; padding-top: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;"><span style="color: #6b7280; font-size: 13px;">Payment Method</span><span style="font-weight: 500; font-size: 13px; color: #111827;">${payment.method}</span></div>
                            ${payment.reference !== 'N/A' ? `<div style="display: flex; justify-content: space-between; margin-bottom: 6px;"><span style="color: #6b7280; font-size: 13px;">Reference</span><span style="font-weight: 500; font-size: 13px; color: #111827;">${payment.reference}</span></div>` : ''}
                        </div>
                    </div>
                </div>
                ${loan ? `
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Loan Details</div>
                    <div style="background: #f9fafb; border-radius: 8px; padding: 12px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;"><span style="color: #6b7280; font-size: 13px;">Loan Number</span><span style="font-weight: 600; font-size: 13px; color: #111827;">${loan.loan_number}</span></div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;"><span style="color: #6b7280; font-size: 13px;">Product</span><span style="font-weight: 500; font-size: 13px; color: #111827;">${loan.product_name}</span></div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;"><span style="color: #6b7280; font-size: 13px;">Balance Before</span><span style="font-weight: 500; font-size: 13px; color: #111827;">TZS ${formatNumber(loan.outstanding_before)}</span></div>
                        <div style="display: flex; justify-content: space-between; padding-top: 6px; border-top: 1px dashed #d1d5db;"><span style="color: #059669; font-weight: 600; font-size: 13px;">Balance After</span><span style="font-weight: 700; font-size: 13px; color: #059669;">TZS ${formatNumber(loan.outstanding_after)}</span></div>
                    </div>
                </div>` : ''}
                <div style="border-top: 1px solid #e5e7eb; padding-top: 12px; margin-top: 16px;">
                    <div style="display: flex; justify-content: space-between;"><span style="color: #6b7280; font-size: 12px;">Processed By</span><span style="font-weight: 500; font-size: 12px; color: #111827;">${receipt.processed_by}</span></div>
                </div>
                <div style="margin-top: 28px; display: flex; justify-content: space-between;">
                    <div style="text-align: center; width: 45%;"><div style="border-top: 1px solid #9ca3af; padding-top: 4px; margin-top: 36px;"><span style="font-size: 11px; color: #6b7280;">Officer Signature</span></div></div>
                    <div style="text-align: center; width: 45%;"><div style="border-top: 1px solid #9ca3af; padding-top: 4px; margin-top: 36px;"><span style="font-size: 11px; color: #6b7280;">Client Signature</span></div></div>
                </div>
                <div style="text-align: center; margin-top: 20px; padding-top: 12px; border-top: 2px solid #059669;">
                    <p style="font-size: 12px; color: #6b7280; margin: 0;">Thank you for your payment!</p>
                    <p style="font-size: 11px; color: #9ca3af; margin: 4px 0 0;">This is a computer-generated receipt.</p>
                </div>
            `;
            document.getElementById('receiptModal').classList.remove('hidden');
        }

        // Auto-show receipt if session data exists
        @if(session('receipt'))
            document.addEventListener('DOMContentLoaded', function() {
                showReceipt(@json(session('receipt')));
            });
        @endif
    </script>
</x-app-shell>
