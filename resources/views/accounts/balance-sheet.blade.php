<x-app-shell title="Balance Sheet" header="Balance Sheet">
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-48">
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                    <select name="branch_id" id="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex-1 min-w-48">
                    <label for="as_of_date" class="block text-sm font-medium text-gray-700 mb-2">As of Date</label>
                    <input type="date" name="as_of_date" id="as_of_date" value="{{ $asOfDate }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Generate
                    </button>
                    
                    <a href="{{ route('accounts.balance-sheet.export', ['branch_id' => $branchId, 'as_of_date' => $asOfDate]) }}" 
                       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export PDF
                    </a>
                </div>
            </form>
        </div>

        <!-- Balance Sheet Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Balance Sheet</h1>
                <p class="text-lg text-gray-600 mb-1">{{ $balanceSheetData['organization']->name }}</p>
                @if($balanceSheetData['branch'])
                    <p class="text-sm text-gray-500 mb-2">{{ $balanceSheetData['branch']->name }}</p>
                @endif
                <p class="text-sm text-gray-500">As of {{ $balanceSheetData['as_of_date']->format('F d, Y') }}</p>
            </div>
        </div>

        <!-- Balance Sheet Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <!-- Assets Column -->
                <div class="border-r border-gray-200">
                    <div class="bg-green-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-green-800">ASSETS</h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Current Assets -->
                        @if(count($balanceSheetData['assets']['current_assets']) > 0)
                            <div>
                                <h3 class="text-md font-semibold text-gray-800 mb-3">Current Assets</h3>
                                <div class="space-y-2">
                                    @foreach($balanceSheetData['assets']['current_assets'] as $asset)
                                        <div class="flex justify-between items-center py-1">
                                            <span class="text-sm text-gray-700">{{ $asset['type'] }}</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($asset['total'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-sm text-gray-800">Total Current Assets</span>
                                        <span class="text-sm text-gray-900">{{ number_format($balanceSheetData['assets']['current_assets_total'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Fixed Assets -->
                        @if(count($balanceSheetData['assets']['fixed_assets']) > 0)
                            <div>
                                <h3 class="text-md font-semibold text-gray-800 mb-3">Fixed Assets</h3>
                                <div class="space-y-2">
                                    @foreach($balanceSheetData['assets']['fixed_assets'] as $asset)
                                        <div class="flex justify-between items-center py-1">
                                            <span class="text-sm text-gray-700">{{ $asset['type'] }}</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($asset['total'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-sm text-gray-800">Total Fixed Assets</span>
                                        <span class="text-sm text-gray-900">{{ number_format($balanceSheetData['assets']['fixed_assets_total'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Other Assets -->
                        @if(count($balanceSheetData['assets']['other_assets']) > 0)
                            <div>
                                <h3 class="text-md font-semibold text-gray-800 mb-3">Other Assets</h3>
                                <div class="space-y-2">
                                    @foreach($balanceSheetData['assets']['other_assets'] as $asset)
                                        <div class="flex justify-between items-center py-1">
                                            <span class="text-sm text-gray-700">{{ $asset['type'] }}</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($asset['total'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-sm text-gray-800">Total Other Assets</span>
                                        <span class="text-sm text-gray-900">{{ number_format($balanceSheetData['assets']['other_assets_total'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Total Assets -->
                        <div class="border-t-2 border-gray-300 pt-4">
                            <div class="flex justify-between items-center text-lg font-bold">
                                <span class="text-gray-800">TOTAL ASSETS</span>
                                <span class="text-gray-900">{{ number_format($balanceSheetData['totals']['total_assets'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liabilities & Equity Column -->
                <div>
                    <div class="bg-blue-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-blue-800">LIABILITIES & EQUITY</h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Current Liabilities -->
                        @if(count($balanceSheetData['liabilities']['current_liabilities']) > 0)
                            <div>
                                <h3 class="text-md font-semibold text-gray-800 mb-3">Current Liabilities</h3>
                                <div class="space-y-2">
                                    @foreach($balanceSheetData['liabilities']['current_liabilities'] as $liability)
                                        <div class="flex justify-between items-center py-1">
                                            <span class="text-sm text-gray-700">{{ $liability['type'] }}</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($liability['total'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-sm text-gray-800">Total Current Liabilities</span>
                                        <span class="text-sm text-gray-900">{{ number_format($balanceSheetData['liabilities']['current_liabilities_total'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Long-term Liabilities -->
                        @if(count($balanceSheetData['liabilities']['long_term_liabilities']) > 0)
                            <div>
                                <h3 class="text-md font-semibold text-gray-800 mb-3">Long-term Liabilities</h3>
                                <div class="space-y-2">
                                    @foreach($balanceSheetData['liabilities']['long_term_liabilities'] as $liability)
                                        <div class="flex justify-between items-center py-1">
                                            <span class="text-sm text-gray-700">{{ $liability['type'] }}</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($liability['total'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-sm text-gray-800">Total Long-term Liabilities</span>
                                        <span class="text-sm text-gray-900">{{ number_format($balanceSheetData['liabilities']['long_term_liabilities_total'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Total Liabilities -->
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between items-center font-semibold">
                                <span class="text-sm text-gray-800">TOTAL LIABILITIES</span>
                                <span class="text-sm text-gray-900">{{ number_format($balanceSheetData['totals']['total_liabilities'], 2) }}</span>
                            </div>
                        </div>

                        <!-- Owner Equity -->
                        @if(count($balanceSheetData['equity']['owner_equity']) > 0)
                            <div>
                                <h3 class="text-md font-semibold text-gray-800 mb-3">Owner Equity</h3>
                                <div class="space-y-2">
                                    @foreach($balanceSheetData['equity']['owner_equity'] as $equity)
                                        <div class="flex justify-between items-center py-1">
                                            <span class="text-sm text-gray-700">{{ $equity['type'] }}</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($equity['total'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-sm text-gray-800">Total Owner Equity</span>
                                        <span class="text-sm text-gray-900">{{ number_format($balanceSheetData['equity']['owner_equity_total'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Retained Earnings -->
                        @if(count($balanceSheetData['equity']['retained_earnings']) > 0)
                            <div>
                                <h3 class="text-md font-semibold text-gray-800 mb-3">Retained Earnings</h3>
                                <div class="space-y-2">
                                    @foreach($balanceSheetData['equity']['retained_earnings'] as $equity)
                                        <div class="flex justify-between items-center py-1">
                                            <span class="text-sm text-gray-700">{{ $equity['type'] }}</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($equity['total'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-sm text-gray-800">Total Retained Earnings</span>
                                        <span class="text-sm text-gray-900">{{ number_format($balanceSheetData['equity']['retained_earnings_total'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Total Equity -->
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between items-center font-semibold">
                                <span class="text-sm text-gray-800">TOTAL EQUITY</span>
                                <span class="text-sm text-gray-900">{{ number_format($balanceSheetData['totals']['total_equity'], 2) }}</span>
                            </div>
                        </div>

                        <!-- Total Liabilities & Equity -->
                        <div class="border-t-2 border-gray-300 pt-4">
                            <div class="flex justify-between items-center text-lg font-bold">
                                <span class="text-gray-800">TOTAL LIABILITIES & EQUITY</span>
                                <span class="text-gray-900">{{ number_format($balanceSheetData['totals']['total_liabilities_and_equity'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Verification -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                @if($balanceSheetData['totals']['is_balanced'])
                    <div class="inline-flex items-center px-4 py-2 rounded-lg bg-green-100 text-green-800">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium">Balance Sheet is Balanced</span>
                    </div>
                @else
                    <div class="inline-flex items-center px-4 py-2 rounded-lg bg-red-100 text-red-800">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <span class="font-medium">Balance Sheet is Not Balanced</span>
                    </div>
                @endif
                
                <div class="mt-4 text-sm text-gray-600">
                    <p>Assets: {{ number_format($balanceSheetData['totals']['total_assets'], 2) }}</p>
                    <p>Liabilities & Equity: {{ number_format($balanceSheetData['totals']['total_liabilities_and_equity'], 2) }}</p>
                    @if(!$balanceSheetData['totals']['is_balanced'])
                        <p class="text-red-600 font-medium">
                            Difference: {{ number_format($balanceSheetData['totals']['total_assets'] - $balanceSheetData['totals']['total_liabilities_and_equity'], 2) }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-shell>
