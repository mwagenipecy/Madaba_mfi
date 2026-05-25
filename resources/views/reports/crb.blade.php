<x-app-shell title="CRB Report" header="CRB Report">
    <div class="space-y-6">
        <!-- Report Description -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-blue-800">Credit Reference Bureau (CRB) Report</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Download each CRB report as a separate CSV file. Individual and Contract are linked by <strong>Customer Code</strong>:</p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            <li><strong>Contract:</strong> Latest active contract per customer only</li>
                            <li><strong>Individual:</strong> Clients matching those contracts (Customer Code)</li>
                            <li><strong>Subject Relation:</strong> Client relationships and references</li>
                            <li><strong>Company:</strong> Business/group clients matching those contracts (Customer Code)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="GET" action="{{ route('reports.crb.export') }}" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Branch Filter -->
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                        <select name="branch_id" id="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Client Filter -->
                    <div>
                        <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">Client</label>
                        <select name="client_id" id="client_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" name="start_date" id="start_date" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" name="end_date" id="end_date" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                <!-- Date Range Validation -->
                <div id="date-validation" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Date Range Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>Start date must be before end date.</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($errors->has('sheet'))
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm text-red-700">{{ $errors->first('sheet') }}</p>
                    </div>
                @endif

                <!-- Download Buttons -->
                <div class="pt-6 border-t border-gray-200 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            <p>Select filters, then download the sheet you need. Leave dates empty to include all active contracts.</p>
                        </div>
                        <button type="button" onclick="clearFilters()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Clear Filters
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <button type="submit" name="sheet" value="contract" class="download-btn px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Contract
                        </button>
                        <button type="submit" name="sheet" value="individual" class="download-btn px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Individual
                        </button>
                        <button type="submit" name="sheet" value="subject-relation" class="download-btn px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Subject Relation
                        </button>
                        <button type="submit" name="sheet" value="company" class="download-btn px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Company
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Report Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Report Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Contract Sheet</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• One row per customer (latest active contract)</li>
                        <li>• Customer Code links to Individual sheet</li>
                        <li>• Exported as CSV</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Individual Sheet</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Only clients with an active contract in Contract sheet</li>
                        <li>• Join key: Customer Code (<code>client_number</code>)</li>
                        <li>• Exported as CSV</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Subject Relation Sheet</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Client relationship types</li>
                        <li>• Guarantor and co-signer information</li>
                        <li>• Reference contacts</li>
                        <li>• Relation status and notes</li>
                        <li>• Created date and history</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Company Sheet</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Only business/group clients with an active contract in Contract sheet</li>
                        <li>• Join key: Customer Code (<code>client_number</code>)</li>
                        <li>• Company registration, address, and contact details</li>
                        <li>• Exported as CSV</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Date validation
        function validateDates() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const validationDiv = document.getElementById('date-validation');
            const downloadBtns = document.querySelectorAll('.download-btn');

            if (startDate && endDate && startDate > endDate) {
                validationDiv.classList.remove('hidden');
                downloadBtns.forEach(btn => {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                });
            } else {
                validationDiv.classList.add('hidden');
                downloadBtns.forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            }
        }

        // Clear all filters
        function clearFilters() {
            document.getElementById('branch_id').value = '';
            document.getElementById('client_id').value = '';
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.getElementById('date-validation').classList.add('hidden');
            document.querySelectorAll('.download-btn').forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        }

        // Add event listeners
        document.getElementById('start_date').addEventListener('change', validateDates);
        document.getElementById('end_date').addEventListener('change', validateDates);

    </script>
</x-app-shell>

