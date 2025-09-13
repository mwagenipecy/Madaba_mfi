<x-app-shell title="Create Account Recharge" header="Create Account Recharge">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Create Account Recharge</h1>
                        <p class="text-gray-600 mt-1">Recharge main accounts and distribute funds to branches</p>
                    </div>

                    <form method="POST" action="{{ route('payments.account-recharge.store') }}">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Main Account -->
                            <div>
                                <label for="main_account_id" class="block text-sm font-medium text-gray-700 mb-2">Main Account</label>
                                <select name="main_account_id" id="main_account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Select main account to recharge</option>
                                    @foreach($mainAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('main_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->accountType->name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('main_account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Recharge Amount -->
                            <div>
                                <label for="recharge_amount" class="block text-sm font-medium text-gray-700 mb-2">Recharge Amount (TZS)</label>
                                <input type="number" name="recharge_amount" id="recharge_amount" step="0.01" min="0.01" required 
                                       value="{{ old('recharge_amount') }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Enter recharge amount">
                                @error('recharge_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" id="description" rows="3" required
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                          placeholder="Enter recharge description">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Distribution Plan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Distribution Plan</label>
                                <p class="text-sm text-gray-500 mb-4">Select branches and amounts for fund distribution</p>
                                
                                <div id="distribution-container" class="space-y-3">
                                    @if(old('distribution_plan') && count(old('distribution_plan')) > 0)
                                        @foreach(old('distribution_plan') as $index => $distribution)
                                            <div class="distribution-item flex items-center space-x-3 p-3 border border-gray-200 rounded-lg">
                                                <div class="flex-1">
                                                    <select name="distribution_plan[{{ $index }}][account_id]" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                                        <option value="">Select branch account</option>
                                                        @foreach($branches as $branch)
                                                            @php
                                                                $branchAccount = \App\Models\Account::where('organization_id', $branch->organization_id)
                                                                    ->where('branch_id', $branch->id)
                                                                    ->whereHas('accountType', function($query) {
                                                                        $query->where('name', 'Liability');
                                                                    })
                                                                    ->first();
                                                            @endphp
                                                            @if($branchAccount)
                                                                <option value="{{ $branchAccount->id }}" {{ $distribution['account_id'] == $branchAccount->id ? 'selected' : '' }}>
                                                                    {{ $branch->name }} - {{ $branchAccount->name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="w-32">
                                                    <input type="number" name="distribution_plan[{{ $index }}][amount]" step="0.01" min="0.01" required 
                                                           value="{{ $distribution['amount'] }}"
                                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                                           placeholder="Amount">
                                                </div>
                                                <button type="button" class="remove-distribution text-red-600 hover:text-red-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    @else
                                        <!-- Default empty distribution item -->
                                        <div class="distribution-item flex items-center space-x-3 p-3 border border-gray-200 rounded-lg">
                                            <div class="flex-1">
                                                <select name="distribution_plan[0][account_id]" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                                    <option value="">Select branch account</option>
                                                    @foreach($branches as $branch)
                                                        @php
                                                            $branchAccount = \App\Models\Account::where('organization_id', $branch->organization_id)
                                                                ->where('branch_id', $branch->id)
                                                                ->whereHas('accountType', function($query) {
                                                                    $query->where('name', 'Liability');
                                                                })
                                                                ->first();
                                                        @endphp
                                                        @if($branchAccount)
                                                            <option value="{{ $branchAccount->id }}">
                                                                {{ $branch->name }} - {{ $branchAccount->name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="w-32">
                                                <input type="number" name="distribution_plan[0][amount]" step="0.01" min="0.01" required 
                                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                                       placeholder="Amount">
                                            </div>
                                            <button type="button" class="remove-distribution text-red-600 hover:text-red-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                <button type="button" id="add-distribution" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Add Branch Distribution
                                </button>
                                @error('distribution_plan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('payments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                Submit Recharge Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let distributionIndex = {{ old('distribution_plan') && count(old('distribution_plan')) > 0 ? count(old('distribution_plan')) : 1 }};
        const branchAccounts = @json($branchAccounts);
        
        console.log('Distribution index:', distributionIndex);
        console.log('Branch accounts:', branchAccounts);

        document.getElementById('add-distribution').addEventListener('click', function() {
            console.log('Add distribution button clicked');
            const container = document.getElementById('distribution-container');
            const div = document.createElement('div');
            div.className = 'distribution-item flex items-center space-x-3 p-3 border border-gray-200 rounded-lg';
            div.innerHTML = `
                <div class="flex-1">
                    <select name="distribution_plan[${distributionIndex}][account_id]" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select branch account</option>
                        ${branchAccounts.map(account => 
                            account.id ? `<option value="${account.id}">${account.branch_name} - ${account.name}</option>` : ''
                        ).join('')}
                    </select>
                </div>
                <div class="w-32">
                    <input type="number" name="distribution_plan[${distributionIndex}][amount]" step="0.01" min="0.01" required 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="Amount">
                </div>
                <button type="button" class="remove-distribution text-red-600 hover:text-red-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            
            container.appendChild(div);
            distributionIndex++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-distribution')) {
                e.target.closest('.distribution-item').remove();
            }
        });
    </script>
</x-app-shell>
