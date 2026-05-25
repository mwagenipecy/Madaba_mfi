@php
    $inputClass = 'w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500';
@endphp
<x-app-shell title="Edit Collateral" header="Edit Collateral">
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('collaterals.show', $collateral) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>

            <form method="POST" action="{{ route('collaterals.update', $collateral) }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                    <select name="client_id" required class="{{ $inputClass }}">
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id', $collateral->client_id) == $client->id)>
                                {{ $client->client_type === 'individual'
                                    ? trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''))
                                    : ($client->business_name ?? 'Client') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select name="type" required class="{{ $inputClass }}">
                            @foreach(\App\Models\Collateral::TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(old('type', $collateral->type) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                        <select name="branch_id" class="{{ $inputClass }}">
                            <option value="">Optional</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id', $collateral->branch_id) == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $collateral->title) }}" required class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="{{ $inputClass }}">{{ old('description', $collateral->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estimated value (TZS) *</label>
                        <input type="number" name="estimated_value" step="0.01" min="0.01" value="{{ old('estimated_value', $collateral->estimated_value) }}" required class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lending ratio (%)</label>
                        <input type="number" name="lending_ratio" step="0.01" min="1" max="100" value="{{ old('lending_ratio', $collateral->lending_ratio) }}" class="{{ $inputClass }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" value="{{ old('location', $collateral->location) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Identifier</label>
                        <input type="text" name="identifier" value="{{ old('identifier', $collateral->identifier) }}" class="{{ $inputClass }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Replace document</label>
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="{{ $inputClass }}">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('collaterals.show', $collateral) }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700">Cancel</a>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-app-shell>
