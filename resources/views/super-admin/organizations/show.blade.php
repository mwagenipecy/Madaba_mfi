<x-app-shell title="Organization Details" header="Organization Details">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $organization->name }}</h1>
                <p class="text-gray-600">Reg No: {{ $organization->registration_number }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('super-admin.organizations.edit', $organization->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Edit</a>
                @if($organization->status === 'active')
                    <form method="POST" action="{{ route('super-admin.organizations.deactivate', $organization->id) }}" onsubmit="return confirm('Deactivate this organization?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Deactivate</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('super-admin.organizations.reactivate', $organization->id) }}" onsubmit="return confirm('Reactivate this organization?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Reactivate</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Profile</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-500">Email</dt><dd class="text-gray-900">{{ $organization->email }}</dd></div>
                        <div><dt class="text-gray-500">Phone</dt><dd class="text-gray-900">{{ $organization->phone }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-gray-500">Address</dt><dd class="text-gray-900">{{ $organization->full_address }}</dd></div>
                        <div><dt class="text-gray-500">Status</dt><dd>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $organization->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($organization->status) }}
                            </span>
                        </dd></div>
                        <div><dt class="text-gray-500">Created</dt><dd class="text-gray-900">{{ $organization->created_at->format('M d, Y') }}</dd></div>
                    </dl>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Branches</h3>
                        <span class="text-sm text-gray-500">{{ $organization->branches->count() }} total</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Name</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">HQ</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($organization->branches as $branch)
                                    <tr>
                                        <td class="px-4 py-2 text-gray-900">{{ $branch->name }}</td>
                                        <td class="px-4 py-2">{{ $branch->is_hq ? 'Yes' : 'No' }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $branch->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($branch->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="px-4 py-4 text-gray-500" colspan="3">No branches</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Main Accounts</h3>
                        <span class="text-sm text-gray-500">{{ $mainAccounts->count() }} total</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Name</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Number</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($mainAccounts as $account)
                                    <tr>
                                        <td class="px-4 py-2 text-gray-900">{{ $account->name }}</td>
                                        <td class="px-4 py-2 text-gray-900">{{ $account->account_number }}</td>
                                        <td class="px-4 py-2 text-gray-900">{{ $account->formatted_balance }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="px-4 py-4 text-gray-500" colspan="3">No accounts</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Admin Users</h3>
                    <ul class="space-y-2 text-sm">
                        @forelse($organization->users as $user)
                            <li class="flex items-center justify-between">
                                <span class="text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</span>
                                <span class="text-gray-500">{{ $user->email }}</span>
                            </li>
                        @empty
                            <li class="text-gray-500">No admin users</li>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <a href="{{ route('super-admin.organizations.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200">Back to list</a>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>


