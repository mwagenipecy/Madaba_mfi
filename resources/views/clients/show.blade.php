<x-app-shell title="Client Details - {{ $client->display_name }}" header="Client Details">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $client->display_name }}</h1>
                        <p class="text-gray-600">{{ $client->client_number }}</p>
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $client->client_type_badge_color }}">
                                {{ ucfirst($client->client_type) }}
                            </span>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $client->status_badge_color }}">
                                {{ ucfirst($client->status) }}
                            </span>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $client->kyc_status_badge_color }}">
                                KYC: {{ ucfirst($client->kyc_status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('clients.edit', $client) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Edit Client
                    </a>
                    <a href="{{ route('clients.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Back to Clients
                    </a>
                </div>
            </div>
        </div>

        <!-- Client Information Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                <div class="space-y-3">
                    @if($client->client_type === 'individual')
                        <div class="flex justify-between">
                            <span class="text-gray-600">Full Name:</span>
                            <span class="font-medium">{{ $client->full_name }}</span>
                        </div>
                        @if($client->date_of_birth)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date of Birth:</span>
                                <span class="font-medium">{{ $client->date_of_birth->format('M d, Y') }} ({{ $client->age }} years)</span>
                            </div>
                        @endif
                        @if($client->gender)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Gender:</span>
                                <span class="font-medium">{{ ucfirst($client->gender) }}</span>
                            </div>
                        @endif
                        @if($client->marital_status)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Marital Status:</span>
                                <span class="font-medium">{{ ucfirst($client->marital_status) }}</span>
                            </div>
                        @endif
                        @if($client->dependents)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Dependents:</span>
                                <span class="font-medium">{{ $client->dependents }}</span>
                            </div>
                        @endif
                    @else
                        <div class="flex justify-between">
                            <span class="text-gray-600">Business Name:</span>
                            <span class="font-medium">{{ $client->business_name }}</span>
                        </div>
                        @if($client->business_type)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Business Type:</span>
                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $client->business_type)) }}</span>
                            </div>
                        @endif
                        @if($client->business_registration_number)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Registration Number:</span>
                                <span class="font-medium">{{ $client->business_registration_number }}</span>
                            </div>
                        @endif
                        @if($client->years_in_business)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Years in Business:</span>
                                <span class="font-medium">{{ $client->years_in_business }}</span>
                            </div>
                        @endif
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-600">Organization:</span>
                        <span class="font-medium">{{ $client->organization->name }}</span>
                    </div>
                    @if($client->branch)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Branch:</span>
                            <span class="font-medium">{{ $client->branch->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                <div class="space-y-3">
                    @if($client->phone_number)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Primary Phone:</span>
                            <span class="font-medium">{{ $client->phone_number }}</span>
                        </div>
                    @endif
                    @if($client->secondary_phone)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Secondary Phone:</span>
                            <span class="font-medium">{{ $client->secondary_phone }}</span>
                        </div>
                    @endif
                    @if($client->email)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium">{{ $client->email }}</span>
                        </div>
                    @endif
                    @if($client->physical_address)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Address:</span>
                            <span class="font-medium text-right">{{ $client->physical_address }}</span>
                        </div>
                    @endif
                    @if($client->city)
                        <div class="flex justify-between">
                            <span class="text-gray-600">City:</span>
                            <span class="font-medium">{{ $client->city }}</span>
                        </div>
                    @endif
                    @if($client->region)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Region:</span>
                            <span class="font-medium">{{ $client->region }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Information</h3>
                <div class="space-y-3">
                    @if($client->monthly_income)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Monthly Income:</span>
                            <span class="font-medium">{{ $client->formatted_monthly_income }}</span>
                        </div>
                    @endif
                    @if($client->annual_turnover)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Annual Turnover:</span>
                            <span class="font-medium">{{ $client->formatted_annual_turnover }}</span>
                        </div>
                    @endif
                    @if($client->income_source)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Income Source:</span>
                            <span class="font-medium">{{ $client->income_source }}</span>
                        </div>
                    @endif
                    @if($client->occupation)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Occupation:</span>
                            <span class="font-medium">{{ $client->occupation }}</span>
                        </div>
                    @endif
                    @if($client->employer_name)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Employer:</span>
                            <span class="font-medium">{{ $client->employer_name }}</span>
                        </div>
                    @endif
                    @if($client->bank_name)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bank:</span>
                            <span class="font-medium">{{ $client->bank_name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- KYC Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">KYC Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">KYC Status:</span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $client->kyc_status_badge_color }}">
                            {{ ucfirst($client->kyc_status) }}
                        </span>
                    </div>
                    @if($client->kyc_verification_date)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Verified Date:</span>
                            <span class="font-medium">{{ $client->kyc_verification_date->format('M d, Y') }}</span>
                        </div>
                    @endif
                    @if($client->verifiedBy)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Verified By:</span>
                            <span class="font-medium">{{ $client->verifiedBy->first_name }} {{ $client->verifiedBy->last_name }}</span>
                        </div>
                    @endif
                    @if($client->national_id)
                        <div class="flex justify-between">
                            <span class="text-gray-600">National ID:</span>
                            <span class="font-medium">{{ $client->national_id }}</span>
                        </div>
                    @endif
                    @if($client->passport_number)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Passport:</span>
                            <span class="font-medium">{{ $client->passport_number }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Emergency Contact -->
            @if($client->emergency_contact_name)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Emergency Contact</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Name:</span>
                        <span class="font-medium">{{ $client->emergency_contact_name }}</span>
                    </div>
                    @if($client->emergency_contact_phone)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium">{{ $client->emergency_contact_phone }}</span>
                        </div>
                    @endif
                    @if($client->emergency_contact_relationship)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Relationship:</span>
                            <span class="font-medium">{{ $client->emergency_contact_relationship }}</span>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Additional Information -->
            @if($client->notes || $client->business_description)
            <div class="bg-white rounded-lg shadow-sm p-6 lg:col-span-2">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                <div class="space-y-3">
                    @if($client->business_description)
                        <div>
                            <span class="text-gray-600 block mb-1">Business Description:</span>
                            <p class="text-gray-900">{{ $client->business_description }}</p>
                        </div>
                    @endif
                    @if($client->notes)
                        <div>
                            <span class="text-gray-600 block mb-1">Notes:</span>
                            <p class="text-gray-900">{{ $client->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Loans Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Client Loans</h3>
                <a href="{{ route('loans.create') }}?client_id={{ $client->id }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Create New Loan
                </a>
            </div>
            
            @if($client->loans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Payment</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($client->loans as $loan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $loan->loan_number }}</div>
                                        <div class="text-sm text-gray-500">{{ $loan->application_date->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $loan->loanProduct->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $loan->interest_rate }}% for {{ $loan->loan_tenure_months }} months</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">TZS {{ number_format($loan->loan_amount, 2) }}</div>
                                        @if($loan->approved_amount && $loan->approved_amount != $loan->loan_amount)
                                            <div class="text-sm text-gray-500">Approved: TZS {{ number_format($loan->approved_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'under_review' => 'bg-blue-100 text-blue-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'disbursed' => 'bg-blue-100 text-blue-800',
                                                'active' => 'bg-green-100 text-green-800',
                                                'overdue' => 'bg-red-100 text-red-800',
                                                'completed' => 'bg-gray-100 text-gray-800',
                                                'written_off' => 'bg-red-100 text-red-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$loan->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">TZS {{ number_format($loan->outstanding_balance ?? 0, 2) }}</div>
                                        @if($loan->overdue_amount && $loan->overdue_amount > 0)
                                            <div class="text-sm text-red-600">Overdue: TZS {{ number_format($loan->overdue_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($loan->first_payment_date)
                                            <div class="text-sm text-gray-900">{{ $loan->first_payment_date->format('M d, Y') }}</div>
                                            @if($loan->monthly_payment)
                                                <div class="text-sm text-gray-500">TZS {{ number_format($loan->monthly_payment, 2) }}</div>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-500">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('loans.show', $loan) }}" class="text-green-600 hover:text-green-900">
                                                View
                                            </a>
                                            <a href="{{ route('loans.edit', $loan) }}" class="text-blue-600 hover:text-blue-900">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No loans found</h3>
                    <p class="text-gray-500 mb-4">This client doesn't have any loans yet.</p>
                    <a href="{{ route('loans.create') }}?client_id={{ $client->id }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Create First Loan
                    </a>
                </div>
            @endif
        </div>

        <!-- Client Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Client Actions</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('clients.edit', $client) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Edit Client Information
                </a>
                
                @if($client->kyc_status === 'pending')
                    <form action="{{ route('clients.update-kyc-status', $client) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="kyc_status" value="verified">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Verify KYC
                        </button>
                    </form>
                @endif

                @if($client->status === 'active')
                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this client?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Delete Client
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-shell>

