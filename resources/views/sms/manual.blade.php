<x-app-shell title="Manual SMS" header="Manual SMS">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Manual SMS</h1>
                    <p class="text-sm text-gray-500 mt-1">Select clients or paste phone numbers for flexible messaging.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('sms.index') }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg">
                        Bulk by criteria
                    </a>
                    <a href="{{ route('sms.reports') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg">
                        SMS Reports
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">{{ $errors->first() }}</div>
            @endif

            <form method="GET" action="{{ route('sms.manual') }}" class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search clients</label>
                        <input type="text" name="search" value="{{ $search }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Name, client number, phone...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                        <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">All branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) $branchId === (string) $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-medium py-2 px-5 rounded-lg">Filter</button>
                </div>
            </form>

            <form method="POST" action="{{ route('sms.send-manual') }}" class="space-y-6"
                  onsubmit="return confirm('Send SMS to the selected clients / numbers now?');">
                @csrf

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Select clients</h2>
                            <p class="text-sm text-gray-500">Only clients with a phone number are listed.</p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox"
                                   onchange="document.querySelectorAll('.client-check').forEach(cb => cb.checked = this.checked)"
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            Select all on page
                        </label>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Select</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($clients as $client)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="client_ids[]" value="{{ $client->id }}"
                                                   class="client-check rounded border-gray-300 text-green-600 focus:ring-green-500"
                                                   @checked(collect(old('client_ids', []))->contains($client->id))>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $client->display_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $client->client_number }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $client->phone_number }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $client->branch->name ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $client->status_badge_color }}">
                                                {{ $client->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No clients found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($clients->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $clients->links() }}
                        </div>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-1">Or paste phone numbers</h2>
                        <p class="text-sm text-gray-500 mb-3">Separate with commas, spaces, or new lines. Formats like 07..., +255..., 255... are accepted.</p>
                        <textarea name="phone_numbers" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="0767582837, 255767582837">{{ old('phone_numbers') }}</textarea>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-1">Message</h2>
                        <p class="text-xs text-gray-500 mb-3">
                            For selected clients you can use: {client_name}, {client_number}, {phone}
                        </p>
                        <textarea name="message" rows="5" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Write SMS content...">{{ old('message', 'Habari {client_name}, ujumbe kutoka WIBOOK. Asante.') }}</textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-6 rounded-lg">
                            Send SMS
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-shell>
