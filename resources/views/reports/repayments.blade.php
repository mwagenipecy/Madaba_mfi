<x-app-shell title="Performance Analytics" header="Performance Analytics">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Repayments</dt>
                                    <dd class="text-lg font-medium text-gray-900">TZS {{ number_format($totalRepayments, 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Principal Collected</dt>
                                    <dd class="text-lg font-medium text-gray-900">TZS {{ number_format($totalPrincipal, 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Interest Collected</dt>
                                    <dd class="text-lg font-medium text-gray-900">TZS {{ number_format($totalInterest, 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
                                    <dd class="text-lg font-medium text-gray-900">TZS {{ number_format($thisMonthTotal, 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trends Chart -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Repayment Trends (Last 6 Months)</h3>
                    <div class="h-64 flex items-end space-x-2">
                        @foreach($trends as $trend)
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-full bg-green-200 rounded-t" style="height: {{ $trend['amount'] > 0 ? max(20, ($trend['amount'] / max(array_column($trends, 'amount'))) * 200) : 0 }}px;"></div>
                                <div class="text-xs text-gray-500 mt-2 text-center">
                                    <div>{{ $trend['month'] }}</div>
                                    <div class="font-medium">TZS {{ number_format($trend['amount'], 0) }}</div>
                                    <div class="text-gray-400">{{ $trend['count'] }} payments</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" id="date_from" name="date_from" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   value="{{ request('date_from') }}">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" id="date_to" name="date_to" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   value="{{ request('date_to') }}">
                        </div>
                        <div>
                            <label for="loan_officer_id" class="block text-sm font-medium text-gray-700 mb-1">Loan Officer</label>
                            <select id="loan_officer_id" name="loan_officer_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">All Officers</option>
                                @foreach($loanOfficers as $officer)
                                    <option value="{{ $officer->id }}" {{ request('loan_officer_id') == $officer->id ? 'selected' : '' }}>
                                        {{ $officer->first_name }} {{ $officer->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                            <select id="client_id" name="client_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">All Clients</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Repayments Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Repayment Records</h3>
                        <div class="flex space-x-2">
                            <button onclick="exportToExcel()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Export Excel
                            </button>
                            <button onclick="printReport()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Print Report
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="repaymentsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Officer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Principal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($repayments as $repayment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $repayment->transaction_date?->format('M d, Y') ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $repayment->transaction_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $repayment->loan->client->display_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $repayment->loan->loanOfficer ? $repayment->loan->loanOfficer->first_name . ' ' . $repayment->loan->loanOfficer->last_name : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            TZS {{ number_format($repayment->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            TZS {{ number_format($repayment->principal_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            TZS {{ number_format($repayment->interest_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ ucfirst(str_replace('_', ' ', $repayment->payment_method)) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                            No repayment records found for the selected period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($repayments->hasPages())
                        <div class="mt-6">
                            {{ $repayments->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportToExcel() {
            // Simple Excel export functionality
            const table = document.getElementById('repaymentsTable');
            const rows = Array.from(table.querySelectorAll('tr'));
            const csvContent = rows.map(row => 
                Array.from(row.querySelectorAll('th, td')).map(cell => 
                    '"' + cell.textContent.replace(/"/g, '""') + '"'
                ).join(',')
            ).join('\n');
            
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'repayment_report_' + new Date().toISOString().split('T')[0] + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function printReport() {
            window.print();
        }
    </script>
</x-app-shell>
