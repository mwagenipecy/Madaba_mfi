<x-app-shell title="Loan Repayments" header="Loan Repayments">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Client Search Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Search Client for Repayment</h2>
                    
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" 
                               id="clientSearch" 
                               class="w-full px-4 py-3 pl-10 pr-4 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Search by name, client number, phone, or email..."
                               autocomplete="off">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <div id="searchLoading" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                            <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div id="searchResults" class="mt-4 hidden">
                        <div class="border border-gray-200 rounded-lg max-h-64 overflow-y-auto">
                            <!-- Results will be populated here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Details and Payment Form -->
            <div id="clientDetailsSection" class="hidden">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Client Details & Payment</h2>
                            <button onclick="clearClientSelection()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Client Info -->
                        <div id="clientInfo" class="bg-gray-50 rounded-lg p-4 mb-6">
                            <!-- Client details will be populated here -->
                        </div>

                        <!-- Loans and Charges -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <!-- Active Loans -->
                            <div>
                                <h3 class="text-md font-semibold text-gray-900 mb-3">Active Loans</h3>
                                <div id="loansList" class="space-y-3">
                                    <!-- Loans will be populated here -->
                                </div>
                            </div>

                            <!-- Outstanding Charges -->
                            <div>
                                <h3 class="text-md font-semibold text-gray-900 mb-3">Outstanding Charges</h3>
                                <div id="chargesList" class="space-y-3">
                                    <!-- Charges will be populated here -->
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <div class="border-t pt-6">
                            <h3 class="text-md font-semibold text-gray-900 mb-4">Process Payment</h3>
                            
                            <form id="paymentForm" class="space-y-4">
                                @csrf
                                <input type="hidden" id="selectedClientId" name="client_id">
                                
                                <!-- Payment Type -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Type</label>
                                        <select id="paymentType" name="payment_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                            <option value="">Select Payment Type</option>
                                            <option value="loan_repayment">Loan Repayment</option>
                                            <option value="charge_payment">Charge Payment</option>
                                            <option value="both">Both Loan & Charges</option>
                                        </select>
                                    </div>

                                    <!-- Loan Selection -->
                                    <div id="loanSelection" class="hidden">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Loan</label>
                                        <select id="selectedLoanId" name="loan_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">Select Loan</option>
                                        </select>
                                    </div>

                                    <!-- Charge Selection -->
                                    <div id="chargeSelection" class="hidden">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Charge</label>
                                        <select id="selectedChargeId" name="charge_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">Select Charge</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Payment Amount and Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Amount (TZS)</label>
                                        <input type="number" id="paymentAmount" name="payment_amount" step="0.01" min="0.01" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Collection Account</label>
                                        <select name="collection_account_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                            <option value="">Select Account</option>
                                            @foreach($collectionAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Payment Method and Reference -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                        <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                            <option value="">Select Method</option>
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="mobile_money">Mobile Money</option>
                                            <option value="check">Check</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                                        <input type="text" name="payment_reference" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="Optional reference number">
                                    </div>
                                </div>

                                <!-- Payment Notes -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Notes</label>
                                    <textarea name="payment_notes" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                              placeholder="Additional notes about this payment..."></textarea>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex justify-end">
                                    <button type="submit" id="submitPayment" 
                                            class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Process Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="alertContainer" class="fixed top-4 right-4 z-50"></div>

    <!-- Payment Receipt Modal -->
    <div id="receiptModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full hidden z-50 flex items-start justify-center pt-10">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 my-8">
            <!-- Modal Header (non-printable) -->
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

            <!-- Printable Receipt Content -->
            <div id="receiptContent" class="px-6 py-5">
                <!-- Receipt will be populated by JS -->
            </div>

            <!-- Modal Footer (non-printable) -->
            <div class="px-6 py-4 border-t border-gray-200 flex justify-between no-print">
                <button onclick="closeReceiptModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Close
                </button>
                <button onclick="printReceipt()" class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors inline-flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Receipt
                </button>
            </div>
        </div>
    </div>

    <!-- Print-only styles -->
    <style>
        @media print {
            body * { visibility: hidden; }
            #receiptContent, #receiptContent * { visibility: visible; }
            #receiptContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
            }
            .no-print { display: none !important; }
        }
    </style>

    <script>
        let selectedClient = null;
        let searchTimeout = null;

        // Client search functionality
        document.getElementById('clientSearch').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                hideSearchResults();
                return;
            }

            showSearchLoading();
            searchTimeout = setTimeout(() => {
                searchClients(query);
            }, 300);
        });

        function searchClients(query) {
            fetch(`{{ url('/repayments/search-clients') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    hideSearchLoading();
                    displaySearchResults(data.clients);
                })
                .catch(error => {
                    hideSearchLoading();
                    console.error('Error searching clients:', error);
                });
        }

        function displaySearchResults(clients) {
            const resultsContainer = document.getElementById('searchResults');
            const results = document.querySelector('#searchResults .border');
            
            if (clients.length === 0) {
                results.innerHTML = '<div class="p-4 text-center text-gray-500">No clients found</div>';
            } else {
                results.innerHTML = clients.map(client => `
                    <div class="p-4 border-b border-gray-200 hover:bg-gray-50 cursor-pointer" onclick="selectClient(${client.id})">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">${client.name}</h4>
                                <p class="text-sm text-gray-600">${client.client_number} • ${client.phone}</p>
                                <p class="text-xs text-gray-500">${client.active_loans_count} active loans</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">TZS ${formatNumber(client.total_outstanding)}</p>
                                <p class="text-xs text-gray-500">Outstanding</p>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
            
            resultsContainer.classList.remove('hidden');
        }

        function selectClient(clientId) {
            fetch(`{{ url('/repayments/client') }}/${clientId}`)
                .then(response => response.json())
                .then(data => {
                    selectedClient = data;
                    displayClientDetails(data);
                    hideSearchResults();
                })
                .catch(error => {
                    console.error('Error fetching client details:', error);
                    showAlert('Error fetching client details', 'error');
                });
        }

        function displayClientDetails(data) {
            // Display client info
            document.getElementById('clientInfo').innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">${data.client.name}</h3>
                        <p class="text-sm text-gray-600">Client #: ${data.client.client_number}</p>
                        <p class="text-sm text-gray-600">Phone: ${data.client.phone}</p>
                        <p class="text-sm text-gray-600">Email: ${data.client.email}</p>
                    </div>
                </div>
            `;

            // Display loans
            const loansList = document.getElementById('loansList');
            if (data.loans.length === 0) {
                loansList.innerHTML = '<p class="text-gray-500 text-sm">No active loans</p>';
            } else {
                loansList.innerHTML = data.loans.map(loan => `
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">${loan.loan_number}</h4>
                                <p class="text-sm text-gray-600">${loan.product_name}</p>
                                <p class="text-xs text-gray-500">Status: ${loan.status}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">TZS ${formatNumber(loan.outstanding_balance)}</p>
                                <p class="text-xs text-gray-500">Outstanding</p>
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            // Display charges
            const chargesList = document.getElementById('chargesList');
            if (data.charges.length === 0) {
                chargesList.innerHTML = '<p class="text-gray-500 text-sm">No outstanding charges</p>';
            } else {
                chargesList.innerHTML = data.charges.map(charge => `
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">${charge.charge_type}</h4>
                                <p class="text-sm text-gray-600">${charge.description}</p>
                                <p class="text-xs text-gray-500">Loan: ${charge.loan_number}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">TZS ${formatNumber(charge.amount)}</p>
                                <p class="text-xs text-gray-500">${charge.status}</p>
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            // Populate loan dropdown
            const loanSelect = document.getElementById('selectedLoanId');
            loanSelect.innerHTML = '<option value="">Select Loan</option>' + 
                data.loans.map(loan => `<option value="${loan.id}">${loan.loan_number} - TZS ${formatNumber(loan.outstanding_balance)}</option>`).join('');

            // Populate charge dropdown
            const chargeSelect = document.getElementById('selectedChargeId');
            chargeSelect.innerHTML = '<option value="">Select Charge</option>' + 
                data.charges.map(charge => `<option value="${charge.id}">${charge.charge_type} - TZS ${formatNumber(charge.amount)}</option>`).join('');

            // Set client ID
            document.getElementById('selectedClientId').value = data.client.id;

            // Show client details section
            document.getElementById('clientDetailsSection').classList.remove('hidden');
        }

        // Payment type change handler
        document.getElementById('paymentType').addEventListener('change', function() {
            const loanSelection = document.getElementById('loanSelection');
            const chargeSelection = document.getElementById('chargeSelection');
            const loanSelect = document.getElementById('selectedLoanId');
            const chargeSelect = document.getElementById('selectedChargeId');

            // Hide both sections first
            loanSelection.classList.add('hidden');
            chargeSelection.classList.add('hidden');

            // Show relevant sections based on selection
            if (this.value === 'loan_repayment' || this.value === 'both') {
                loanSelection.classList.remove('hidden');
                loanSelect.required = true;
            } else {
                loanSelect.required = false;
            }

            if (this.value === 'charge_payment' || this.value === 'both') {
                chargeSelection.classList.remove('hidden');
                chargeSelect.required = true;
            } else {
                chargeSelect.required = false;
            }
        });

        // Payment form submission
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = document.getElementById('submitPayment');
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            const formData = new FormData(this);

            fetch('{{ url("/repayments/process") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                submitButton.textContent = 'Process Payment';
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    this.reset();
                    
                    // Show receipt if available
                    if (data.receipt) {
                        showReceipt(data.receipt);
                    }
                    
                    clearClientSelection();
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                submitButton.textContent = 'Process Payment';
                console.error('Error processing payment:', error);
                showAlert('An error occurred while processing the payment', 'error');
            });
        });

        // ==================== RECEIPT FUNCTIONS ====================
        
        function showReceipt(receipt) {
            const org = receipt.organization;
            const client = receipt.client;
            const loan = receipt.loan;
            const payment = receipt.payment;

            let loanSection = '';
            if (loan) {
                loanSection = `
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Loan Details</div>
                        <div style="background: #f9fafb; border-radius: 8px; padding: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <span style="color: #6b7280; font-size: 13px;">Loan Number</span>
                                <span style="font-weight: 600; font-size: 13px; color: #111827;">${loan.loan_number}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <span style="color: #6b7280; font-size: 13px;">Product</span>
                                <span style="font-weight: 500; font-size: 13px; color: #111827;">${loan.product_name}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <span style="color: #6b7280; font-size: 13px;">Balance Before Payment</span>
                                <span style="font-weight: 500; font-size: 13px; color: #111827;">TZS ${formatNumber(loan.outstanding_before)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-top: 6px; border-top: 1px dashed #d1d5db;">
                                <span style="color: #059669; font-weight: 600; font-size: 13px;">Balance After Payment</span>
                                <span style="font-weight: 700; font-size: 13px; color: #059669;">TZS ${formatNumber(loan.outstanding_after)}</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            let chargeSection = '';
            if (receipt.charge) {
                chargeSection = `
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Charge Details</div>
                        <div style="background: #f9fafb; border-radius: 8px; padding: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <span style="color: #6b7280; font-size: 13px;">Charge Type</span>
                                <span style="font-weight: 500; font-size: 13px; color: #111827;">${receipt.charge.type}</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            const receiptHTML = `
                <!-- Organization Header -->
                <div style="text-align: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #059669;">
                    ${org.logo ? `<img src="${org.logo}" alt="Logo" style="height: 48px; margin: 0 auto 8px;">` : ''}
                    <h2 style="font-size: 20px; font-weight: 700; color: #111827; margin: 0;">${org.name}</h2>
                    ${org.address ? `<p style="font-size: 12px; color: #6b7280; margin: 2px 0;">${org.address}${org.city ? ', ' + org.city : ''}</p>` : ''}
                    ${org.phone ? `<p style="font-size: 12px; color: #6b7280; margin: 2px 0;">Tel: ${org.phone}${org.email ? ' | ' + org.email : ''}</p>` : ''}
                </div>

                <!-- Receipt Title & Number -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="display: inline-block; background: linear-gradient(135deg, #059669, #047857); color: white; padding: 6px 20px; border-radius: 20px; font-size: 13px; font-weight: 600; letter-spacing: 0.05em;">
                        PAYMENT RECEIPT
                    </div>
                    <p style="font-size: 13px; color: #6b7280; margin-top: 8px;">
                        Receipt No: <span style="font-weight: 700; color: #111827;">${receipt.receipt_number}</span>
                    </p>
                    <p style="font-size: 13px; color: #6b7280;">
                        ${receipt.date} at ${receipt.time}
                    </p>
                </div>

                <!-- Client Details -->
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Received From</div>
                    <div style="background: #f9fafb; border-radius: 8px; padding: 12px;">
                        <div style="font-weight: 600; font-size: 15px; color: #111827;">${client.name}</div>
                        <div style="font-size: 13px; color: #6b7280;">Client #: ${client.client_number}</div>
                        <div style="font-size: 13px; color: #6b7280;">Phone: ${client.phone}</div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Payment Details</div>
                    <div style="border: 2px solid #059669; border-radius: 12px; padding: 16px; background: linear-gradient(135deg, #ecfdf5, #f0fdf4);">
                        <div style="text-align: center; margin-bottom: 12px;">
                            <div style="font-size: 12px; color: #059669; font-weight: 500;">Amount Paid</div>
                            <div style="font-size: 28px; font-weight: 800; color: #059669;">TZS ${formatNumber(payment.amount)}</div>
                        </div>
                        <div style="border-top: 1px dashed #a7f3d0; padding-top: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <span style="color: #6b7280; font-size: 13px;">Payment Type</span>
                                <span style="font-weight: 600; font-size: 13px; color: #111827;">${payment.type}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <span style="color: #6b7280; font-size: 13px;">Payment Method</span>
                                <span style="font-weight: 500; font-size: 13px; color: #111827;">${payment.method}</span>
                            </div>
                            ${payment.reference !== 'N/A' ? `
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <span style="color: #6b7280; font-size: 13px;">Reference</span>
                                <span style="font-weight: 500; font-size: 13px; color: #111827;">${payment.reference}</span>
                            </div>` : ''}
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280; font-size: 13px;">Collection Account</span>
                                <span style="font-weight: 500; font-size: 13px; color: #111827;">${payment.account}</span>
                            </div>
                        </div>
                    </div>
                </div>

                ${loanSection}
                ${chargeSection}

                ${payment.notes ? `
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Notes</div>
                    <div style="background: #f9fafb; border-radius: 8px; padding: 12px; font-size: 13px; color: #374151; font-style: italic;">${payment.notes}</div>
                </div>` : ''}

                <!-- Footer -->
                <div style="border-top: 1px solid #e5e7eb; padding-top: 12px; margin-top: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="color: #6b7280; font-size: 12px;">Processed By</span>
                        <span style="font-weight: 500; font-size: 12px; color: #111827;">${receipt.processed_by}</span>
                    </div>
                </div>

                <!-- Signature Area -->
                <div style="margin-top: 28px; display: flex; justify-content: space-between;">
                    <div style="text-align: center; width: 45%;">
                        <div style="border-top: 1px solid #9ca3af; padding-top: 4px; margin-top: 36px;">
                            <span style="font-size: 11px; color: #6b7280;">Officer Signature</span>
                        </div>
                    </div>
                    <div style="text-align: center; width: 45%;">
                        <div style="border-top: 1px solid #9ca3af; padding-top: 4px; margin-top: 36px;">
                            <span style="font-size: 11px; color: #6b7280;">Client Signature</span>
                        </div>
                    </div>
                </div>

                <!-- Thank You -->
                <div style="text-align: center; margin-top: 20px; padding-top: 12px; border-top: 2px solid #059669;">
                    <p style="font-size: 12px; color: #6b7280; margin: 0;">Thank you for your payment!</p>
                    <p style="font-size: 11px; color: #9ca3af; margin: 4px 0 0;">This is a computer-generated receipt.</p>
                </div>
            `;

            document.getElementById('receiptContent').innerHTML = receiptHTML;
            document.getElementById('receiptModal').classList.remove('hidden');
        }

        function closeReceiptModal() {
            document.getElementById('receiptModal').classList.add('hidden');
        }

        function printReceipt() {
            window.print();
        }

        // Close receipt modal on outside click
        document.getElementById('receiptModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReceiptModal();
            }
        });

        // ==================== END RECEIPT FUNCTIONS ====================

        function clearClientSelection() {
            selectedClient = null;
            document.getElementById('clientDetailsSection').classList.add('hidden');
            document.getElementById('clientSearch').value = '';
            hideSearchResults();
        }

        function hideSearchResults() {
            document.getElementById('searchResults').classList.add('hidden');
        }

        function showSearchLoading() {
            document.getElementById('searchLoading').classList.remove('hidden');
        }

        function hideSearchLoading() {
            document.getElementById('searchLoading').classList.add('hidden');
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            
            const alert = document.createElement('div');
            alert.className = `${alertClass} text-white px-6 py-3 rounded-lg shadow-lg mb-4 max-w-sm`;
            alert.innerHTML = `
                <div class="flex justify-between items-center">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            alertContainer.appendChild(alert);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 5000);
        }

        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }
    </script>
</x-app-shell>
