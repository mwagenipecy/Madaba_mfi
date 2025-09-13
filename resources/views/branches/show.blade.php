<x-app-shell title="Branch Details" header="Branch Details - {{ $branch->name }}">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('branches.index') }}" class="text-green-600 hover:text-green-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">Branch Details</h1>
                    </div>
                    <p class="text-gray-600">View branch information and statistics</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('branches.users', $branch) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        View Users
                    </a>
                    <a href="{{ route('branches.edit', $branch) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Branch
                    </a>
                </div>
            </div>
        </div>

        <!-- Branch Details Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Branch Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Branch Information</h3>
                    <div class="space-y-4">
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Branch Name</label>
                            <p class="text-gray-900 font-medium">{{ $branch->name }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Branch Code</label>
                            <p class="text-gray-900">{{ $branch->code }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Organization</label>
                            <p class="text-gray-900">{{ $branch->organization->name }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $branch->status_badge_color }}">
                                {{ ucfirst($branch->status) }}
                            </span>
                        </div>
                        @if($branch->description)
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Description</label>
                            <p class="text-gray-900">{{ $branch->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Contact Information</h3>
                    <div class="space-y-4">
                        @if($branch->address)
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Address</label>
                            <p class="text-gray-900">{{ $branch->address }}</p>
                        </div>
                        @endif
                        @if($branch->city || $branch->state || $branch->country)
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Location</label>
                            <p class="text-gray-900">
                                @if($branch->city){{ $branch->city }}@endif
                                @if($branch->state), {{ $branch->state }}@endif
                                @if($branch->country), {{ $branch->country }}@endif
                                @if($branch->postal_code) {{ $branch->postal_code }}@endif
                            </p>
                        </div>
                        @endif
                        @if($branch->phone)
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Phone</label>
                            <p class="text-gray-900">{{ $branch->phone }}</p>
                        </div>
                        @endif
                        @if($branch->email)
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Email</label>
                            <p class="text-gray-900">{{ $branch->email }}</p>
                        </div>
                        @endif
                        @if($branch->manager_name)
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Manager</label>
                            <p class="text-gray-900">{{ $branch->manager_name }}</p>
                            @if($branch->manager_email)
                                <p class="text-sm text-gray-600">{{ $branch->manager_email }}</p>
                            @endif
                            @if($branch->manager_phone)
                                <p class="text-sm text-gray-600">{{ $branch->manager_phone }}</p>
                            @endif
                        </div>
                        @endif
                        @if($branch->established_date)
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Established Date</label>
                            <p class="text-gray-900">{{ $branch->established_date->format('F d, Y') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Users</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $branch->users_count }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Active Users</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $branch->users()->where('status', 'active')->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Created</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $branch->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>
