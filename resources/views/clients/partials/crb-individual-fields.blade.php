@php
    $crb = fn (string $key, $default = '') => old($key, $meta[$key] ?? $default);
@endphp

<div class="md:col-span-2 lg:col-span-3 border-t border-gray-200 pt-6 mt-2">
    <h4 class="text-md font-semibold text-gray-800 mb-1">CRB Report Details</h4>
    <p class="text-xs text-gray-500 mb-4">These fields populate the Individual sheet on the CRB report.</p>
</div>

<div>
    <label for="birth_surname" class="block text-sm font-medium text-gray-700 mb-2">Birth Surname</label>
    <input type="text" id="birth_surname" name="birth_surname" value="{{ $crb('birth_surname') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Surname at birth">
</div>

<div>
    <label for="number_of_spouse" class="block text-sm font-medium text-gray-700 mb-2">Number of Spouse</label>
    <input type="number" id="number_of_spouse" name="number_of_spouse" min="0" value="{{ $crb('number_of_spouse') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="0">
</div>

<div>
    <label for="number_of_children" class="block text-sm font-medium text-gray-700 mb-2">Number of Children</label>
    <input type="number" id="number_of_children" name="number_of_children" min="0" value="{{ $crb('number_of_children') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="0">
</div>

<div>
    <label for="country_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Country of Birth</label>
    <input type="text" id="country_of_birth" name="country_of_birth" value="{{ $crb('country_of_birth') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Country of birth">
</div>

<div>
    <label for="fate_status" class="block text-sm font-medium text-gray-700 mb-2">Fate Status</label>
    <input type="text" id="fate_status" name="fate_status" value="{{ $crb('fate_status') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Fate status">
</div>

<div>
    <label for="social_status" class="block text-sm font-medium text-gray-700 mb-2">Social Status</label>
    <input type="text" id="social_status" name="social_status" value="{{ $crb('social_status') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Social status">
</div>

<div>
    <label for="residency" class="block text-sm font-medium text-gray-700 mb-2">Residency</label>
    <input type="text" id="residency" name="residency" value="{{ $crb('residency') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Country of residency">
</div>

<div>
    <label for="citizenship" class="block text-sm font-medium text-gray-700 mb-2">Citizenship</label>
    <input type="text" id="citizenship" name="citizenship" value="{{ $crb('citizenship') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Citizenship">
</div>

<div>
    <label for="nationality" class="block text-sm font-medium text-gray-700 mb-2">Nationality</label>
    <input type="text" id="nationality" name="nationality" value="{{ $crb('nationality') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Nationality">
</div>

<div>
    <label for="education" class="block text-sm font-medium text-gray-700 mb-2">Education</label>
    <input type="text" id="education" name="education" value="{{ $crb('education') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Education level">
</div>

<div>
    <label for="individual_business_name" class="block text-sm font-medium text-gray-700 mb-2">Business Name</label>
    <input type="text" id="individual_business_name" name="individual_business_name" value="{{ old('individual_business_name', isset($client) && $client->client_type === 'individual' ? $client->business_name : '') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Business name (if applicable)">
</div>

<div>
    <label for="passport_issuer_country" class="block text-sm font-medium text-gray-700 mb-2">Passport Issuer Country</label>
    <input type="text" id="passport_issuer_country" name="passport_issuer_country" value="{{ $crb('passport_issuer_country') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Passport issuing country">
</div>

<div>
    <label for="driving_license_number" class="block text-sm font-medium text-gray-700 mb-2">Driving License Number</label>
    <input type="text" id="driving_license_number" name="driving_license_number" value="{{ $crb('driving_license_number') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Driving license number">
</div>

<div>
    <label for="voters_id" class="block text-sm font-medium text-gray-700 mb-2">Voter's ID</label>
    <input type="text" id="voters_id" name="voters_id" value="{{ $crb('voters_id') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Voter's ID number">
</div>

<div>
    <label for="foreign_unique_id" class="block text-sm font-medium text-gray-700 mb-2">Foreign Unique ID</label>
    <input type="text" id="foreign_unique_id" name="foreign_unique_id" value="{{ $crb('foreign_unique_id') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Foreign unique ID">
</div>

<div>
    <label for="custom_id_number_1" class="block text-sm font-medium text-gray-700 mb-2">Custom ID Number 1</label>
    <input type="text" id="custom_id_number_1" name="custom_id_number_1" value="{{ $crb('custom_id_number_1') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Custom ID number 1">
</div>

<div>
    <label for="custom_id_number_2" class="block text-sm font-medium text-gray-700 mb-2">Custom ID Number 2</label>
    <input type="text" id="custom_id_number_2" name="custom_id_number_2" value="{{ $crb('custom_id_number_2') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Custom ID number 2">
</div>
