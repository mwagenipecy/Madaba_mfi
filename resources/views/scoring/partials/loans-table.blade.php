<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Loan #</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Arrears Days</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Overdue Amt</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Impact</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($loans as $loan)
                <tr>
                    <td class="px-4 py-2 font-medium">{{ $loan['loan_number'] }}</td>
                    <td class="px-4 py-2">{{ $loan['product_name'] }}</td>
                    <td class="px-4 py-2">TZS {{ number_format($loan['loan_amount'], 2) }}</td>
                    <td class="px-4 py-2">TZS {{ number_format($loan['outstanding_balance'], 2) }}</td>
                    <td class="px-4 py-2 {{ $loan['overdue_days'] > 0 ? 'text-red-700 font-semibold' : '' }}">{{ $loan['overdue_days'] }}</td>
                    <td class="px-4 py-2">TZS {{ number_format($loan['overdue_amount'], 2) }}</td>
                    <td class="px-4 py-2 capitalize">{{ $loan['status'] }}</td>
                    <td class="px-4 py-2 {{ $loan['score_impact'] < 0 ? 'text-red-700' : ($loan['score_impact'] > 0 ? 'text-green-700' : 'text-gray-500') }}">
                        {{ $loan['score_impact'] > 0 ? '+' : '' }}{{ $loan['score_impact'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
