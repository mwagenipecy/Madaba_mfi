<x-app-shell title="Collateral {{ $collateral->reference_number }}" header="Collateral Details">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-between">
                <a href="{{ route('collaterals.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; All collaterals</a>
                @if($collateral->status !== 'pledged')
                    <a href="{{ route('collaterals.edit', $collateral) }}" class="text-sm text-blue-600 hover:underline">Edit</a>
                @endif
            </div>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-500 font-mono">{{ $collateral->reference_number }}</p>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $collateral->title }}</h1>
                        <p class="text-sm text-gray-600 mt-1">{{ $collateral->typeLabel() }}</p>
                    </div>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $collateral->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $collateral->statusLabel() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Client:</span> <span class="font-medium">{{ $collateral->client?->display_name ?? '—' }}</span></div>
                    <div><span class="text-gray-500">Branch:</span> <span class="font-medium">{{ $collateral->branch?->name ?? '—' }}</span></div>
                    <div><span class="text-gray-500">Estimated value:</span> <span class="font-medium">TZS {{ number_format($collateral->estimated_value, 2) }}</span></div>
                    <div><span class="text-gray-500">Loan boost capacity:</span> <span class="font-medium text-green-700">TZS {{ number_format($collateral->lendingCapacity(), 2) }}</span></div>
                    <div><span class="text-gray-500">Lending ratio:</span> <span class="font-medium">{{ $collateral->lending_ratio }}%</span></div>
                    <div><span class="text-gray-500">Location:</span> <span class="font-medium">{{ $collateral->location ?? '—' }}</span></div>
                    <div><span class="text-gray-500">Identifier:</span> <span class="font-medium">{{ $collateral->identifier ?? '—' }}</span></div>
                    <div><span class="text-gray-500">Registered by:</span> <span class="font-medium">{{ $collateral->creator?->name ?? '—' }}</span></div>
                    <div><span class="text-gray-500">Registered on:</span> <span class="font-medium">{{ $collateral->created_at?->format('d M Y H:i') ?? '—' }}</span></div>
                    <div><span class="text-gray-500">Pledged at:</span> <span class="font-medium">{{ $collateral->pledged_at?->format('d M Y H:i') ?? '—' }}</span></div>
                </div>

                @if($collateral->description)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-500 mb-1">Description</p>
                        <p class="text-sm text-gray-900">{{ $collateral->description }}</p>
                    </div>
                @endif

                @if($collateral->loan)
                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                        Pledged to loan <a href="{{ route('loans.show', $collateral->loan) }}" class="font-semibold text-blue-700 hover:underline">{{ $collateral->loan->loan_number }}</a>.
                        This collateral can only be used once until released.
                    </div>
                @endif

                @if($collateral->document_path)
                    <div class="mt-4">
                        <a href="{{ asset('storage/' . $collateral->document_path) }}" target="_blank" class="text-sm text-blue-600 hover:underline">View supporting document</a>
                    </div>
                @endif

                @if($collateral->status === 'pledged')
                    <form method="POST" action="{{ route('collaterals.release', $collateral) }}" class="mt-6" onsubmit="return confirm('Release this collateral from the loan? It will become available again.');">
                        @csrf
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Release from loan</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-shell>
