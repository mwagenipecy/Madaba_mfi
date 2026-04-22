<x-app-shell>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daily Repayment Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
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
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Apply
                            </button>
                            <a href="{{ route('reports.daily-repayments') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                                Reset
                            </a>
                        </div>
                    </form>
                    <p class="text-sm text-gray-500 mt-2">Repayments recorded on the <a href="{{ url('/repayments') }}" class="text-green-600 hover:underline">Repayments</a> page appear here.</p>
                </div>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <p class="text-sm font-medium text-gray-500">Total Collected</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">TZS {{ number_format($totalCollected, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $transactionCount }} payment(s)</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <p class="text-sm font-medium text-gray-500">Cash Collected</p>
                        <p class="text-xl font-bold text-gray-900 mt-1">TZS {{ number_format($byPaymentMethod['cash'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $countByMethod['cash'] }} payment(s)</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <p class="text-sm font-medium text-gray-500">Mobile Wallet Collected</p>
                        <p class="text-xl font-bold text-gray-900 mt-1">TZS {{ number_format($byPaymentMethod['mobile_money'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $countByMethod['mobile_money'] }} payment(s)</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <p class="text-sm font-medium text-gray-500">Other (Bank / Check / Other)</p>
                        <p class="text-xl font-bold text-gray-900 mt-1">TZS {{ number_format($byPaymentMethod['bank_transfer'] + $byPaymentMethod['check'] + $byPaymentMethod['other'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $countByMethod['bank_transfer'] + $countByMethod['check'] + $countByMethod['other'] }} payment(s)</p>
                    </div>
                </div>
            </div>

            <!-- Principal / Interest summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Breakdown</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Principal</p>
                            <p class="font-medium text-gray-900">TZS {{ number_format($totalPrincipal, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Interest</p>
                            <p class="font-medium text-gray-900">TZS {{ number_format($totalInterest, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Average per payment</p>
                            <p class="font-medium text-gray-900">TZS {{ number_format($averagePayment, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Period</p>
                            <p class="font-medium text-gray-900">{{ $startDate->format('M d, Y') }} – {{ $endDate->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily breakdown (compact) -->
            @if($dailyBreakdown->isNotEmpty())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Summary by Date</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cash</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Mobile Wallet</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Count</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($dailyBreakdown as $day)
                                <tr>
                                    <td class="px-4 py-2 text-gray-900">{{ \Carbon\Carbon::parse($day['date'])->format('M d, Y') }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900">TZS {{ number_format($day['cash'], 2) }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900">TZS {{ number_format($day['mobile_money'], 2) }}</td>
                                    <td class="px-4 py-2 text-right font-medium text-gray-900">TZS {{ number_format($day['amount'], 2) }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ $day['count'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Repayments list -->
            @if($repayments->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Repayment Records</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="dailyRepaymentsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan #</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Principal</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Payment</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded By</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($repayments as $repayment)
                                @php
                                    $loan = $repayment->loan;
                                    $nextSchedule = $loan ? $loan->schedules->first() : null;
                                    $paymentMethodLabel = ($repayment->payment_type ?? '') === 'loan_closure'
                                        ? 'Loan closure'
                                        : match($repayment->payment_method) {
                                            'cash' => 'Cash',
                                            'mobile_money' => 'Mobile Wallet',
                                            'bank_transfer' => 'Bank Transfer',
                                            'check', 'cheque' => 'Check',
                                            default => ucfirst(str_replace('_', ' ', $repayment->payment_method ?? 'Other')),
                                          };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ $repayment->payment_date ? $repayment->payment_date->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ $loan && $loan->client ? $loan->client->display_name : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $loan->loan_number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                        TZS {{ number_format($repayment->amount ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700">
                                        TZS {{ number_format($repayment->principal_amount ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700">
                                        TZS {{ number_format($repayment->interest_amount ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ $paymentMethodLabel }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                                        @if($loan)
                                            TZS {{ number_format($loan->calculated_outstanding_amount ?? 0, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                        @if($nextSchedule && $nextSchedule->due_date)
                                            {{ \Carbon\Carbon::parse($nextSchedule->due_date)->format('M d, Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        @if($repayment->recordedBy)
                                            {{ $repayment->recordedBy->first_name }} {{ $repayment->recordedBy->last_name }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        @if($loan)
                                            <a href="{{ route('loans.show', $loan) }}" class="text-green-600 hover:text-green-700">View Loan</a>
                                        @else
                                            —
                                        @endif
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
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No repayments found</h3>
                    <p class="mt-1 text-sm text-gray-500">No repayments recorded for the selected date range. Record repayments on the <a href="{{ url('/repayments') }}" class="text-green-600 hover:underline">Repayments</a> page.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-shell>
