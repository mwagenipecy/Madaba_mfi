<x-app-shell>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analytics Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Critical Activities Section -->
            @if($criticalActivities->count() > 0)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Critical Activities & Alerts</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($criticalActivities as $activity)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 {{ $activity['severity'] === 'critical' ? 'border-red-400' : ($activity['severity'] === 'warning' ? 'border-yellow-400' : 'border-blue-400') }}">
                        <div class="p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 {{ $activity['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $activity['icon'] }}"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $activity['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Key Performance Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Portfolio Value -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-green-100">Portfolio Value</p>
                                <p class="text-2xl font-bold text-white">TZS {{ number_format($totalPortfolioValue, 0) }}</p>
                                <p class="text-xs text-green-200">{{ $activeLoans }} active loans</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collection Rate -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-blue-100">Collection Rate</p>
                                <p class="text-2xl font-bold text-white">{{ number_format($collectionRate, 1) }}%</p>
                                <p class="text-xs text-blue-200">Payment efficiency</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PAR 30 -->
                <div class="bg-gradient-to-r {{ $par30 > 5 ? 'from-red-500 to-red-600' : 'from-yellow-500 to-yellow-600' }} overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-white">PAR 30</p>
                                <p class="text-2xl font-bold text-white">{{ number_format($par30, 1) }}%</p>
                                <p class="text-xs text-white">{{ $overdueLoans }} overdue loans</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Clients -->
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-purple-100">Total Clients</p>
                                <p class="text-2xl font-bold text-white">{{ number_format($totalClients) }}</p>
                                <p class="text-xs text-purple-200">Active customers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Balances Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Bank Accounts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Bank Accounts</h3>
                        @if(count($accountBalances['banks']) > 0)
                            <div class="space-y-3">
                                @foreach($accountBalances['banks'] as $bank)
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $bank['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $bank['account_type'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900">TZS {{ number_format($bank['balance'], 2) }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No bank accounts found</p>
                        @endif
                    </div>
                </div>

                <!-- Mobile Money Accounts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Mobile Money</h3>
                        @if(count($accountBalances['mobile_money']) > 0)
                            <div class="space-y-3">
                                @foreach($accountBalances['mobile_money'] as $mno)
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $mno['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $mno['account_type'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900">TZS {{ number_format($mno['balance'], 2) }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No mobile money accounts found</p>
                        @endif
                    </div>
                </div>

                <!-- Cash Accounts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cash Accounts</h3>
                        @if(count($accountBalances['cash']) > 0)
                            <div class="space-y-3">
                                @foreach($accountBalances['cash'] as $cash)
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $cash['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $cash['account_type'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900">TZS {{ number_format($cash['balance'], 2) }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No cash accounts found</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Monthly Disbursements vs Collections -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Performance</h3>
                        <div class="h-64">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Loan Status Distribution -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Status Distribution</h3>
                        <div class="h-64">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics Section -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Disbursement Growth</span>
                                <span class="text-sm font-bold {{ $performanceMetrics['disbursement_growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $performanceMetrics['disbursement_growth'] >= 0 ? '+' : '' }}{{ number_format($performanceMetrics['disbursement_growth'], 1) }}%
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Collection Efficiency</span>
                                <span class="text-sm font-bold text-blue-600">{{ number_format($performanceMetrics['collection_efficiency'], 1) }}%</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Avg Loan Size</span>
                                <span class="text-sm font-bold text-gray-900">TZS {{ number_format($performanceMetrics['average_loan_size'], 0) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Client Growth</span>
                                <span class="text-sm font-bold {{ $performanceMetrics['client_growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $performanceMetrics['client_growth'] >= 0 ? '+' : '' }}{{ number_format($performanceMetrics['client_growth'], 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Type Distribution Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Distribution</h3>
                        <div class="h-48">
                            <canvas id="accountTypeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Branch Performance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Branch Performance</h3>
                        @if($branchPerformance->count() > 0)
                            <div class="space-y-3">
                                @foreach($branchPerformance->take(3) as $branch)
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $branch['branch']->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $branch['clients'] }} clients</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900">{{ $branch['active_loans'] }} loans</p>
                                        <p class="text-xs text-gray-500">TZS {{ number_format($branch['total_portfolio'], 0) }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No branch data available</p>
                        @endif
                    </div>
                </div>

                <!-- Total Balance Summary -->
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-indigo-100">Total Balance</p>
                                <p class="text-2xl font-bold text-white">TZS {{ number_format($accountBalances['total_balance'], 0) }}</p>
                                <p class="text-xs text-indigo-200">All accounts</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAR Analysis -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">PAR Analysis</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">PAR 30</span>
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ min($par30, 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold {{ $par30 > 5 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($par30, 1) }}%</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">PAR 60</span>
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-orange-500 h-2 rounded-full" style="width: {{ min($par60, 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ number_format($par60, 1) }}%</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">PAR 90</span>
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ min($par90, 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ number_format($par90, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expense Trends -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Expense Trends</h3>
                        <div class="h-48">
                            <canvas id="expenseChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Total Loans</span>
                                <span class="text-lg font-bold text-gray-900">{{ number_format($totalLoans) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Active Loans</span>
                                <span class="text-lg font-bold text-green-600">{{ number_format($activeLoans) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Overdue Loans</span>
                                <span class="text-lg font-bold text-red-600">{{ number_format($overdueLoans) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Total Expenses</span>
                                <span class="text-lg font-bold text-gray-900">TZS {{ number_format($totalExpenses, 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @forelse($recentActivities as $index => $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full {{ $activity['color'] }} bg-opacity-10 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-4 w-4 {{ $activity['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $activity['icon'] }}"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">{{ $activity['description'] }}</p>
                                                <p class="text-xs text-gray-400">TZS {{ number_format($activity['amount'], 2) }}</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ \Carbon\Carbon::parse($activity['date'])->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li>
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No recent activities</h3>
                                    <p class="mt-1 text-sm text-gray-500">Recent loan and payment activities will appear here.</p>
                                </div>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Monthly Performance Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyDisbursements->pluck('month')) !!},
                datasets: [{
                    label: 'Disbursements',
                    data: {!! json_encode($monthlyDisbursements->pluck('amount')) !!},
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.1
                }, {
                    label: 'Collections',
                    data: {!! json_encode($monthlyCollections->pluck('amount')) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'TZS ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Loan Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($loanStatusDistribution->pluck('status')) !!},
                datasets: [{
                    data: {!! json_encode($loanStatusDistribution->pluck('count')) !!},
                    backgroundColor: [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(107, 114, 128)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Expense Trends Chart
        const expenseCtx = document.getElementById('expenseChart').getContext('2d');
        new Chart(expenseCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyExpenses->pluck('month')) !!},
                datasets: [{
                    label: 'Expenses',
                    data: {!! json_encode($monthlyExpenses->pluck('amount')) !!},
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'TZS ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Account Type Distribution Chart
        const accountTypeCtx = document.getElementById('accountTypeChart').getContext('2d');
        const accountTypeData = {!! json_encode($accountTypeDistribution) !!};
        
        new Chart(accountTypeCtx, {
            type: 'doughnut',
            data: {
                labels: accountTypeData.map(item => item.account_type ? item.account_type.name : 'Unknown'),
                datasets: [{
                    data: accountTypeData.map(item => parseFloat(item.total_balance)),
                    backgroundColor: [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(147, 51, 234)',
                        'rgb(236, 72, 153)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return context.label + ': TZS ' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>
</x-app-shell>
