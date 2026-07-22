<x-app-shell title="Bulk SMS" header="Bulk SMS to Clients">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Bulk SMS</h1>
                    <p class="text-sm text-gray-500 mt-1">Send SMS to clients by loan end date, due date, arrears, and more.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('sms.manual') }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg">
                        Manual SMS
                    </a>
                    <a href="{{ route('sms.reports') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg">
                        SMS Reports
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Recipient criteria</h2>
                    <p class="text-sm text-gray-500 mt-1">Choose who should receive the message, then preview before sending.</p>
                </div>
                <form method="GET" action="{{ route('sms.index') }}" class="p-6 space-y-4">
                    <input type="hidden" name="preview" value="1">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Criteria</label>
                            <select name="criteria" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                @foreach($criteriaOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['criteria'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target date</label>
                            <input type="date" name="target_date" value="{{ $filters['target_date'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Days before</label>
                            <input type="number" min="0" max="90" name="days_before" value="{{ $filters['days_before'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Days after</label>
                            <input type="number" min="0" max="90" name="days_after" value="{{ $filters['days_after'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                            <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">All branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) $filters['branch_id'] === (string) $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min overdue days</label>
                            <input type="number" min="0" name="min_overdue_days" value="{{ $filters['min_overdue_days'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="Optional">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loan status</label>
                            <select name="loan_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="all" @selected($filters['loan_status'] === 'all')>Active / Overdue / Disbursed</option>
                                <option value="active" @selected($filters['loan_status'] === 'active')>Active</option>
                                <option value="overdue" @selected($filters['loan_status'] === 'overdue')>Overdue</option>
                                <option value="disbursed" @selected($filters['loan_status'] === 'disbursed')>Disbursed</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" value="{{ $filters['search'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="Client name, phone, loan number...">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-medium py-2 px-5 rounded-lg">
                            Preview recipients
                        </button>
                    </div>
                </form>
            </div>

            @if(request()->filled('preview'))
                <form method="POST" action="{{ route('sms.send-bulk') }}" class="space-y-6"
                      onsubmit="return confirm('Send SMS to selected recipients now?');">
                    @csrf
                    <input type="hidden" name="criteria" value="{{ $filters['criteria'] }}">
                    <input type="hidden" name="target_date" value="{{ $filters['target_date'] }}">
                    <input type="hidden" name="days_before" value="{{ $filters['days_before'] }}">
                    <input type="hidden" name="days_after" value="{{ $filters['days_after'] }}">
                    <input type="hidden" name="branch_id" value="{{ $filters['branch_id'] }}">
                    <input type="hidden" name="min_overdue_days" value="{{ $filters['min_overdue_days'] }}">
                    <input type="hidden" name="loan_status" value="{{ $filters['loan_status'] }}">
                    <input type="hidden" name="search" value="{{ $filters['search'] }}">

                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Recipients ({{ $recipients->count() }})</h2>
                                <p class="text-sm text-gray-500">Uncheck anyone you want to exclude before sending.</p>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" checked
                                       onchange="document.querySelectorAll('.loan-check').forEach(cb => cb.checked = this.checked)"
                                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                Select all
                            </label>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Send</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Maturity</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Arrears</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($recipients as $loan)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <input type="checkbox" name="loan_ids[]" value="{{ $loan->id }}" checked
                                                       class="loan-check rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $loan->client->display_name ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-500">{{ $loan->client->client_number ?? '' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $loan->client->phone_number }}</td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm text-gray-900">{{ $loan->loan_number }}</div>
                                                <div class="text-xs text-gray-500">{{ $loan->status }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $loan->maturity_date?->format('d M Y') ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if(($loan->overdue_days ?? 0) > 0 || ($loan->overdue_amount ?? 0) > 0)
                                                    <span class="text-red-600 font-medium">{{ $loan->overdue_days }}d</span>
                                                    <div class="text-xs text-red-500">TZS {{ number_format($loan->overdue_amount, 0) }}</div>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                                No recipients match these criteria.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($recipients->count() > 0)
                        <div class="bg-white shadow-sm rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">Message</h2>
                            <p class="text-xs text-gray-500 mb-3">
                                Placeholders: {client_name}, {loan_number}, {overdue_days}, {overdue_amount}, {outstanding_balance}, {maturity_date}
                            </p>
                            <textarea name="message" rows="5" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      placeholder="Write SMS content...">{{ old('message', $defaultMessage) }}</textarea>
                            <div class="mt-4 flex justify-end">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-6 rounded-lg">
                                    Send SMS to selected
                                </button>
                            </div>
                        </div>
                    @endif
                </form>
            @endif
        </div>
    </div>
</x-app-shell>
