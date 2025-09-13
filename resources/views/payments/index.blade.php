<x-app-shell title="Payments" header="Payments">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
                            <p class="text-gray-600 mt-1">Manage fund transfers and account recharges</p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('payments.fund-transfer.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Fund Transfer
                            </a>
                            <a href="{{ route('payments.account-recharge.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Recharge Account
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Fund Transfer Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Fund Transfers</h3>
                                <p class="text-gray-600">Transfer money between branches with approval workflow</p>
                                <a href="{{ route('payments.fund-transfer.create') }}" class="mt-2 inline-block text-green-600 hover:text-green-700 font-medium">Create Transfer →</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Recharge Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Account Recharge</h3>
                                <p class="text-gray-600">Recharge main accounts and distribute to branches</p>
                                <a href="{{ route('payments.account-recharge.create') }}" class="mt-2 inline-block text-blue-600 hover:text-blue-700 font-medium">Create Recharge →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @if(isset($recentTransfers) && $recentTransfers->count() > 0 || isset($recentRecharges) && $recentRecharges->count() > 0)
                        @if(isset($recentTransfers))
                            @foreach($recentTransfers as $transfer)
                                <div class="p-4 flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-green-100 rounded-lg">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $transfer->transfer_number }}</p>
                                            <p class="text-sm text-gray-500">
                                                {{ $transfer->fromAccount ? $transfer->fromAccount->name : 'N/A' }} → 
                                                {{ $transfer->toAccount ? $transfer->toAccount->name : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">TZS {{ number_format($transfer->amount, 2) }}</p>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $transfer->status_badge_color }}">
                                            {{ ucfirst($transfer->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if(isset($recentRecharges))
                            @foreach($recentRecharges as $recharge)
                                <div class="p-4 flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $recharge->recharge_number }}</p>
                                            <p class="text-sm text-gray-500">
                                                {{ $recharge->mainAccount ? $recharge->mainAccount->name : 'N/A' }} - 
                                                {{ $recharge->description }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">TZS {{ number_format($recharge->recharge_amount, 2) }}</p>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $recharge->status_badge_color }}">
                                            {{ ucfirst($recharge->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @else
                        <div class="p-6 text-center text-gray-500">
                            <p>Recent transactions will be displayed here</p>
                            <p class="text-sm mt-1">Fund transfers and account recharges will appear in this list</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-shell>