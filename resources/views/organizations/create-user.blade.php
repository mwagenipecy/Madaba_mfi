<x-app-shell title="Create Organization User" header="Add New User - {{ $organization->name }}">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('organizations.users', $organization) }}" class="text-green-600 hover:text-green-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">Add New User</h1>
                    </div>
                    <p class="text-gray-600">Create a new user for {{ $organization->name }}</p>
                </div>
            </div>
        </div>

        <!-- Create Organization User Component -->
        <livewire:create-organization-user :organization="$organization" />

     
    </div>
</x-app-shell>
