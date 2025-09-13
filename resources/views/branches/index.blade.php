<x-app-shell title="Branch Management" header="Branch Management">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Branch Management</h1>
                    <p class="text-gray-600 mt-1">Manage all branches across organizations</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('branches.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Branch
                    </a>
                </div>
            </div>
        </div>

        <!-- Branches List Component -->
        <livewire:branches-list />
    </div>
</x-app-shell>
