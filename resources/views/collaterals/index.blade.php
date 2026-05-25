<x-app-shell title="Collaterals" header="Collateral Management">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Collateral Management</h1>
                    <p class="text-sm text-gray-500 mt-1">Register assets per client. Each item can be pledged once to boost loan eligibility and amount.</p>
                </div>
                <a href="{{ route('collaterals.create') }}" class="inline-flex bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                    Register Collateral
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-200">
                    <p class="text-xs text-gray-500 uppercase">Total items</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border border-green-200">
                    <p class="text-xs text-gray-500 uppercase">Available</p>
                    <p class="text-2xl font-bold text-green-700">{{ $stats['available'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border border-blue-200">
                    <p class="text-xs text-gray-500 uppercase">Pledged</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $stats['pledged'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-200">
                    <p class="text-xs text-gray-500 uppercase">Total value</p>
                    <p class="text-2xl font-bold text-gray-900">TZS {{ number_format($stats['total_value'], 2) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">All</option>
                            <option value="available" @selected(request('status') === 'available')>Available</option>
                            <option value="pledged" @selected(request('status') === 'pledged')>Pledged</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Client</label>
                        <select name="client_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[200px]">
                            <option value="">All clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected(request('client_id') == $client->id)>
                                    {{ $client->client_type === 'individual'
                                        ? trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''))
                                        : ($client->business_name ?? 'Client') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title / Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan boost</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($collaterals as $collateral)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $collateral->reference_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $collateral->client?->display_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ $collateral->title }}</div>
                                        <div class="text-gray-500 text-xs">{{ $collateral->typeLabel() }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">TZS {{ number_format($collateral->estimated_value, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-green-700 font-medium">TZS {{ number_format($collateral->lendingCapacity(), 2) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $collateral->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $collateral->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($collateral->loan)
                                            <a href="{{ route('loans.show', $collateral->loan) }}" class="text-blue-600 hover:underline">{{ $collateral->loan->loan_number }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <a href="{{ route('collaterals.show', $collateral) }}" class="text-green-600 hover:underline">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-500">No collateral registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($collaterals->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">{{ $collaterals->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-shell>
