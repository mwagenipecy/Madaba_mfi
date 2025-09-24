<x-app-shell title="Add New Client" header="Add New Client">
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Add New Client</h1>
                    <p class="text-gray-600 mt-1">Register a new client with comprehensive KYC information</p>
                </div>
                <a href="{{ route('clients.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Back to Clients
                </a>
            </div>
        </div>

        <!-- Client Registration Form -->
        <form action="{{ route('clients.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Display validation errors -->
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Please correct the following errors:
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Client Type -->
                    <div>
                        <label for="client_type" class="block text-sm font-medium text-gray-700 mb-2">Client Type *</label>
                        <select id="client_type" name="client_type" required onchange="toggleClientTypeFields()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('client_type') ? 'border-red-500' : '' }}">
                            <option value="">Select client type</option>
                            <option value="individual" {{ old('client_type') == 'individual' ? 'selected' : '' }}>Individual Client</option>
                            <option value="business" {{ old('client_type') == 'business' ? 'selected' : '' }}>Business Client</option>
                            <option value="group" {{ old('client_type') == 'group' ? 'selected' : '' }}>Group Client</option>
                        </select>
                        @error('client_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Organization (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Organization</label>
                        <div class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900">
                            {{ $userOrganization->name }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Client will be created under your organization</p>
                    </div>

                    <!-- Branch -->
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                        <select id="branch_id" name="branch_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('branch_id') ? 'border-red-500' : '' }}">
                            <option value="">Select branch (optional)</option>
                            @forelse($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }} - {{ $branch->city }}</option>
                            @empty
                                <option value="" disabled>No branches available for your organization</option>
                            @endforelse
                        </select>
                        @error('branch_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if($branches->isEmpty())
                            <p class="mt-1 text-xs text-amber-600">No branches are set up for your organization yet</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Individual Client Information -->
            <div id="individual_fields" class="bg-white rounded-lg shadow-sm p-6" style="display: none;">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Individual Client Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('first_name') ? 'border-red-500' : '' }}"
                               placeholder="Enter first name">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Middle Name -->
                    <div>
                        <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('middle_name') ? 'border-red-500' : '' }}"
                               placeholder="Enter middle name">
                        @error('middle_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('last_name') ? 'border-red-500' : '' }}"
                               placeholder="Enter last name">
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('date_of_birth') ? 'border-red-500' : '' }}">
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                        <select id="gender" name="gender"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('gender') ? 'border-red-500' : '' }}">
                            <option value="">Select gender</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Marital Status -->
                    <div>
                        <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                        <select id="marital_status" name="marital_status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select marital status</option>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="divorced">Divorced</option>
                            <option value="widowed">Widowed</option>
                        </select>
                    </div>

                    <!-- National ID -->
                    <div>
                        <label for="national_id" class="block text-sm font-medium text-gray-700 mb-2">National ID</label>
                        <input type="text" id="national_id" name="national_id"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter national ID number">
                    </div>

                    <!-- Passport Number -->
                    <div>
                        <label for="passport_number" class="block text-sm font-medium text-gray-700 mb-2">Passport Number</label>
                        <input type="text" id="passport_number" name="passport_number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter passport number">
                    </div>

                    <!-- Dependents -->
                    <div>
                        <label for="dependents" class="block text-sm font-medium text-gray-700 mb-2">Number of Dependents</label>
                        <input type="number" id="dependents" name="dependents" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="0">
                    </div>
                </div>
            </div>

            <!-- Business/Group Information -->
            <div id="business_fields" class="bg-white rounded-lg shadow-sm p-6" style="display: none;">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Business/Group Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Business Name -->
                    <div>
                        <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">Business/Group Name *</label>
                        <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('business_name') ? 'border-red-500' : '' }}"
                               placeholder="Enter business or group name">
                        @error('business_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Business Registration Number -->
                    <div>
                        <label for="business_registration_number" class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                        <input type="text" id="business_registration_number" name="business_registration_number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter business registration number">
                    </div>

                    <!-- Business Type -->
                    <div>
                        <label for="business_type" class="block text-sm font-medium text-gray-700 mb-2">Business Type *</label>
                        <select id="business_type" name="business_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('business_type') ? 'border-red-500' : '' }}">
                            <option value="">Select business type</option>
                            <option value="sole_proprietorship" {{ old('business_type') == 'sole_proprietorship' ? 'selected' : '' }}>Sole Proprietorship</option>
                            <option value="partnership" {{ old('business_type') == 'partnership' ? 'selected' : '' }}>Partnership</option>
                            <option value="corporation" {{ old('business_type') == 'corporation' ? 'selected' : '' }}>Corporation</option>
                            <option value="cooperative" {{ old('business_type') == 'cooperative' ? 'selected' : '' }}>Cooperative</option>
                            <option value="ngo" {{ old('business_type') == 'ngo' ? 'selected' : '' }}>NGO</option>
                            <option value="other" {{ old('business_type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('business_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Years in Business -->
                    <div>
                        <label for="years_in_business" class="block text-sm font-medium text-gray-700 mb-2">Years in Business</label>
                        <input type="number" id="years_in_business" name="years_in_business" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="0">
                    </div>

                    <!-- Business Description -->
                    <div class="md:col-span-2">
                        <label for="business_description" class="block text-sm font-medium text-gray-700 mb-2">Business Description</label>
                        <textarea id="business_description" name="business_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Describe the nature of the business"></textarea>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Phone Number -->
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Primary Phone *</label>
                        <input type="tel" id="phone_number" name="phone_number" required value="{{ old('phone_number') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('phone_number') ? 'border-red-500' : '' }}"
                               placeholder="+255 XXX XXX XXX">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Secondary Phone -->
                    <div>
                        <label for="secondary_phone" class="block text-sm font-medium text-gray-700 mb-2">Secondary Phone</label>
                        <input type="tel" id="secondary_phone" name="secondary_phone"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="+255 XXX XXX XXX">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="client@example.com">
                    </div>

                    <!-- Physical Address -->
                    <div class="md:col-span-2">
                        <label for="physical_address" class="block text-sm font-medium text-gray-700 mb-2">Physical Address *</label>
                        <textarea id="physical_address" name="physical_address" rows="2" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('physical_address') ? 'border-red-500' : '' }}"
                                  placeholder="Enter complete physical address">{{ old('physical_address') }}</textarea>
                        @error('physical_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                        <input type="text" id="city" name="city" required value="{{ old('city') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('city') ? 'border-red-500' : '' }}"
                               placeholder="Enter city">
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Region -->
                    <div>
                        <label for="region" class="block text-sm font-medium text-gray-700 mb-2">Region *</label>
                        <input type="text" id="region" name="region" required value="{{ old('region') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('region') ? 'border-red-500' : '' }}"
                               placeholder="Enter region">
                        @error('region')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                        <input type="text" id="country" name="country" value="Tanzania"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter postal code">
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Monthly Income -->
                    <div>
                        <label for="monthly_income" class="block text-sm font-medium text-gray-700 mb-2">Monthly Income (TZS)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">TZS</span>
                            </div>
                            <input type="number" id="monthly_income" name="monthly_income" step="0.01" min="0"
                                   class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <!-- Annual Turnover (for business) -->
                    <div id="annual_turnover_field" style="display: none;">
                        <label for="annual_turnover" class="block text-sm font-medium text-gray-700 mb-2">Annual Turnover (TZS)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">TZS</span>
                            </div>
                            <input type="number" id="annual_turnover" name="annual_turnover" step="0.01" min="0"
                                   class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <!-- Income Source -->
                    <div>
                        <label for="income_source" class="block text-sm font-medium text-gray-700 mb-2">Income Source</label>
                        <input type="text" id="income_source" name="income_source"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Employment, business, etc.">
                    </div>

                    <!-- Occupation -->
                    <div>
                        <label for="occupation" class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                        <input type="text" id="occupation" name="occupation"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter occupation">
                    </div>

                    <!-- Employer Name -->
                    <div>
                        <label for="employer_name" class="block text-sm font-medium text-gray-700 mb-2">Employer Name</label>
                        <input type="text" id="employer_name" name="employer_name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter employer name">
                    </div>

                    <!-- Employment Address -->
                    <div>
                        <label for="employment_address" class="block text-sm font-medium text-gray-700 mb-2">Employment Address</label>
                        <textarea id="employment_address" name="employment_address" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Enter employment address"></textarea>
                    </div>

                    <!-- Bank Name -->
                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter bank name">
                    </div>

                    <!-- Bank Account Number -->
                    <div>
                        <label for="bank_account_number" class="block text-sm font-medium text-gray-700 mb-2">Bank Account Number</label>
                        <input type="text" id="bank_account_number" name="bank_account_number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter account number">
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Emergency Contact</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Emergency Contact Name -->
                    <div>
                        <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Name</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter emergency contact name">
                    </div>

                    <!-- Emergency Contact Phone -->
                    <div>
                        <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Phone</label>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="+255 XXX XXX XXX">
                    </div>

                    <!-- Emergency Contact Relationship -->
                    <div>
                        <label for="emergency_contact_relationship" class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                        <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Spouse, parent, sibling, etc.">
                    </div>
                </div>
            </div>

            <!-- Additional Notes -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea id="notes" name="notes" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                              placeholder="Any additional information about the client"></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('clients.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        Create Client
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toggleClientTypeFields() {
            const clientType = document.getElementById('client_type').value;
            const individualFields = document.getElementById('individual_fields');
            const businessFields = document.getElementById('business_fields');
            const annualTurnoverField = document.getElementById('annual_turnover_field');

            // Hide all fields first
            individualFields.style.display = 'none';
            businessFields.style.display = 'none';
            annualTurnoverField.style.display = 'none';

            // Show relevant fields based on client type
            if (clientType === 'individual') {
                individualFields.style.display = 'block';
            } else if (clientType === 'business' || clientType === 'group') {
                businessFields.style.display = 'block';
                if (clientType === 'business') {
                    annualTurnoverField.style.display = 'block';
                }
            }
        }
    </script>
</x-app-shell>

