@php
    $inputClass = 'w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500';
@endphp
<x-app-shell title="Register Collateral" header="Register Collateral">
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('collaterals.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to collaterals</a>
            </div>

            <form method="POST" action="{{ route('collaterals.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                    <select name="client_id" id="client_id" required class="{{ $inputClass }}">
                        <option value="">Select client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" data-uuid="{{ $client->uuid }}" @selected(old('client_id', request('client_id')) == $client->id)>
                                {{ $client->client_type === 'individual'
                                    ? trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''))
                                    : ($client->business_name ?? 'Client') }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="attach_to_loan" name="attach_to_loan" value="1"
                               class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                               @checked(old('attach_to_loan', request('loan_id')) ? true : false)>
                        <div class="flex-1">
                            <label for="attach_to_loan" class="text-sm font-medium text-blue-900 cursor-pointer">Attach directly to a loan</label>
                            <p class="text-xs text-blue-700 mt-1">Pledge this collateral to an open loan for the selected client. It will appear on the loan page immediately.</p>
                        </div>
                    </div>
                    <div id="loan_attach_section" class="{{ old('attach_to_loan', request('loan_id')) ? '' : 'hidden' }}">
                        <label for="loan_id" class="block text-sm font-medium text-blue-900 mb-1">Loan</label>
                        <select name="loan_id" id="loan_id" class="{{ $inputClass }}">
                            <option value="">Select loan...</option>
                        </select>
                        <p id="loan_attach_hint" class="text-xs text-blue-600 mt-1">Select a client first to load their open loans.</p>
                        @error('loan_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select name="type" required class="{{ $inputClass }}">
                            @foreach(\App\Models\Collateral::TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                        <select name="branch_id" class="{{ $inputClass }}">
                            <option value="">Optional</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title / Asset name *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="{{ $inputClass }}" placeholder="e.g. Toyota Hilux 2019">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="{{ $inputClass }}" placeholder="Condition, registration details, etc.">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estimated value (TZS) *</label>
                        <input type="number" name="estimated_value" step="0.01" min="0.01" value="{{ old('estimated_value') }}" required class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lending ratio (%)</label>
                        <input type="number" name="lending_ratio" step="0.01" min="1" max="100" value="{{ old('lending_ratio', 70) }}" class="{{ $inputClass }}">
                        <p class="text-xs text-gray-500 mt-1">Portion of value that can boost loan amount (default 70%).</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" value="{{ old('location') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Identifier / Reg. no.</label>
                        <input type="text" name="identifier" value="{{ old('identifier') }}" class="{{ $inputClass }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supporting document</label>
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="{{ $inputClass }}">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('collaterals.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700">Cancel</a>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700">Save collateral</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const clientSelect = document.getElementById('client_id');
        const loanSelect = document.getElementById('loan_id');
        const attachCheckbox = document.getElementById('attach_to_loan');
        const loanSection = document.getElementById('loan_attach_section');
        const loanHint = document.getElementById('loan_attach_hint');
        const preselectedLoanNumber = @json(request('loan_id'));
        const oldLoanId = @json(old('loan_id'));
        const clientLoansUrlTemplate = @json(route('collaterals.client-loans', ['client' => 'CLIENT_KEY']));

        function toggleLoanSection() {
            const show = attachCheckbox.checked;
            loanSection.classList.toggle('hidden', !show);
            loanSelect.required = show;
            if (show) {
                loadClientLoans();
            } else {
                loanSelect.value = '';
            }
        }

        async function loadClientLoans() {
            const option = clientSelect.options[clientSelect.selectedIndex];
            const clientKey = option?.dataset?.uuid || clientSelect.value;
            loanSelect.innerHTML = '<option value="">Select loan...</option>';

            if (!clientKey) {
                loanHint.textContent = 'Select a client first to load their open loans.';
                return;
            }

            loanHint.textContent = 'Loading loans...';

            try {
                const url = clientLoansUrlTemplate.replace('CLIENT_KEY', clientKey);
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error('Failed to load loans');
                const loans = await response.json();

                if (loans.length === 0) {
                    loanHint.textContent = 'No open loans without collateral for this client.';
                    return;
                }

                loans.forEach(loan => {
                    const opt = document.createElement('option');
                    opt.value = loan.id;
                    opt.textContent = loan.loan_number + ' — TZS ' + Number(loan.loan_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) + ' (' + loan.status_label + ')';
                    opt.dataset.loanNumber = loan.loan_number;
                    loanSelect.appendChild(opt);
                });

                if (oldLoanId) {
                    loanSelect.value = oldLoanId;
                } else if (preselectedLoanNumber) {
                    const match = [...loanSelect.options].find(o => o.dataset.loanNumber === preselectedLoanNumber);
                    if (match) loanSelect.value = match.value;
                }

                loanHint.textContent = loans.length + ' loan(s) available for attachment.';
            } catch (e) {
                loanHint.textContent = 'Unable to load loans. Please try again.';
            }
        }

        attachCheckbox.addEventListener('change', toggleLoanSection);
        clientSelect.addEventListener('change', () => {
            if (attachCheckbox.checked) loadClientLoans();
        });

        if (attachCheckbox.checked || preselectedLoanNumber) {
            attachCheckbox.checked = true;
            toggleLoanSection();
        }
    </script>
</x-app-shell>
