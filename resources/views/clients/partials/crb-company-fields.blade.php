@php
    $crb = fn (string $key, $default = '') => old($key, $meta[$key] ?? $default);
@endphp

<div class="md:col-span-2 border-t border-gray-200 pt-6 mt-2">
    <h4 class="text-md font-semibold text-gray-800 mb-1">CRB Report Details</h4>
    <p class="text-xs text-gray-500 mb-4">These fields populate the Company sheet on the CRB report.</p>
</div>

<div>
    <label for="trade_name" class="block text-sm font-medium text-gray-700 mb-2">Trade Name</label>
    <input type="text" id="trade_name" name="trade_name" value="{{ $crb('trade_name') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Trading name">
</div>

<div>
    <label for="legal_form" class="block text-sm font-medium text-gray-700 mb-2">Legal Form</label>
    <input type="text" id="legal_form" name="legal_form" value="{{ $crb('legal_form') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Optional override (defaults from business type)">
</div>

<div>
    <label for="establishment_date" class="block text-sm font-medium text-gray-700 mb-2">Establishment Date</label>
    <input type="date" id="establishment_date" name="establishment_date" value="{{ $crb('establishment_date') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
</div>

<div>
    <label for="registration_country" class="block text-sm font-medium text-gray-700 mb-2">Registration Country</label>
    <input type="text" id="registration_country" name="registration_country" value="{{ $crb('registration_country') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Country of registration">
</div>

<div>
    <label for="industry_sector" class="block text-sm font-medium text-gray-700 mb-2">Industry Sector</label>
    <input type="text" id="industry_sector" name="industry_sector" value="{{ $crb('industry_sector') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
           placeholder="Industry or sector">
</div>
