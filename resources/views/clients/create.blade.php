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
        <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                        <input type="number" id="dependents" name="dependents" min="0" value="{{ old('dependents') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="0">
                    </div>

                    @include('clients.partials.crb-individual-fields', ['meta' => []])
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
                        <label for="business_description" class="block text-sm font-medium text-gray-700 mb-2">Industry Sector / Business Description</label>
                        <textarea id="business_description" name="business_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Describe the industry sector or nature of the business">{{ old('business_description') }}</textarea>
                    </div>

                    @include('clients.partials.crb-company-fields', ['meta' => []])
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
                        <label for="physical_address" class="block text-sm font-medium text-gray-700 mb-2">Main Address *</label>
                        <p class="text-xs text-gray-500 mb-2">Used as Main address on the CRB report.</p>
                        <textarea id="physical_address" name="physical_address" rows="2" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('physical_address') ? 'border-red-500' : '' }}"
                                  placeholder="Enter complete physical address">{{ old('physical_address') }}</textarea>
                        @error('physical_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Street -->
                    <div>
                        <label for="street" class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                        <input type="text" id="street" name="street" value="{{ old('street') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Street name">
                    </div>

                    <!-- Number of Building -->
                    <div>
                        <label for="number_of_building" class="block text-sm font-medium text-gray-700 mb-2">Number of Building</label>
                        <input type="text" id="number_of_building" name="number_of_building" value="{{ old('number_of_building') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Building number">
                    </div>

                    <!-- City -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City / District *</label>
                        <input type="text" id="city" name="city" required value="{{ old('city') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 {{ $errors->has('city') ? 'border-red-500' : '' }}"
                               placeholder="Enter city or district">
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
                        <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter postal code">
                    </div>

                    <!-- Tax Identification Number -->
                    <div>
                        <label for="tax_identification_number" class="block text-sm font-medium text-gray-700 mb-2">Tax Identification Number</label>
                        <input type="text" id="tax_identification_number" name="tax_identification_number" value="{{ old('tax_identification_number') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="TIN">
                    </div>

                    <!-- Web Page -->
                    <div>
                        <label for="web_page" class="block text-sm font-medium text-gray-700 mb-2">Web Page</label>
                        <input type="url" id="web_page" name="web_page" value="{{ old('web_page') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="https://example.com">
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Monthly Income -->
                    <div id="monthly_income_field">
                        <label for="monthly_income" class="block text-sm font-medium text-gray-700 mb-2">Monthly Income (TZS)</label>
                        <p class="text-xs text-gray-500 mb-2">Maps to Income Available on the CRB Individual report.</p>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">TZS</span>
                            </div>
                            <input type="number" id="monthly_income" name="monthly_income" step="0.01" min="0" value="{{ old('monthly_income') }}"
                                   class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <!-- Monthly Expenses -->
                    <div id="monthly_expenses_field" style="display: none;">
                        <label for="monthly_expenses" class="block text-sm font-medium text-gray-700 mb-2">Monthly Expenses (TZS)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">TZS</span>
                            </div>
                            <input type="number" id="monthly_expenses" name="monthly_expenses" step="0.01" min="0" value="{{ old('monthly_expenses') }}"
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
                    <div id="income_source_field">
                        <label for="income_source" class="block text-sm font-medium text-gray-700 mb-2">Income Source</label>
                        <input type="text" id="income_source" name="income_source" value="{{ old('income_source') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Employment, business, etc.">
                    </div>

                    <!-- Occupation -->
                    <div id="occupation_field">
                        <label for="occupation" class="block text-sm font-medium text-gray-700 mb-2">Employment / Occupation</label>
                        <input type="text" id="occupation" name="occupation" value="{{ old('occupation') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter occupation">
                    </div>

                    <!-- Employer Name -->
                    <div id="employer_name_field">
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

            <!-- KYC Documents -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">KYC Documents</h3>
                <p class="text-sm text-gray-600 mb-4">Upload required KYC documents for client verification. Supported formats: PDF, JPG, PNG, DOC, DOCX (Max 10MB per file)</p>
                
                <div id="kyc_documents_container" class="space-y-4">
                    <!-- Document Upload Fields -->
                    <div class="kyc-document-item border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Document Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Document Type *</label>
                                <select name="kyc_document_types[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                                    <option value="">Select document type</option>
                                    <option value="national_id">National ID</option>
                                    <option value="passport">Passport</option>
                                    <option value="driving_license">Driving License</option>
                                    <option value="birth_certificate">Birth Certificate</option>
                                    <option value="utility_bill">Utility Bill</option>
                                    <option value="bank_statement">Bank Statement</option>
                                    <option value="business_registration">Business Registration</option>
                                    <option value="tax_certificate">Tax Certificate</option>
                                    <option value="proof_of_address">Proof of Address</option>
                                    <option value="employment_letter">Employment Letter</option>
                                    <option value="salary_slip">Salary Slip</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <!-- Document File -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Document File *</label>
                                <input type="file" name="kyc_documents[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" 
                                       required>
                            </div>
                            
                            <!-- Document Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <input type="text" name="kyc_document_descriptions[]" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                       placeholder="Optional description">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Add More Documents Button -->
                <div class="mt-4">
                    <button type="button" id="add_kyc_document" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors text-sm">
                        + Add Another Document
                    </button>
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

            <!-- Record created by (default: current user) -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between border-t border-gray-200 pt-4 mt-4">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium text-gray-700">Record created by:</span>
                        {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                        <span class="text-gray-500">({{ auth()->user()->email }})</span>
                    </p>
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
        function setSectionFieldsDisabled(section, disabled) {
            if (!section) {
                return;
            }

            section.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = disabled;
            });
        }

        function setContainerFieldsDisabled(container, disabled) {
            if (!container) {
                return;
            }

            container.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = disabled;
            });
        }

        function toggleClientTypeFields() {
            const clientType = document.getElementById('client_type').value;
            const individualFields = document.getElementById('individual_fields');
            const businessFields = document.getElementById('business_fields');
            const annualTurnoverField = document.getElementById('annual_turnover_field');
            const monthlyExpensesField = document.getElementById('monthly_expenses_field');
            const individualFinancialFields = ['occupation_field', 'employer_name_field', 'income_source_field', 'monthly_income_field'];

            individualFields.style.display = 'none';
            businessFields.style.display = 'none';
            annualTurnoverField.style.display = 'none';
            monthlyExpensesField.style.display = 'none';
            individualFinancialFields.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.display = 'none';
                    setContainerFieldsDisabled(el, true);
                }
            });

            setSectionFieldsDisabled(individualFields, true);
            setSectionFieldsDisabled(businessFields, true);
            setContainerFieldsDisabled(monthlyExpensesField, true);
            setContainerFieldsDisabled(annualTurnoverField, true);

            if (clientType === 'individual') {
                individualFields.style.display = 'block';
                monthlyExpensesField.style.display = 'block';
                setSectionFieldsDisabled(individualFields, false);
                setContainerFieldsDisabled(monthlyExpensesField, false);
                individualFinancialFields.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.style.display = 'block';
                        setContainerFieldsDisabled(el, false);
                    }
                });
            } else if (clientType === 'business' || clientType === 'group') {
                businessFields.style.display = 'block';
                setSectionFieldsDisabled(businessFields, false);
                if (clientType === 'business') {
                    annualTurnoverField.style.display = 'block';
                    setContainerFieldsDisabled(annualTurnoverField, false);
                }
            }
        }

        document.getElementById('client_type').addEventListener('change', toggleClientTypeFields);
        toggleClientTypeFields();

        // Add more KYC document fields
        document.getElementById('add_kyc_document').addEventListener('click', function() {
            const container = document.getElementById('kyc_documents_container');
            const newDocument = document.createElement('div');
            newDocument.className = 'kyc-document-item border border-gray-200 rounded-lg p-4 bg-gray-50';
            newDocument.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document Type *</label>
                        <select name="kyc_document_types[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                            <option value="">Select document type</option>
                            <option value="national_id">National ID</option>
                            <option value="passport">Passport</option>
                            <option value="driving_license">Driving License</option>
                            <option value="birth_certificate">Birth Certificate</option>
                            <option value="utility_bill">Utility Bill</option>
                            <option value="bank_statement">Bank Statement</option>
                            <option value="business_registration">Business Registration</option>
                            <option value="tax_certificate">Tax Certificate</option>
                            <option value="proof_of_address">Proof of Address</option>
                            <option value="employment_letter">Employment Letter</option>
                            <option value="salary_slip">Salary Slip</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document File *</label>
                        <input type="file" name="kyc_documents[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" 
                               required>
                    </div>
                    <div class="flex items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <input type="text" name="kyc_document_descriptions[]" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   placeholder="Optional description">
                        </div>
                        <button type="button" class="ml-2 px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors remove-document" title="Remove this document">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newDocument);
            
            // Add remove functionality
            newDocument.querySelector('.remove-document').addEventListener('click', function() {
                newDocument.remove();
            });
        });

        // Add remove functionality to initial document (if more than one)
        document.querySelectorAll('.remove-document').forEach(button => {
            button.addEventListener('click', function() {
                const container = document.getElementById('kyc_documents_container');
                if (container.children.length > 1) {
                    this.closest('.kyc-document-item').remove();
                } else {
                    alert('You must have at least one document field.');
                }
            });
        });
    </script>
</x-app-shell>

