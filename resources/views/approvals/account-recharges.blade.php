<x-app-shell title="Account Recharge Approvals" header="Account Recharge Approvals">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Account Recharge Approvals</h1>
                        <div class="flex space-x-2">
                            <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                                {{ $pendingRecharges->count() }} Pending Recharges
                            </span>
                        </div>
                    </div>
                    
                    <!-- Pending Account Recharges Table -->
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recharge #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capital Account</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (TZS)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source Account</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if($pendingRecharges->count() > 0)
                                        @php
                                            // Pre-load all giver accounts to avoid N+1 queries
                                            $giverAccountIds = $pendingRecharges->pluck('metadata')->filter(function($meta) {
                                                return isset($meta['giver_account_id']);
                                            })->pluck('giver_account_id')->unique()->filter();
                                            $giverAccounts = \App\Models\Account::whereIn('id', $giverAccountIds)->get()->keyBy('id');
                                        @endphp
                                        @foreach($pendingRecharges as $recharge)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $recharge->recharge_number }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @if($recharge->mainAccount)
                                                        <div class="flex flex-col">
                                                            <span class="font-medium">{{ $recharge->mainAccount->name }}</span>
                                                            <span class="text-xs text-gray-500">{{ $recharge->mainAccount->accountType->name ?? 'N/A' }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-gray-400">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    TZS {{ number_format($recharge->recharge_amount, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @if(isset($recharge->metadata['giver_account_id']) && isset($giverAccounts[$recharge->metadata['giver_account_id']]))
                                                        {{ $giverAccounts[$recharge->metadata['giver_account_id']]->name }}
                                                    @else
                                                        <span class="text-gray-400">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <div class="flex flex-col">
                                                        @if($recharge->description)
                                                            <span class="font-medium">{{ Str::limit($recharge->description, 40) }}</span>
                                                        @endif
                                                        @if($recharge->requester)
                                                            <span class="text-xs text-gray-500">By: {{ $recharge->requester->name }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $recharge->created_at->format('M d, Y H:i') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <button onclick="showApproveModal({{ $recharge->id }})" class="text-green-600 hover:text-green-900 font-medium">Approve</button>
                                                        <span class="text-gray-300">|</span>
                                                        <button onclick="showRejectModal({{ $recharge->id }})" class="text-red-600 hover:text-red-900 font-medium">Reject</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                                No pending account recharges at this time.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif

    <!-- Approve Modal -->
    <div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Approve Account Recharge</h3>
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="approval_notes" class="block text-sm font-medium text-gray-700 mb-2">Approval Notes (Optional)</label>
                        <textarea name="approval_notes" id="approval_notes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                  placeholder="Add any notes about this approval..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeApproveModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Account Recharge</h3>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                  placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showApproveModal(rechargeId) {
            const modal = document.getElementById('approveModal');
            const form = document.getElementById('approveForm');
            form.action = `/approvals/account-recharges/${rechargeId}/approve`;
            modal.classList.remove('hidden');
        }

        function closeApproveModal() {
            const modal = document.getElementById('approveModal');
            modal.classList.add('hidden');
            document.getElementById('approveForm').reset();
        }

        function showRejectModal(rechargeId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/approvals/account-recharges/${rechargeId}/reject`;
            modal.classList.remove('hidden');
        }

        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('hidden');
            document.getElementById('rejectForm').reset();
        }

        // Close modals when clicking outside
        document.getElementById('approveModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeApproveModal();
            }
        });

        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });

        // Auto-hide success/error messages
        setTimeout(function() {
            const messages = document.querySelectorAll('.fixed.top-4');
            messages.forEach(function(msg) {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(function() {
                    msg.remove();
                }, 500);
            });
        }, 5000);
    </script>
</x-app-shell>

