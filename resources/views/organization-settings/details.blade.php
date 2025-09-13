<x-app-shell title="Organization Details" header="Organization Details">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('organization-settings.index') }}" class="text-green-600 hover:text-green-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">Organization Details</h1>
                    </div>
                    <p class="text-gray-600">View your organization information and settings</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('organization-settings.edit') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Details
                    </a>
                </div>
            </div>
        </div>

        <!-- Organization Details Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Organization Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Organization Information</h3>
                    <div class="space-y-4">
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Organization Name</label>
                            <p class="text-gray-900 font-medium">Default Organization</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Organization Type</label>
                            <p class="text-gray-900">Microfinance Bank</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Registration Number</label>
                            <p class="text-gray-900">ORG001</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">License Number</label>
                            <p class="text-gray-900">LIC001</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Contact Information</h3>
                    <div class="space-y-4">
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Email Address</label>
                            <p class="text-gray-900">info@defaultorg.com</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Phone Number</label>
                            <p class="text-gray-900">+1234567890</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Address</label>
                            <p class="text-gray-900">123 Main Street<br>Sample City, Sample State<br>Sample Country, 12345</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Authorized Capital</label>
                            <p class="text-gray-900">$1,000,000.00</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Incorporation Date</label>
                            <p class="text-gray-900">{{ now()->subYears(2)->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                <p class="text-gray-700 leading-relaxed">
                    Default organization for system administration. This organization serves as the primary entity for managing users and system operations within the microfinance platform.
                </p>
            </div>
        </div>
    </div>
</x-app-shell>
