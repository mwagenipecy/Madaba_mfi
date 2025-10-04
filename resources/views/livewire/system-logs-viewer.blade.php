<div>
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">System Logs</h3>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" wire:model.live="search" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="Search logs...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                    <select wire:model.live="levelFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">All Levels</option>
                        <option value="emergency">Emergency</option>
                        <option value="alert">Alert</option>
                        <option value="critical">Critical</option>
                        <option value="error">Error</option>
                        <option value="warning">Warning</option>
                        <option value="notice">Notice</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
                    <select wire:model.live="actionFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">All Actions</option>
                        <option value="user_login">User Login</option>
                        <option value="user_logout">User Logout</option>
                        <option value="account_created">Account Created</option>
                        <option value="loan_created">Loan Created</option>
                        <option value="loan_approved">Loan Approved</option>
                        <option value="fund_transfer">Fund Transfer</option>
                        <option value="system_error">System Error</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" wire:model.live="dateFrom" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" wire:model.live="dateTo" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
        </div>
        
        @if($logs->count() > 0)
            <!-- Results Summary -->
            <div class="px-6 py-3 bg-blue-50 border-b border-blue-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-blue-800">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
                    </p>
                    <div class="text-xs text-blue-600">
                        Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
                    </div>
                </div>
            </div>

            <!-- Table Container with improved scrolling -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="font-mono text-xs">
                                        {{ $log->created_at->format('M d, Y') }}
                                        <br>
                                        <span class="text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ 
                                        $log->level === 'error' || $log->level === 'critical' ? 'bg-red-100 text-red-800' : 
                                        ($log->level === 'warning' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($log->level === 'info' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'))
                                    }}">
                                        {{ strtoupper($log->level) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="font-medium">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-md">
                                        <div class="break-words" title="{{ $log->description }}">
                                            {{ $log->description }}
                                        </div>
                                        @if($log->data && count($log->data) > 0)
                                            <button onclick="toggleData({{ $log->id }})" class="text-xs text-blue-600 hover:text-blue-800 mt-2 inline-flex items-center gap-1 transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                                View Details
                                            </button>
                                            <div id="data-{{ $log->id }}" class="hidden mt-3 p-3 bg-gray-50 rounded-lg border text-xs">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="font-medium text-gray-700">Additional Data:</span>
                                                    <button onclick="toggleData({{ $log->id }})" class="text-gray-500 hover:text-gray-700">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <pre class="whitespace-pre-wrap bg-white p-2 rounded border text-gray-700 overflow-x-auto">{{ json_encode($log->data, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        @if($log->user)
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-xs font-medium text-green-800">{{ substr($log->user->name, 0, 1) }}</span>
                                            </div>
                                            <span class="font-medium">{{ $log->user->name }}</span>
                                        @else
                                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <span class="font-medium text-gray-500">System</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($log->model_type && $log->model_id)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $logs->links() }}
                </div>
            @endif
        @else
            <div class="p-6 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p>No system logs found</p>
                <p class="text-sm mt-1">System logs will appear here as activities occur</p>
            </div>
        @endif
    </div>

    <script>
        function toggleData(logId) {
            const dataDiv = document.getElementById('data-' + logId);
            const button = event.target.closest('button');
            
            if (dataDiv.classList.contains('hidden')) {
                dataDiv.classList.remove('hidden');
                // Update button text and icon
                const buttonText = button.querySelector('svg');
                if (buttonText) {
                    buttonText.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>';
                }
                button.innerHTML = button.innerHTML.replace('View Details', 'Hide Details');
                
                // Smooth scroll to show the expanded content
                setTimeout(() => {
                    dataDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);
            } else {
                dataDiv.classList.add('hidden');
                // Update button text and icon
                const buttonText = button.querySelector('svg');
                if (buttonText) {
                    buttonText.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>';
                }
                button.innerHTML = button.innerHTML.replace('Hide Details', 'View Details');
            }
        }

        // Add keyboard accessibility
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                // Close all open detail panels
                const openPanels = document.querySelectorAll('[id^="data-"]:not(.hidden)');
                openPanels.forEach(panel => {
                    panel.classList.add('hidden');
                    const logId = panel.id.replace('data-', '');
                    const button = document.querySelector(`button[onclick*="${logId}"]`);
                    if (button) {
                        const buttonText = button.querySelector('svg');
                        if (buttonText) {
                            buttonText.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>';
                        }
                        button.innerHTML = button.innerHTML.replace('Hide Details', 'View Details');
                    }
                });
            }
        });
    </script>
</div>