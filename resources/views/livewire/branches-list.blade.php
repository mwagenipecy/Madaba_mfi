<div>
    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Branches</h3>
            </div>
        </div>
        
        @if($branches->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($branches as $branch)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $branch->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $branch->code }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $branch->address }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $branch->phone }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $branch->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($branch->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('branches.edit', $branch) }}" class="text-green-600 hover:text-green-700 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md text-xs font-medium transition-colors">
                                            Edit
                                        </a>
                                        <a href="{{ route('branches.users', $branch) }}" class="text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md text-xs font-medium transition-colors">
                                            Users
                                        </a>
                                        @if($branch->status === 'active')
                                            <button onclick="openDisableModal({{ $branch->id }}, '{{ $branch->name }}')" class="text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md text-xs font-medium transition-colors">
                                                Disable
                                            </button>
                                        @else
                                            <button onclick="openEnableModal({{ $branch->id }}, '{{ $branch->name }}')" class="text-green-600 hover:text-green-700 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md text-xs font-medium transition-colors">
                                                Enable
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <p>No branches found</p>
                <p class="text-sm mt-1">Create your first branch to get started</p>
                <a href="{{ route('branches.create') }}" class="mt-4 inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Create Branch
                </a>
            </div>
        @endif
    </div>

    <!-- Disable Branch Modal -->
    <div id="disableModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-4">Disable Branch</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to disable the branch <span id="disableBranchName" class="font-medium text-gray-900"></span>?
                        This will change the branch status to inactive.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmDisable" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Disable
                    </button>
                    <button onclick="closeDisableModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enable Branch Modal -->
    <div id="enableModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-4">Enable Branch</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to enable the branch <span id="enableBranchName" class="font-medium text-gray-900"></span>?
                        This will change the branch status to active.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmEnable" class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                        Enable
                    </button>
                    <button onclick="closeEnableModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentBranchId = null;

        function openDisableModal(branchId, branchName) {
            currentBranchId = branchId;
            document.getElementById('disableBranchName').textContent = branchName;
            document.getElementById('disableModal').classList.remove('hidden');
        }

        function closeDisableModal() {
            document.getElementById('disableModal').classList.add('hidden');
            currentBranchId = null;
        }

        function openEnableModal(branchId, branchName) {
            currentBranchId = branchId;
            document.getElementById('enableBranchName').textContent = branchName;
            document.getElementById('enableModal').classList.remove('hidden');
        }

        function closeEnableModal() {
            document.getElementById('enableModal').classList.add('hidden');
            currentBranchId = null;
        }

        // Confirm disable
        document.getElementById('confirmDisable').addEventListener('click', function() {
            if (currentBranchId) {
                @this.disableBranch(currentBranchId);
                closeDisableModal();
            }
        });

        // Confirm enable
        document.getElementById('confirmEnable').addEventListener('click', function() {
            if (currentBranchId) {
                @this.enableBranch(currentBranchId);
                closeEnableModal();
            }
        });

        // Close modals when clicking outside
        document.getElementById('disableModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDisableModal();
            }
        });

        document.getElementById('enableModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEnableModal();
            }
        });
    </script>
</div>