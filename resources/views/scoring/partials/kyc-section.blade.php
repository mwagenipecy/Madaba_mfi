<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Client KYC</h3>
            @php
                $kycBadge = match($report['kyc']['status'] ?? 'pending') {
                    'verified' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    'expired' => 'bg-orange-100 text-orange-800',
                    default => 'bg-yellow-100 text-yellow-800',
                };
            @endphp
            <span class="px-3 py-1 text-xs font-semibold rounded-full capitalize {{ $kycBadge }}">
                {{ $report['kyc']['status'] ?? 'pending' }}
            </span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-3 text-sm mb-6">
            <div><dt class="text-gray-500">Verified</dt><dd class="font-medium">{{ $report['kyc']['verified'] ? 'Yes' : 'No' }}</dd></div>
            <div><dt class="text-gray-500">Verification Date</dt><dd class="font-medium">{{ $report['kyc']['verification_date'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Verified By</dt><dd class="font-medium">{{ $report['kyc']['verified_by'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">National ID</dt><dd class="font-medium">{{ $report['kyc']['national_id'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Passport</dt><dd class="font-medium">{{ $report['kyc']['passport_number'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Date of Birth</dt><dd class="font-medium">{{ $report['kyc']['date_of_birth'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Gender</dt><dd class="font-medium capitalize">{{ $report['kyc']['gender'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Business Reg. No.</dt><dd class="font-medium">{{ $report['kyc']['business_registration_number'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Business Type</dt><dd class="font-medium capitalize">{{ $report['kyc']['business_type'] ?? '—' }}</dd></div>
            <div class="sm:col-span-2 lg:col-span-3"><dt class="text-gray-500">Address</dt><dd class="font-medium">{{ trim(($report['kyc']['physical_address'] ?? '') . ', ' . ($report['kyc']['city'] ?? '') . ' ' . ($report['kyc']['region'] ?? '')) ?: '—' }}</dd></div>
            @if($report['kyc']['kyc_notes'])
                <div class="sm:col-span-2 lg:col-span-3"><dt class="text-gray-500">KYC Notes</dt><dd class="font-medium text-gray-700">{{ $report['kyc']['kyc_notes'] }}</dd></div>
            @endif
        </dl>

        <h4 class="text-sm font-semibold text-gray-800 mb-3">KYC Documents ({{ $report['kyc']['document_count'] }})</h4>
        @if(count($report['kyc']['documents']) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($report['kyc']['documents'] as $document)
                    @php
                        $docBadge = match($document['status'] ?? 'pending') {
                            'approved', 'verified' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            default => 'bg-yellow-100 text-yellow-800',
                        };
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $document['type']) }}</p>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full capitalize {{ $docBadge }}">{{ $document['status'] }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1 truncate">{{ $document['name'] }}</p>
                        @if($document['description'])
                            <p class="text-xs text-gray-500 mt-1">{{ $document['description'] }}</p>
                        @endif
                        <div class="text-xs text-gray-500 mt-2 space-y-0.5">
                            @if($document['uploaded_at'])<div>Uploaded: {{ $document['uploaded_at'] }}</div>@endif
                            @if($document['size_kb'])<div>Size: {{ $document['size_kb'] }} KB</div>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3">No KYC documents uploaded for this client.</p>
        @endif
    </div>
</div>
