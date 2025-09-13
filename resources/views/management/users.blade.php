<x-app-shell title="System Users Management" header="System Users Management">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">System Users Management</h1>
                    <p class="text-gray-600 mt-1">Manage all system users across organizations</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Admin Access Required</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Management Component -->
        <livewire:system-users-management />
    </div>
</x-app-shell>
