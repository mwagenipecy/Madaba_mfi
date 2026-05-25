<x-app-shell title="Edit Client - {{ $client->display_name }}" header="Edit Client">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Edit Client - {{ $client->display_name }}</h1>
                        <a href="{{ route('clients.show', $client) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Back to Client Details
                        </a>
                    </div>

                    <form method="POST" action="{{ route('clients.update', $client) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')
                        @php
                            $meta = is_array($client->metadata) ? $client->metadata : [];
                        @endphp
                        
                        <!-- Hidden fields for required data -->
                        <input type="hidden" name="organization_id" value="{{ $client->organization_id }}">

                        <!-- Client Type -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Client Type</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-center">
                                    <input type="radio" name="client_type" value="individual" 
                                           {{ old('client_type', $client->client_type) == 'individual' ? 'checked' : '' }}
                                           class="text-green-600 focus:ring-green-500">
                                    <span class="ml-2 text-sm text-gray-700">Individual</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="client_type" value="business" 
                                           {{ old('client_type', $client->client_type) == 'business' ? 'checked' : '' }}
                                           class="text-green-600 focus:ring-green-500">
                                    <span class="ml-2 text-sm text-gray-700">Business</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="client_type" value="group" 
                                           {{ old('client_type', $client->client_type) == 'group' ? 'checked' : '' }}
                                           class="text-green-600 focus:ring-green-500">
                                    <span class="ml-2 text-sm text-gray-700">Group</span>
                                </label>
                            </div>
                            @error('client_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Personal Information -->
                        <div class="bg-gray-50 rounded-lg p-6" id="individual-info" style="display: {{ old('client_type', $client->client_type) === 'individual' ? 'block' : 'none' }};">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Individual Client Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                    <input type="text" name="first_name" id="first_name" 
                                           value="{{ old('first_name', $client->first_name) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('first_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                                    <input type="text" name="middle_name" id="middle_name" 
                                           value="{{ old('middle_name', $client->middle_name) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('middle_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" 
                                           value="{{ old('last_name', $client->last_name) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('last_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" 
                                           value="{{ old('date_of_birth', $client->date_of_birth?->format('Y-m-d')) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('date_of_birth')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                    <select name="gender" id="gender" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $client->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $client->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $client->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                                    <select name="marital_status" id="marital_status" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="">Select Status</option>
                                        <option value="single" {{ old('marital_status', $client->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ old('marital_status', $client->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                        <option value="divorced" {{ old('marital_status', $client->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="widowed" {{ old('marital_status', $client->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                    </select>
                                    @error('marital_status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="national_id" class="block text-sm font-medium text-gray-700 mb-2">National ID</label>
                                    <input type="text" name="national_id" id="national_id"
                                           value="{{ old('national_id', $client->national_id) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('national_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="passport_number" class="block text-sm font-medium text-gray-700 mb-2">Passport Number</label>
                                    <input type="text" name="passport_number" id="passport_number"
                                           value="{{ old('passport_number', $client->passport_number) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('passport_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="dependents" class="block text-sm font-medium text-gray-700 mb-2">Number of Dependents</label>
                                    <input type="number" name="dependents" id="dependents" min="0"
                                           value="{{ old('dependents', $client->dependents) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>

                                @include('clients.partials.crb-individual-fields', ['meta' => $meta, 'client' => $client])
                            </div>
                        </div>

                        <!-- Business Information (for business/group clients) -->
                        <div class="bg-gray-50 rounded-lg p-6" id="business-info" style="display: {{ in_array(old('client_type', $client->client_type), ['business', 'group']) ? 'block' : 'none' }}">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Business Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">Business Name</label>
                                    <input type="text" name="business_name" id="business_name" 
                                           value="{{ old('business_name', $client->business_name) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('business_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="business_registration_number" class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                                    <input type="text" name="business_registration_number" id="business_registration_number" 
                                           value="{{ old('business_registration_number', $client->business_registration_number) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('business_registration_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="business_type" class="block text-sm font-medium text-gray-700 mb-2">Business Type</label>
                                    <input type="text" name="business_type" id="business_type" 
                                           value="{{ old('business_type', $client->business_type) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('business_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="years_in_business" class="block text-sm font-medium text-gray-700 mb-2">Years in Business</label>
                                    <input type="number" name="years_in_business" id="years_in_business" 
                                           value="{{ old('years_in_business', $client->years_in_business) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('years_in_business')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="business_description" class="block text-sm font-medium text-gray-700 mb-2">Industry Sector / Business Description</label>
                                    <textarea name="business_description" id="business_description" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('business_description', $client->business_description) }}</textarea>
                                    @error('business_description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                @include('clients.partials.crb-company-fields', ['meta' => $meta])
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Primary Phone</label>
                                    <input type="tel" name="phone_number" id="phone_number" 
                                           value="{{ old('phone_number', $client->phone_number) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('phone_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="secondary_phone" class="block text-sm font-medium text-gray-700 mb-2">Secondary Phone</label>
                                    <input type="tel" name="secondary_phone" id="secondary_phone" 
                                           value="{{ old('secondary_phone', $client->secondary_phone) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('secondary_phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" name="email" id="email" 
                                           value="{{ old('email', $client->email) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="physical_address" class="block text-sm font-medium text-gray-700 mb-2">Main Address</label>
                                    <textarea name="physical_address" id="physical_address" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('physical_address', $client->physical_address) }}</textarea>
                                    @error('physical_address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="street" class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                                    <input type="text" name="street" id="street"
                                           value="{{ old('street', $meta['street'] ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           placeholder="Street name">
                                </div>

                                <div>
                                    <label for="number_of_building" class="block text-sm font-medium text-gray-700 mb-2">Number of Building</label>
                                    <input type="text" name="number_of_building" id="number_of_building"
                                           value="{{ old('number_of_building', $meta['number_of_building'] ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           placeholder="Building number">
                                </div>

                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City / District</label>
                                    <input type="text" name="city" id="city" 
                                           value="{{ old('city', $client->city) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('city')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="region" class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                    <input type="text" name="region" id="region" 
                                           value="{{ old('region', $client->region) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('region')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                    <input type="text" name="country" id="country"
                                           value="{{ old('country', $client->country) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                                    <input type="text" name="postal_code" id="postal_code"
                                           value="{{ old('postal_code', $client->postal_code) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div>
                                    <label for="tax_identification_number" class="block text-sm font-medium text-gray-700 mb-2">Tax Identification Number</label>
                                    <input type="text" name="tax_identification_number" id="tax_identification_number"
                                           value="{{ old('tax_identification_number', $meta['tax_identification_number'] ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           placeholder="TIN">
                                </div>

                                <div>
                                    <label for="web_page" class="block text-sm font-medium text-gray-700 mb-2">Web Page</label>
                                    <input type="url" name="web_page" id="web_page"
                                           value="{{ old('web_page', $meta['web_page'] ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           placeholder="https://example.com">
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Financial Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="monthly_income" class="block text-sm font-medium text-gray-700 mb-2">Monthly Income</label>
                                    <input type="number" step="0.01" name="monthly_income" id="monthly_income" 
                                           value="{{ old('monthly_income', $client->monthly_income) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('monthly_income')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div id="monthly_expenses_field" style="display: {{ old('client_type', $client->client_type) === 'individual' ? 'block' : 'none' }};">
                                    <label for="monthly_expenses" class="block text-sm font-medium text-gray-700 mb-2">Monthly Expenses</label>
                                    <input type="number" step="0.01" name="monthly_expenses" id="monthly_expenses"
                                           value="{{ old('monthly_expenses', $meta['monthly_expenses'] ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div>
                                    <label for="income_source" class="block text-sm font-medium text-gray-700 mb-2">Income Source</label>
                                    <input type="text" name="income_source" id="income_source" 
                                           value="{{ old('income_source', $client->income_source) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('income_source')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="employer_name" class="block text-sm font-medium text-gray-700 mb-2">Employer Name</label>
                                    <input type="text" name="employer_name" id="employer_name" 
                                           value="{{ old('employer_name', $client->employer_name) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('employer_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                                    <input type="text" name="bank_name" id="bank_name" 
                                           value="{{ old('bank_name', $client->bank_name) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('bank_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="bank_account_number" class="block text-sm font-medium text-gray-700 mb-2">Bank Account Number</label>
                                    <input type="text" name="bank_account_number" id="bank_account_number" 
                                           value="{{ old('bank_account_number', $client->bank_account_number) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('bank_account_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Emergency Contact</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-2">Contact Name</label>
                                    <input type="text" name="emergency_contact_name" id="emergency_contact_name" 
                                           value="{{ old('emergency_contact_name', $client->emergency_contact_name) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('emergency_contact_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                    <input type="tel" name="emergency_contact_phone" id="emergency_contact_phone" 
                                           value="{{ old('emergency_contact_phone', $client->emergency_contact_phone) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('emergency_contact_phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="emergency_contact_relationship" class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                                    <input type="text" name="emergency_contact_relationship" id="emergency_contact_relationship" 
                                           value="{{ old('emergency_contact_relationship', $client->emergency_contact_relationship) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @error('emergency_contact_relationship')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status and Notes -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Status and Notes</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select name="status" id="status" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="active" {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $client->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="disabled" {{ old('status', $client->status) == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                        <option value="suspended" {{ old('status', $client->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="blacklisted" {{ old('status', $client->status) == 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="kyc_status" class="block text-sm font-medium text-gray-700 mb-2">KYC Status</label>
                                    <select name="kyc_status" id="kyc_status" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="pending" {{ old('kyc_status', $client->kyc_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="verified" {{ old('kyc_status', $client->kyc_status) == 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="rejected" {{ old('kyc_status', $client->kyc_status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="expired" {{ old('kyc_status', $client->kyc_status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                    </select>
                                    @error('kyc_status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                    <textarea name="notes" id="notes" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('notes', $client->notes) }}</textarea>
                                    @error('notes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- KYC Documents -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">KYC Documents</h2>
                            <p class="text-sm text-gray-600 mb-4">Upload additional KYC documents or manage existing ones. Supported formats: PDF, JPG, PNG, DOC, DOCX (Max 10MB per file)</p>
                            
                            <!-- Existing Documents -->
                            @if($client->kyc_documents && count($client->kyc_documents) > 0)
                            <div class="mb-6">
                                <h3 class="text-md font-medium text-gray-900 mb-3">Existing Documents</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($client->kyc_documents as $index => $document)
                                        <div class="border border-gray-200 rounded-lg p-3 bg-white">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-medium text-gray-900 text-sm capitalize truncate">{{ str_replace('_', ' ', $document['type'] ?? 'Document') }}</h4>
                                                    @if(isset($document['description']) && $document['description'])
                                                        <p class="text-xs text-gray-600 mt-1 truncate">{{ $document['description'] }}</p>
                                                    @endif
                                                </div>
                                                <span class="px-2 py-1 rounded-full text-xs font-medium ml-2 flex-shrink-0
                                                    {{ ($document['status'] ?? 'pending') === 'approved' ? 'bg-green-100 text-green-800' : 
                                                       (($document['status'] ?? 'pending') === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($document['status'] ?? 'pending') }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500 mb-2">
                                                <div class="truncate">{{ $document['name'] ?? 'Unknown' }}</div>
                                                @if(isset($document['size']))
                                                    <div>{{ number_format($document['size'] / 1024, 2) }} KB</div>
                                                @endif
                                            </div>
                                            <div class="flex space-x-2">
                                                @php
                                                    $fileUrl = asset('storage/' . $document['path']);
                                                @endphp
                                                <a href="{{ $fileUrl }}" target="_blank" 
                                                   class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center px-2 py-1 rounded text-xs font-medium transition-colors">
                                                    View
                                                </a>
                                                <a href="{{ $fileUrl }}" download="{{ $document['name'] ?? 'document' }}" 
                                                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-2 py-1 rounded text-xs font-medium transition-colors">
                                                    Download
                                                </a>
                                                <button type="button" onclick="removeDocument({{ $index }})" 
                                                        class="bg-red-100 hover:bg-red-200 text-red-700 px-2 py-1 rounded text-xs font-medium transition-colors">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <!-- New Document Upload Fields -->
                            <div id="kyc_documents_container" class="space-y-4">
                                <div class="kyc-document-item border border-gray-200 rounded-lg p-4 bg-white">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                                            <select name="kyc_document_types[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
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
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Document File</label>
                                            <input type="file" name="kyc_documents[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                                        </div>
                                        <div class="flex items-end">
                                            <div class="flex-1">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                                <input type="text" name="kyc_document_descriptions[]" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                                       placeholder="Optional">
                                            </div>
                                            <button type="button" class="ml-2 px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors remove-document" title="Remove this field">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden field to track removed documents -->
                            <input type="hidden" name="removed_documents" id="removed_documents" value="">
                            
                            <!-- Add More Documents Button -->
                            <div class="mt-4">
                                <button type="button" id="add_kyc_document" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors text-sm">
                                    + Add Another Document
                                </button>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('clients.show', $client) }}" 
                               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                Update Client
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show/hide sections based on client type
        document.addEventListener('DOMContentLoaded', function() {
            const clientTypeRadios = document.querySelectorAll('input[name="client_type"]');
            const businessInfo = document.getElementById('business-info');
            const individualInfo = document.getElementById('individual-info');
            const monthlyExpensesField = document.getElementById('monthly_expenses_field');
            
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

            function toggleClientSections() {
                const selectedType = document.querySelector('input[name="client_type"]:checked');
                const type = selectedType ? selectedType.value : 'individual';

                if (individualInfo) {
                    individualInfo.style.display = type === 'individual' ? 'block' : 'none';
                    setSectionFieldsDisabled(individualInfo, type !== 'individual');
                }

                if (businessInfo) {
                    businessInfo.style.display = ['business', 'group'].includes(type) ? 'block' : 'none';
                    setSectionFieldsDisabled(businessInfo, !['business', 'group'].includes(type));
                }

                if (monthlyExpensesField) {
                    monthlyExpensesField.style.display = type === 'individual' ? 'block' : 'none';
                    setContainerFieldsDisabled(monthlyExpensesField, type !== 'individual');
                }
            }
            
            clientTypeRadios.forEach(radio => {
                radio.addEventListener('change', toggleClientSections);
            });
            
            toggleClientSections();
        });

        // Track removed documents
        let removedDocuments = [];
        
        function removeDocument(index) {
            if (confirm('Are you sure you want to remove this document? This action cannot be undone.')) {
                removedDocuments.push(index);
                document.getElementById('removed_documents').value = JSON.stringify(removedDocuments);
                // Hide the document card
                event.target.closest('.border').style.display = 'none';
            }
        }

        // Add more KYC document fields
        document.getElementById('add_kyc_document').addEventListener('click', function() {
            const container = document.getElementById('kyc_documents_container');
            const newDocument = document.createElement('div');
            newDocument.className = 'kyc-document-item border border-gray-200 rounded-lg p-4 bg-white';
            newDocument.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                        <select name="kyc_document_types[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document File</label>
                        <input type="file" name="kyc_documents[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    </div>
                    <div class="flex items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <input type="text" name="kyc_document_descriptions[]" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   placeholder="Optional">
                        </div>
                        <button type="button" class="ml-2 px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors remove-document" title="Remove this field">
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

        // Add remove functionality to initial document
        document.querySelectorAll('.remove-document').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.kyc-document-item').remove();
            });
        });
    </script>
</x-app-shell>
