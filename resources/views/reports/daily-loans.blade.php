<x-app-shell>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daily Loan Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                    <form method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Single Date</label>
                                <input type="date" id="date" name="date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       value="{{ request('date', $singleDate ? $startDate->format('Y-m-d') : '') }}"
                                       placeholder="Pick a day">
                                <p class="mt-1 text-xs text-gray-500">Loans approved on this date</p>
                            </div>
                            <div class="flex items-center text-gray-500 text-sm self-center md:self-end md:pb-2">or</div>
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" id="start_date" name="start_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input type="date" id="end_date" name="end_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                                <p class="mt-1 text-xs text-gray-500">Date range for approvals</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Apply Filters
                            </button>
                            <a href="{{ route('reports.daily-loans') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                                Reset (Today)
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Approved Amount</p>
                            <p class="text-2xl font-bold text-green-600 mt-1">TZS {{ number_format($totalApproved, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Number of Loans</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $loanCount }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Average Loan Amount</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">TZS {{ number_format($averageAmount, 2) }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">
                        @if($singleDate)
                            Loans approved on <strong>{{ $startDate->format('l, M d, Y') }}</strong>
                        @else
                            Loans approved between <strong>{{ $startDate->format('M d, Y') }}</strong> and <strong>{{ $endDate->format('M d, Y') }}</strong>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Loans List -->
            @if($loans->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Loans Given (Approved)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Required</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenure</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($loans as $loan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $loan->approval_date ? \Carbon\Carbon::parse($loan->approval_date)->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $loan->loan_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $loan->client->display_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $loan->loanProduct->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        TZS {{ number_format($loan->approved_amount ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($loan->interest_rate ?? 0, 2) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        TZS {{ number_format($loan->total_required_repayment ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-700">
                                        TZS {{ number_format($loan->calculated_outstanding_amount ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $loan->loan_tenure_months ?? 0 }} months
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $loan->branch->name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($loan->approvedBy)
                                            {{ $loan->approvedBy->first_name }} {{ $loan->approvedBy->last_name }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('loans.show', $loan) }}" class="text-green-600 hover:text-green-700">
                                            View Loan
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No loans found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        No approved loans for the selected {{ $singleDate ? 'date' : 'date range' }}. Loans appear here when they are approved.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-shell>
