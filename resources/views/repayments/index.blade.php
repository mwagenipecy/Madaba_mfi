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
