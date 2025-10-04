        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Monthly Performance Chart -->
            <div class="chart-container bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Monthly Performance</h3>
                <div class="h-72 pb-8">
                    <canvas id="monthlyPerformanceChart"></canvas>
                </div>
            </div>

            <!-- Loan Status Distribution -->
            <div class="chart-container bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Loan Status Distribution</h3>
                <div class="h-72 pb-12">
                    <canvas id="loanStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Account Balances Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Account Balances</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Bank Accounts -->
                <div>
                    <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                        Bank Accounts
                    </h4>
                    <div class="space-y-2">
                        @forelse($accountBalances['banks'] as $account)
                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $account['name'] }}</span>
                            <span class="font-medium text-green-600">{{ number_format($account['balance'], 2) }}</span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No bank accounts found</p>
                        @endforelse
                    </div>
                </div>

                <!-- Mobile Money -->
                <div>
                    <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                        </svg>
                        Mobile Money
                    </h4>
                    <div class="space-y-2">
                        @forelse($accountBalances['mobile_money'] as $account)
                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $account['name'] }}</span>
                            <span class="font-medium text-blue-600">{{ number_format($account['balance'], 2) }}</span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No mobile money accounts found</p>
                        @endforelse
                    </div>
                </div>

                <!-- Cash Accounts -->
                <div>
                    <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                        </svg>
                        Cash Accounts
                    </h4>
                    <div class="space-y-2">
                        @forelse($accountBalances['cash'] as $account)
                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $account['name'] }}</span>
                            <span class="font-medium text-yellow-600">{{ number_format($account['balance'], 2) }}</span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No cash accounts found</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Total Balance Summary -->
            <div class="mt-6 p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border border-green-200">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-green-800">Total Balance</span>
                    <span class="text-2xl font-bold text-green-600">{{ number_format($accountBalances['total_balance'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- PAR Analysis -->
        <div class="bg-white rounded-lg border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Portfolio at Risk (PAR) Analysis</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600">{{ number_format($parData['par30'], 2) }}%</div>
                    <div class="text-sm text-gray-600">PAR 30</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ min($parData['par30'], 100) }}%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ number_format($parData['par60'], 2) }}%</div>
                    <div class="text-sm text-gray-600">PAR 60</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-orange-500 h-2 rounded-full" style="width: {{ min($parData['par60'], 100) }}%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600">{{ number_format($parData['par90'], 2) }}%</div>
                    <div class="text-sm text-gray-600">PAR 90</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ min($parData['par90'], 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Payments Section -->
        <div class="bg-white rounded-lg border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
                Upcoming Payments (3 Days Ago to 3 Days Ahead)
            </h3>
            
            @if(count($upcomingPayments) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($upcomingPayments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ substr($payment['client_name'], 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $payment['client_name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $payment['client_phone'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $payment['loan_product'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $payment['due_date']->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">{{ number_format($payment['total_amount'], 2) }}</div>
                                @if($payment['paid_amount'] > 0)
                                <div class="text-xs text-gray-500">Paid: {{ number_format($payment['paid_amount'], 2) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $payment['status'] === 'overdue' ? 'bg-red-100 text-red-800' : ($payment['status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ ucfirst($payment['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($payment['is_overdue'])
                                    <span class="text-red-600 font-medium">{{ abs($payment['days_diff']) }} days overdue</span>
                                @elseif($payment['is_due_today'])
                                    <span class="text-orange-600 font-medium">Due today</span>
                                @elseif($payment['is_due_soon'])
                                    <span class="text-yellow-600 font-medium">Due in {{ $payment['days_diff'] }} days</span>
                                @else
                                    <span class="text-gray-600">{{ $payment['days_diff'] }} days</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Summary Cards -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">Overdue Payments</p>
                            <p class="text-2xl font-semibold text-red-900">{{ $upcomingPayments->where('is_overdue', true)->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-orange-800">Due Today</p>
                            <p class="text-2xl font-semibold text-orange-900">{{ $upcomingPayments->where('is_due_today', true)->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">Due Soon</p>
                            <p class="text-2xl font-semibold text-yellow-900">{{ $upcomingPayments->where('is_due_soon', true)->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No upcoming payments</h3>
                <p class="mt-1 text-sm text-gray-500">No payments are due in the next 3 days or were due in the last 3 days.</p>
            </div>
            @endif
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>
            <div class="space-y-4">
                @forelse($recentActivities as $activity)
                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="{{ $activity['icon'] }}"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                        <p class="text-sm text-gray-500">{{ $activity['description'] }}</p>
                        <p class="text-xs text-gray-400">{{ $activity['date'] ? $activity['date']->format('M d, Y H:i') : 'N/A' }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">No recent activities found</p>
                @endforelse
            </div>
        </div>

        <!-- Branch Performance (Admin only) -->
        @if($branchPerformance && count($branchPerformance) > 0)
        <div class="bg-white rounded-lg border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Branch Performance</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($branchPerformance as $branch)
                <div class="p-4 border rounded-lg">
                    <h4 class="font-medium text-gray-900">{{ $branch['name'] }}</h4>
                    <div class="mt-2 space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Active Loans:</span>
                            <span class="font-medium">{{ $branch['active_loans'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Portfolio:</span>
                            <span class="font-medium">{{ number_format($branch['total_portfolio'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Clients:</span>
                            <span class="font-medium">{{ $branch['total_clients'] }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
