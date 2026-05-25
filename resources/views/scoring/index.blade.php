<x-app-shell title="Scoring" header="Client Scoring">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Client lookup --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Credit Scoring</h2>
                    <p class="text-sm text-gray-500 mb-4">Select a client to view their system score, loan history, and detailed risk report.</p>

                    <form method="GET" action="{{ route('scoring.index') }}" class="max-w-2xl">
                        <label for="client_search" class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                        <div class="relative">
                            <input type="text" id="client_search"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   placeholder="Search by name, phone, or client number..."
                                   autocomplete="off"
                                   value="{{ $selectedClient?->display_name }}">
                            <div id="client_dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                            <input type="hidden" name="client_id" id="client_id" value="{{ $selectedClient?->id }}">
                        </div>
                        <div class="mt-4 flex gap-3">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-medium transition-colors">
                                Get Score Report
                            </button>
                            @if($report && $selectedClient)
                                <a href="{{ route('scoring.pdf', $selectedClient) }}"
                                   class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg font-medium transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Download PDF
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            @if($report && $selectedClient)
                @php
                    $bandColors = [
                        'excellent' => 'border-green-600 text-green-700 bg-green-50',
                        'good' => 'border-blue-600 text-blue-700 bg-blue-50',
                        'fair' => 'border-yellow-500 text-yellow-700 bg-yellow-50',
                        'poor' => 'border-orange-500 text-orange-700 bg-orange-50',
                        'critical' => 'border-red-600 text-red-700 bg-red-50',
                    ];
                    $circleClass = $bandColors[$report['band']] ?? $bandColors['fair'];
                @endphp

                {{-- Score overview --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                            <div class="flex items-center gap-5">
                                <div class="flex items-center justify-center w-24 h-24 rounded-full border-4 text-3xl font-bold {{ $circleClass }}">
                                    {{ $report['score'] }}
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ $report['client']['display_name'] }}</h3>
                                    <p class="text-sm text-gray-500">{{ $report['client']['client_number'] }} &middot; {{ ucfirst($report['client']['client_type']) }}</p>
                                    <p class="mt-1 text-lg font-semibold {{ str_contains($circleClass, 'red') ? 'text-red-700' : 'text-green-700' }}">
                                        {{ $report['band_label'] }} ({{ ucfirst($report['band']) }})
                                    </p>
                                    <span class="mt-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $report['passes'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $report['passes'] ? 'Eligible' : 'Not Eligible' }}
                                    </span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <div class="p-3 bg-gray-50 rounded-lg text-center">
                                    <p class="text-gray-500 text-xs">Total Loans</p>
                                    <p class="text-xl font-bold">{{ $report['history']['total_loans'] }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-lg text-center">
                                    <p class="text-gray-500 text-xs">Open Contracts</p>
                                    <p class="text-xl font-bold text-blue-700">{{ count($report['loans']['open']) }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-lg text-center">
                                    <p class="text-gray-500 text-xs">Closed</p>
                                    <p class="text-xl font-bold text-green-700">{{ count($report['loans']['closed']) }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-lg text-center">
                                    <p class="text-gray-500 text-xs">Max Arrears Days</p>
                                    <p class="text-xl font-bold {{ $report['history']['max_overdue_days'] > 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $report['history']['max_overdue_days'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Why score is low --}}
                @if($report['score'] < 65 || count($report['negative_contributors']) > 0)
                <div class="bg-red-50 border border-red-200 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-red-900 mb-3">Why This Score?</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-800 mb-4">
                            @foreach($report['score_explanation'] as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>

                        @if(count($report['negative_contributors']) > 0)
                            <h4 class="text-sm font-semibold text-red-900 mb-2">Loans That Hurt the Score</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-red-200 text-sm">
                                    <thead class="bg-red-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-800 uppercase">Loan</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-800 uppercase">Product</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-800 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-800 uppercase">Arrears Days</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-800 uppercase">Impact</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-800 uppercase">Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-red-100 bg-white">
                                        @foreach($report['negative_contributors'] as $loan)
                                            <tr>
                                                <td class="px-4 py-2 font-medium">{{ $loan['loan_number'] }}</td>
                                                <td class="px-4 py-2">{{ $loan['product_name'] }}</td>
                                                <td class="px-4 py-2 capitalize">{{ $loan['status'] }}</td>
                                                <td class="px-4 py-2">{{ $loan['overdue_days'] }}</td>
                                                <td class="px-4 py-2 text-red-700 font-semibold">{{ $loan['score_impact'] }}</td>
                                                <td class="px-4 py-2 text-gray-700">{{ implode(' ', $loan['impact_reasons']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Charts: score trend + payment trend --}}
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-lg font-semibold text-gray-900">Score Trend ({{ $report['score_trend']['year'] }})</h3>
                                @php
                                    $trend = $report['score_trend']['trend_direction'];
                                    $trendClass = $trend === 'up' ? 'text-green-700 bg-green-50' : ($trend === 'down' ? 'text-red-700 bg-red-50' : 'text-gray-700 bg-gray-50');
                                    $trendLabel = $trend === 'up' ? 'Improving' : ($trend === 'down' ? 'Declining' : 'Stable');
                                @endphp
                                <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $trendClass }}">
                                    {{ $trendLabel }} ({{ $report['score_trend']['trend_delta'] >= 0 ? '+' : '' }}{{ $report['score_trend']['trend_delta'] }} pts)
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">Monthly score — green = good, red = poor</p>
                            <div class="relative h-72">
                                <canvas id="scoreTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Loan Payment Trend ({{ $report['payment_trend']['year'] }})</h3>
                            <p class="text-sm text-gray-500 mb-1">Total paid this year: <span class="font-semibold text-green-700">TZS {{ number_format($report['payment_trend']['total'], 2) }}</span></p>
                            <div class="relative h-72 mt-3">
                                <canvas id="paymentTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Full KYC section --}}
                @include('scoring.partials.kyc-section')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Score factors --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Score Factors</h3>
                            @foreach([
                                'repayment_history' => ['label' => 'Repayment History', 'color' => 'bg-green-600'],
                                'arrears' => ['label' => 'Arrears Record', 'color' => 'bg-blue-600'],
                                'profile' => ['label' => 'Profile & KYC', 'color' => 'bg-purple-600'],
                                'portfolio' => ['label' => 'Portfolio Behavior', 'color' => 'bg-amber-600'],
                            ] as $key => $meta)
                                <div class="mb-3">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600">{{ $meta['label'] }}</span>
                                        <span class="font-medium">{{ $report['factors'][$key] }}/100</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="{{ $meta['color'] }} h-2 rounded-full" style="width: {{ $report['factors'][$key] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Client Profile</h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                <div><dt class="text-gray-500">Status</dt><dd class="font-medium capitalize">{{ $report['client']['status'] }}</dd></div>
                                <div><dt class="text-gray-500">Phone</dt><dd class="font-medium">{{ $report['client']['phone_number'] ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Email</dt><dd class="font-medium">{{ $report['client']['email'] ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Branch</dt><dd class="font-medium">{{ $report['client']['branch'] ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Monthly Income</dt><dd class="font-medium">TZS {{ number_format($report['client']['monthly_income'], 2) }}</dd></div>
                                <div><dt class="text-gray-500">Age</dt><dd class="font-medium">{{ $report['profile']['age'] ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Occupation</dt><dd class="font-medium">{{ $report['client']['occupation'] ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Employer</dt><dd class="font-medium">{{ $report['client']['employer_name'] ?? '—' }}</dd></div>
                            </dl>
                        </div>
                    </div>

                </div>

                {{-- Products taken --}}
                @if(count($report['products_taken']) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Products Taken</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Times Taken</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Last Taken</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($report['products_taken'] as $product)
                                        <tr>
                                            <td class="px-4 py-2 font-medium">{{ $product['product_name'] }}</td>
                                            <td class="px-4 py-2">{{ $product['product_code'] }}</td>
                                            <td class="px-4 py-2">{{ $product['times_taken'] }}</td>
                                            <td class="px-4 py-2">TZS {{ number_format($product['total_amount'], 2) }}</td>
                                            <td class="px-4 py-2">{{ $product['last_taken'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Previous closed contracts --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Previous Contracts (Closed Loans)</h3>
                        <p class="text-sm text-gray-500 mb-4">Completed and other closed loan history for this client.</p>

                        <h4 class="text-sm font-semibold text-green-800 mb-2">Successfully Completed ({{ count($report['loans']['previous_completed']) }})</h4>
                        @if(count($report['loans']['previous_completed']) > 0)
                            @include('scoring.partials.closed-loans-table', ['loans' => $report['loans']['previous_completed']])
                        @else
                            <p class="text-sm text-gray-500 mb-4">No successfully completed previous contracts.</p>
                        @endif

                        @if(count($report['loans']['previous_other']) > 0)
                            <h4 class="text-sm font-semibold text-red-800 mb-2 mt-6">Written Off / Rejected / Cancelled ({{ count($report['loans']['previous_other']) }})</h4>
                            @include('scoring.partials.closed-loans-table', ['loans' => $report['loans']['previous_other']])
                        @endif
                    </div>
                </div>

                {{-- Open contracts --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Open Contracts ({{ count($report['loans']['open']) }})</h3>
                        @if(count($report['loans']['open']) > 0)
                            @include('scoring.partials.loans-table', ['loans' => $report['loans']['open']])
                        @else
                            <p class="text-sm text-gray-500">No open contracts.</p>
                        @endif
                    </div>
                </div>

                {{-- Flags --}}
                @if(count($report['flags']) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Alerts</h3>
                        <ul class="space-y-2">
                            @foreach($report['flags'] as $flag)
                                @php
                                    $flagClass = match($flag['type']) {
                                        'danger' => 'bg-red-50 text-red-800 border-red-200',
                                        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        default => 'bg-blue-50 text-blue-800 border-blue-200',
                                    };
                                @endphp
                                <li class="p-3 rounded-lg border text-sm {{ $flagClass }}">{{ $flag['message'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>

    @if($report && $selectedClient)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const scoreTrendData = @json($report['score_trend']);
            const paymentTrendData = @json($report['payment_trend']);

            function scoreColor(score) {
                if (score >= 80) return '#16a34a';
                if (score >= 65) return '#22c55e';
                if (score >= 50) return '#eab308';
                if (score >= 35) return '#f97316';
                return '#dc2626';
            }

            const scoreLabels = scoreTrendData.months.map(m => m.label);
            const scoreValues = scoreTrendData.months.map(m => m.score);
            const scoreColors = scoreTrendData.months.map(m => m.color);

            new Chart(document.getElementById('scoreTrendChart'), {
                type: 'line',
                data: {
                    labels: scoreLabels,
                    datasets: [{
                        label: 'Credit Score',
                        data: scoreValues,
                        borderWidth: 3,
                        tension: 0.35,
                        fill: false,
                        pointRadius: 5,
                        pointBackgroundColor: scoreColors,
                        pointBorderColor: scoreColors,
                        segment: {
                            borderColor: ctx => {
                                const y0 = ctx.p0.parsed?.y;
                                const y1 = ctx.p1.parsed?.y;
                                if (y0 == null || y1 == null) return '#d1d5db';
                                const avg = (y0 + y1) / 2;
                                return scoreColor(Math.round(avg));
                            }
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            ticks: { stepSize: 10 }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const row = scoreTrendData.months[ctx.dataIndex];
                                    if (row.score == null) return 'No data';
                                    let label = `Score: ${row.score}/100 (${row.band_label})`;
                                    if (row.change != null) {
                                        label += ` | ${row.change >= 0 ? '+' : ''}${row.change} vs prev month`;
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('paymentTrendChart'), {
                type: 'bar',
                data: {
                    labels: paymentTrendData.months.map(m => m.label),
                    datasets: [{
                        label: 'Payments (TZS)',
                        data: paymentTrendData.months.map(m => m.amount),
                        backgroundColor: paymentTrendData.months.map(m => m.amount > 0 ? 'rgba(22, 163, 74, 0.75)' : 'rgba(209, 213, 219, 0.6)'),
                        borderColor: paymentTrendData.months.map(m => m.amount > 0 ? '#16a34a' : '#d1d5db'),
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => 'TZS ' + Number(value).toLocaleString()
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'TZS ' + Number(ctx.parsed.y).toLocaleString(undefined, { minimumFractionDigits: 2 })
                            }
                        }
                    }
                }
            });
        </script>
    @endif

    <script>
        const clients = @json($clients);
        const clientSearch = document.getElementById('client_search');
        const clientDropdown = document.getElementById('client_dropdown');
        const clientIdInput = document.getElementById('client_id');

        function filterClients(term) {
            if (!term) { clientDropdown.classList.add('hidden'); return; }
            const q = term.toLowerCase();
            const filtered = clients.filter(c => {
                const name = `${c.first_name||''} ${c.last_name||''} ${c.middle_name||''} ${c.business_name||''}`.toLowerCase();
                return name.includes(q) || (c.phone_number||'').includes(q) || (c.client_number||'').toLowerCase().includes(q);
            });
            if (!filtered.length) {
                clientDropdown.innerHTML = '<div class="p-3 text-sm text-gray-500">No clients found</div>';
            } else {
                clientDropdown.innerHTML = filtered.map(c => {
                    const name = c.client_type === 'individual'
                        ? `${c.first_name||''} ${c.last_name||''}`.trim()
                        : (c.business_name || 'Business');
                    return `<div class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 client-option" data-id="${c.id}" data-name="${name}">
                        <div class="font-medium text-gray-900">${name}</div>
                        <div class="text-xs text-gray-500">${c.client_number||''}${c.phone_number ? ' • '+c.phone_number : ''}</div>
                    </div>`;
                }).join('');
                clientDropdown.querySelectorAll('.client-option').forEach(el => {
                    el.addEventListener('click', () => {
                        clientIdInput.value = el.dataset.id;
                        clientSearch.value = el.dataset.name;
                        clientDropdown.classList.add('hidden');
                    });
                });
            }
            clientDropdown.classList.remove('hidden');
        }

        clientSearch?.addEventListener('input', e => filterClients(e.target.value));
        clientSearch?.addEventListener('focus', e => filterClients(e.target.value));
        document.addEventListener('click', e => {
            if (!clientSearch?.contains(e.target) && !clientDropdown?.contains(e.target)) {
                clientDropdown?.classList.add('hidden');
            }
        });
    </script>
</x-app-shell>
