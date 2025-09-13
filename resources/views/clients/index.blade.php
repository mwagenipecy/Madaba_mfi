<x-app-shell title="Client Management" header="Client Management">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Client Management</h1>
                    <p class="text-gray-600 mt-1">Manage individual and business clients with comprehensive KYC</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('clients.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Add New Client
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Clients -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Clients</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalClients }}</p>
                    </div>
                </div>
            </div>

            <!-- Individual Clients -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Individual Clients</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $individualClients }}</p>
                    </div>
                </div>
            </div>

            <!-- Business Clients -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Business Clients</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $businessClients }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending KYC -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending KYC</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $pendingKyc }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client Types -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Individual Clients -->
            <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('clients.individual') }}'">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Individual Clients</h3>
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">Personal</span>
                </div>
                <p class="text-gray-600 text-sm mb-4">Personal clients with individual KYC requirements</p>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total:</span>
                        <span class="font-semibold">{{ $individualClients }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Verified:</span>
                        <span class="font-semibold text-green-600">{{ \App\Models\Client::where('organization_id', auth()->user()->organization_id ?? 1)->where('client_type', 'individual')->where('kyc_status', 'verified')->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Pending:</span>
                        <span class="font-semibold text-yellow-600">{{ \App\Models\Client::where('organization_id', auth()->user()->organization_id ?? 1)->where('client_type', 'individual')->where('kyc_status', 'pending')->count() }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('clients.individual') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All Individual Clients →</a>
                </div>
            </div>

            <!-- Business Clients -->
            <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('clients.business') }}'">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Business Clients</h3>
                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-medium">Corporate</span>
                </div>
                <p class="text-gray-600 text-sm mb-4">Business entities and group organizations</p>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total:</span>
                        <span class="font-semibold">{{ $businessClients }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Verified:</span>
                        <span class="font-semibold text-green-600">{{ \App\Models\Client::where('organization_id', auth()->user()->organization_id ?? 1)->whereIn('client_type', ['business', 'group'])->where('kyc_status', 'verified')->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Pending:</span>
                        <span class="font-semibold text-yellow-600">{{ \App\Models\Client::where('organization_id', auth()->user()->organization_id ?? 1)->whereIn('client_type', ['business', 'group'])->where('kyc_status', 'pending')->count() }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('clients.business') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">View All Business Clients →</a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('clients.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <div>
                        <h4 class="font-medium text-gray-900">Add Individual Client</h4>
                        <p class="text-sm text-gray-600">Register new personal client</p>
                    </div>
                </a>

                <a href="{{ route('clients.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <div>
                        <h4 class="font-medium text-gray-900">Add Business Client</h4>
                        <p class="text-sm text-gray-600">Register new business entity</p>
                    </div>
                </a>

                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="font-medium text-gray-900">KYC Verification</h4>
                        <p class="text-sm text-gray-600">Review pending KYC</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-shell>

