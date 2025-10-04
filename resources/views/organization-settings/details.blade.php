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
                            <p class="text-gray-900 font-medium">{{ $organization->name }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Organization Type</label>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $organization->type)) }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Registration Number</label>
                            <p class="text-gray-900">{{ $organization->registration_number }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">License Number</label>
                            <p class="text-gray-900">{{ $organization->license_number ?? 'Not Available' }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $organization->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16z{{ $organization->status === 'active' ? 'm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' : '' }}" clip-rule="evenodd"></path>
                                </svg>
                                {{ ucfirst($organization->status) }}
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
                            <p class="text-gray-900">{{ $organization->email }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Phone Number</label>
                            <p class="text-gray-900">{{ $organization->phone }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Address</label>
                            <p class="text-gray-900">{{ $organization->address }}<br>{{ $organization->city }}, {{ $organization->state }}<br>{{ $organization->country }}, {{ $organization->postal_code }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Authorized Capital</label>
                            <p class="text-gray-900">{{ $organization->authorized_capital ? number_format($organization->authorized_capital, 2) . ' TZS' : 'Not Available' }}</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <label class="text-sm font-medium text-gray-500 block mb-1">Incorporation Date</label>
                            <p class="text-gray-900">{{ $organization->incorporation_date ? $organization->incorporation_date->format('F d, Y') : 'Not Available' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                <p class="text-gray-700 leading-relaxed">
                    {{ $organization->description ?? "Organization information and details for {$organization->name}. This organization operates as a " . str_replace('_', ' ', $organization->type) . " providing financial services." }}
                </p>
            </div>
            
            <!-- Main Accounts Section -->
            @if($mainAccounts->count() > 0)
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Main Accounts</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Account Name</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Account Number</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Branch</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($mainAccounts as $account)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900 font-medium">{{ $account->name }}</td>
                                    <td class="px-4 py-3 text-gray-900 font-mono text-xs">{{ $account->account_number }}</td>
                                    <td class="px-4 py-3 text-gray-900">
                                        @if($hqBranch)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $hqBranch->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-500">No Branch</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-900">{{ $account->formatted_balance }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-shell>
