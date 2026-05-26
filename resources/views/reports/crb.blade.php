<x-app-shell title="CRB Report" header="CRB Report">
    <div class="space-y-6">
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
                        <p>Retrieve and preview CRB data by report type. Contracts are disbursed loans only; Individual and Company rows are clients on those contracts.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="GET" action="{{ route('reports.crb') }}" class="space-y-6" id="crb-filter-form">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                        <select name="branch_id" id="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">Client</label>
                        <select name="client_id" id="client_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected(($filters['client_id'] ?? '') == $client->id)>
                                    {{ $client->display_name }} ({{ $client->client_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['contract' => 'Contract', 'individual' => 'Individual', 'company' => 'Company'] as $type => $label)
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="sheet" value="{{ $type }}" class="sr-only"
                                       @checked($sheetType === $type) onchange="this.form.submit()">
                                <span class="px-4 py-2 rounded-lg border text-sm font-medium transition-colors {{ $sheetType === $type ? 'bg-green-600 text-white border-green-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if($dateError)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm text-red-700">{{ $dateError }}</p>
                    </div>
                @endif

                <div id="date-validation" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-700">Start date must be before end date.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-gray-200">
                    <button type="submit" class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-colors">
                        Retrieve Data
                    </button>
                    <button type="button" onclick="clearFilters()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Clear Filters
                    </button>
                    @if($preview && !$dateError)
                        <a href="{{ route('reports.crb.export', array_merge($filters, ['sheet' => $sheetType])) }}"
                           class="px-5 py-2.5 bg-gray-800 text-white rounded-lg hover:bg-gray-900 font-medium transition-colors inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export {{ $preview['label'] }} CSV
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($preview && !$dateError)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $preview['label'] }} Data</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $preview['count'] }} record(s) found</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        {{ ucfirst($sheetType) }} report
                    </span>
                </div>

                @if($preview['count'] > 0)
                    <div class="overflow-x-auto max-h-[32rem]">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-green-700 text-white sticky top-0 z-10">
                                <tr>
                                    @foreach($preview['headers'] as $header)
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider whitespace-nowrap">{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($preview['rows'] as $row)
                                    <tr class="hover:bg-gray-50">
                                        @foreach($row as $cell)
                                            <td class="px-3 py-2 text-gray-900 whitespace-nowrap max-w-xs truncate" title="{{ $cell }}">{{ $cell !== '' ? $cell : '—' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center text-gray-500">
                        <p class="font-medium text-gray-700">No {{ strtolower($preview['label']) }} records match your filters.</p>
                        <p class="text-sm mt-2">
                            @if($sheetType === 'contract')
                                Only disbursed contracts are included (active, overdue, completed, written off). Pending and approved-but-not-disbursed loans are excluded.
                            @else
                                {{ ucfirst($sheetType) }} clients must have at least one disbursed contract in the filtered results.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Report Types</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm text-gray-600">
                <div>
                    <h4 class="font-medium text-gray-800 mb-2">Contract</h4>
                    <p>One row per disbursed loan — status active, overdue, completed, or written off.</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800 mb-2">Individual</h4>
                    <p>Individual clients who have at least one disbursed contract in the report.</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800 mb-2">Company</h4>
                    <p>Business and group clients who have at least one disbursed contract in the report.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function validateDates() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const validationDiv = document.getElementById('date-validation');
            const submitBtn = document.querySelector('#crb-filter-form button[type="submit"]');

            if (startDate && endDate && startDate > endDate) {
                validationDiv.classList.remove('hidden');
                if (submitBtn) submitBtn.disabled = true;
            } else {
                validationDiv.classList.add('hidden');
                if (submitBtn) submitBtn.disabled = false;
            }
        }

        function clearFilters() {
            window.location.href = @json(route('reports.crb', ['sheet' => $sheetType]));
        }

        document.getElementById('start_date').addEventListener('change', validateDates);
        document.getElementById('end_date').addEventListener('change', validateDates);
        validateDates();
    </script>
</x-app-shell>
