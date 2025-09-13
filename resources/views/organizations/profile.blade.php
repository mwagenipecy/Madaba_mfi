<x-app-shell title="Organization Profile" header="Organization Profile">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('organizations.index') }}" class="text-green-600 hover:text-green-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">Organization Profile</h1>
                    </div>
                    <p class="text-gray-600">View organization details and settings</p>
                </div>
            </div>
        </div>

        <!-- Organization Profile Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Organization Details -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization Details</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Name</label>
                            <p class="text-gray-900">Default Organization</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Type</label>
                            <p class="text-gray-900">Microfinance Bank</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Registration Number</label>
                            <p class="text-gray-900">ORG001</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-gray-900">info@defaultorg.com</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Phone</label>
                            <p class="text-gray-900">+1234567890</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Address</label>
                            <p class="text-gray-900">123 Main Street, Sample City, Sample State, Sample Country</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>
