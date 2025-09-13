<x-app-shell>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Risk Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="days_overdue" class="block text-sm font-medium text-gray-700 mb-1">Days Overdue</label>
                            <select id="days_overdue" name="days_overdue" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="30" {{ $daysOverdue == 30 ? 'selected' : '' }}>30+ days</option>
                                <option value="60" {{ $daysOverdue == 60 ? 'selected' : '' }}>60+ days</option>
                                <option value="90" {{ $daysOverdue == 90 ? 'selected' : '' }}>90+ days</option>
                                <option value="120" {{ $daysOverdue == 120 ? 'selected' : '' }}>120+ days</option>
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

            <!-- Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Arrears Summary</h3>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total Arrears Amount</p>
                            <p class="text-2xl font-bold text-red-600">TZS {{ number_format($totalArrears, 2) }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">
                        {{ $arrears->count() }} overdue payments from {{ $arrearsByClient->count() }} clients
                    </p>
                </div>
            </div>

            <!-- Arrears by Client -->
            @if($arrearsByClient->count() > 0)
                @foreach($arrearsByClient as $clientId => $clientArrears)
                @php $client = $clientArrears->first()->loan->client; @endphp
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $client->first_name }} {{ $client->last_name }}
                                </h3>
                                <p class="text-sm text-gray-500">{{ $client->client_type }} • {{ $client->phone_number }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ $clientArrears->count() }} overdue payments</p>
                                <p class="text-lg font-semibold text-red-600">TZS {{ number_format($clientArrears->sum('amount_due'), 2) }}</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Installment</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Overdue</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Due</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($clientArrears as $payment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $payment->loan->loan_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $payment->loan->loanProduct->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $payment->installment_number }}/{{ $payment->loan->tenure_months }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($payment->due_date)->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ \Carbon\Carbon::parse($payment->due_date)->diffInDays(now()) }} days
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            TZS {{ number_format($payment->amount_due, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('loans.show', $payment->loan) }}" class="text-green-600 hover:text-green-700">
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
                @endforeach
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No arrears found</h3>
                        <p class="mt-1 text-sm text-gray-500">No overdue payments found for the selected criteria.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-shell>
