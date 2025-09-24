<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Charge Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('loan-charges.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Charges
                </a>
                @if($loanTransaction->status === 'pending')
                    <button onclick="openPaymentModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Process Payment
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Charge Details -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Charge Information</h3>
                            
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Charge Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $loanTransaction->status_badge_class }}">
                                            {{ ucfirst(str_replace('_', ' ', $loanTransaction->transaction_type)) }}
                                        </span>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                        TZS {{ number_format($loanTransaction->amount, 2) }}
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $loanTransaction->status_badge_class }}">
                                            {{ ucfirst($loanTransaction->status) }}
                                        </span>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Transaction Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $loanTransaction->transaction_date->format('F d, Y') }}
                                    </dd>
                                </div>
                                
                                @if($loanTransaction->notes)
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $loanTransaction->notes }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Payment History -->
                    @if($loanTransaction->status === 'completed')
                        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                                
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    @if($loanTransaction->payment_method)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ ucfirst(str_replace('_', ' ', $loanTransaction->payment_method)) }}
                                            </dd>
                                        </div>
                                    @endif
                                    
                                    @if($loanTransaction->reference_number)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Payment Reference</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ $loanTransaction->reference_number }}
                                            </dd>
                                        </div>
                                    @endif
                                    
                                    @if($loanTransaction->processed_at)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Processed Date</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ $loanTransaction->processed_at->format('F d, Y g:i A') }}
                                            </dd>
                                        </div>
                                    @endif
                                    
                                    @if($loanTransaction->processedBy)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Processed By</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ $loanTransaction->processedBy->name }}
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Loan Information -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Related Loan</h3>
                            
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Loan Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ $loanTransaction->loan->loan_number }}
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Client</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $loanTransaction->loan->client->name ?? 'N/A' }}
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Loan Product</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $loanTransaction->loan->loanProduct->name ?? 'N/A' }}
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Loan Amount</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        TZS {{ number_format($loanTransaction->loan->loan_amount, 2) }}
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Outstanding Balance</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        TZS {{ number_format($loanTransaction->loan->outstanding_balance, 2) }}
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Loan Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $loanTransaction->loan->status_badge_class }}">
                                            {{ ucfirst($loanTransaction->loan->status) }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                            
                            <div class="mt-6">
                                <a href="{{ route('loans.show', $loanTransaction->loan) }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                                    View Loan Details
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($loanTransaction->status === 'pending')
                        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                                
                                <div class="space-y-3">
                                    <button onclick="openPaymentModal()" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                        Process Payment
                                    </button>
                                    
                                    <form method="POST" action="{{ route('loan-charges.update-status', $loanTransaction) }}" class="inline-block w-full">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700" onclick="return confirm('Are you sure you want to cancel this charge?')">
                                            Cancel Charge
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Process Payment</h3>
                <form method="POST" action="{{ route('loan-charges.pay', $loanTransaction) }}">
                    @csrf
                    @method('POST')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Payment Amount</label>
                        <input type="number" name="payment_amount" step="0.01" min="0.01" max="{{ $loanTransaction->amount }}" value="{{ $loanTransaction->amount }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
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
                        <button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md">
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
        function openPaymentModal() {
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
