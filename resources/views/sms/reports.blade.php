<x-app-shell title="SMS Reports" header="SMS Reports">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">SMS Reports</h1>
                    <p class="text-sm text-gray-500 mt-1">Track SMS sending transactions for management and audit.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('sms.index') }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg">
                        Bulk SMS
                    </a>
                    <a href="{{ route('sms.manual') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg">
                        Manual SMS
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Sent</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($summary['sent']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Failed</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($summary['failed']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">SMS cost units</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['cost']) }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg mb-6 p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="all" @selected(request('status', 'all') === 'all')>All</option>
                            <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                            <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                        <select name="source" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="all" @selected(request('source', 'all') === 'all')>All</option>
                            <option value="bulk" @selected(request('source') === 'bulk')>Bulk</option>
                            <option value="manual" @selected(request('source') === 'manual')>Manual</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Criteria</label>
                        <select name="criteria" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="all" @selected(request('criteria', 'all') === 'all')>All</option>
                            @foreach($criteriaOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('criteria') === $value)>{{ $label }}</option>
                            @endforeach
                            <option value="manual_selection" @selected(request('criteria') === 'manual_selection')>Manual selection</option>
                            <option value="manual_numbers" @selected(request('criteria') === 'manual_numbers')>Manual numbers</option>
                            <option value="client_created" @selected(request('criteria') === 'client_created')>Client created</option>
                            <option value="loan_approved" @selected(request('criteria') === 'loan_approved')>Loan approved</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Phone, client, job id...">
                    </div>
                    @if(request('batch_id'))
                        <input type="hidden" name="batch_id" value="{{ request('batch_id') }}">
                    @endif
                    <div class="md:col-span-3 lg:col-span-6 flex justify-end gap-2">
                        <a href="{{ route('sms.reports') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Reset</a>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900">Apply filters</button>
                    </div>
                </form>
                @if(request('batch_id'))
                    <p class="mt-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg px-3 py-2">
                        Showing batch <span class="font-mono">{{ request('batch_id') }}</span>
                    </p>
                @endif
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Criteria</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sent by</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50 align-top">
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                        {{ $log->created_at?->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ $log->client->display_name ?? 'External' }}</div>
                                        <div class="text-gray-500">{{ $log->recipient }}</div>
                                        @if($log->loan)
                                            <div class="text-xs text-gray-400">{{ $log->loan->loan_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 max-w-xs">
                                        <div class="line-clamp-3">{{ $log->content }}</div>
                                        @if($log->error_message)
                                            <div class="mt-1 text-xs text-red-600">{{ \Illuminate\Support\Str::limit($log->error_message, 120) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $criteriaOptions[$log->criteria] ?? str_replace('_', ' ', $log->criteria ?? '—') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 capitalize">{{ $log->source }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $log->status_badge_color }}">
                                            {{ $log->status }}
                                        </span>
                                        @if($log->job_id)
                                            <div class="text-[10px] text-gray-400 mt-1 font-mono truncate max-w-[120px]" title="{{ $log->job_id }}">
                                                {{ $log->job_id }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $log->sender->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $log->cost ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-500">No SMS transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-shell>
