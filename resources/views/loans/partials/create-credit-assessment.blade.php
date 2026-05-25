{{-- Step 2: Credit Assessment --}}
<div class="wizard-step hidden" data-step="2">
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Credit Assessment</h3>
        <p class="text-sm text-gray-500 mb-6">Advisory score based on client profile, loan terms, and history. You can continue even if warnings appear.</p>

        {{-- Collateral attachment (single-use, boosts limit) --}}
        <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900">Attach Collateral (optional)</h4>
                    <p class="text-xs text-gray-500 mt-1">Select a registered asset for this client. Each item can be pledged once to boost eligibility and the recommended loan amount.</p>
                </div>
                <a href="{{ route('collaterals.create') }}" target="_blank" rel="noopener"
                   class="text-sm text-green-700 hover:text-green-800 font-medium whitespace-nowrap">
                    + Register new
                </a>
            </div>
            <select name="collateral_id" id="collateral_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">No collateral</option>
            </select>
            @error('collateral_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <div id="collateral_preview" class="hidden mt-3 p-3 rounded-lg border border-green-200 bg-green-50 text-sm">
                <p class="font-semibold text-green-900" id="collateral_preview_title">—</p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2 text-xs text-green-800">
                    <div><span class="text-green-600">Value:</span> <span id="collateral_preview_value">—</span></div>
                    <div><span class="text-green-600">Lending capacity:</span> <span id="collateral_preview_capacity">—</span></div>
                    <div><span class="text-green-600">Reference:</span> <span id="collateral_preview_ref">—</span></div>
                </div>
            </div>
        </div>

        <div id="collateral_boost_panel" class="hidden mb-6 rounded-lg border border-green-200 bg-green-50 p-4">
            <h4 class="text-sm font-semibold text-green-900 mb-2">Collateral Boost Applied</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                <div>
                    <p class="text-green-700 text-xs">Base recommended max</p>
                    <p class="font-semibold text-gray-900" id="score_base_max">—</p>
                </div>
                <div>
                    <p class="text-green-700 text-xs">Collateral boost</p>
                    <p class="font-semibold text-green-800" id="score_collateral_boost">—</p>
                </div>
                <div>
                    <p class="text-green-700 text-xs">Effective max</p>
                    <p class="font-semibold text-green-900" id="score_effective_max">—</p>
                </div>
            </div>
        </div>

        <div id="score_loading" class="hidden text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
            <p class="mt-3 text-sm text-gray-500">Calculating client score...</p>
        </div>

        <div id="score_empty" class="text-center py-12 text-gray-500">
            <p>Select a client and complete the loan application details in Step 1 to run the credit assessment.</p>
        </div>

        <div id="score_results" class="hidden space-y-6">
            {{-- Amount affordability --}}
            <div id="amount_assessment_panel" class="hidden rounded-lg border border-blue-200 bg-blue-50 p-4">
                <h4 class="text-sm font-semibold text-blue-900 mb-3">Amount Affordability</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm mb-4">
                    <div>
                        <p class="text-gray-500 text-xs">Requested amount</p>
                        <p class="font-semibold text-gray-900" id="assess_requested">—</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Recommended max</p>
                        <p class="font-semibold text-green-700" id="assess_recommended">—</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Within limit?</p>
                        <p id="assess_affordable">—</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Repayment outlook</p>
                        <span id="assess_outlook" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border">—</span>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Est. installment</p>
                        <p class="font-medium text-gray-900" id="assess_monthly">—</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Safe monthly capacity</p>
                        <p class="font-medium text-gray-900" id="assess_max_monthly">—</p>
                    </div>
                </div>
                <ul id="amount_assessment_alerts" class="space-y-2"></ul>
            </div>

            {{-- Score header --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-4 rounded-lg bg-gray-50 border border-gray-200">
                <div class="flex items-center gap-4">
                    <div id="score_circle" class="flex items-center justify-center w-20 h-20 rounded-full border-4 text-2xl font-bold">
                        <span id="score_value">0</span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">System Score</p>
                        <p class="text-xl font-semibold text-gray-900" id="score_band_label">-</p>
                        <p class="text-sm text-gray-600" id="score_client_name">-</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Recommended max loan</p>
                    <p class="text-lg font-semibold text-green-700" id="score_recommended_max">-</p>
                    <span id="score_eligibility_badge" class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full"></span>
                </div>
            </div>

            {{-- Factor breakdown --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Score Factors</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Repayment history</span>
                            <span class="font-medium" id="factor_repayment">-</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="bar_repayment" class="bg-green-600 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Arrears record</span>
                            <span class="font-medium" id="factor_arrears">-</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="bar_arrears" class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Profile & KYC</span>
                            <span class="font-medium" id="factor_profile">-</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="bar_profile" class="bg-purple-600 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Portfolio behavior</span>
                            <span class="font-medium" id="factor_portfolio">-</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="bar_portfolio" class="bg-amber-600 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- History summary --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Loan History</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <div class="p-3 bg-white border border-gray-200 rounded-lg text-center">
                        <p class="text-gray-500 text-xs">Total loans</p>
                        <p class="text-lg font-bold text-gray-900" id="hist_total">0</p>
                    </div>
                    <div class="p-3 bg-white border border-gray-200 rounded-lg text-center">
                        <p class="text-gray-500 text-xs">Completed</p>
                        <p class="text-lg font-bold text-green-700" id="hist_completed">0</p>
                    </div>
                    <div class="p-3 bg-white border border-gray-200 rounded-lg text-center">
                        <p class="text-gray-500 text-xs">Overdue</p>
                        <p class="text-lg font-bold text-red-700" id="hist_overdue">0</p>
                    </div>
                    <div class="p-3 bg-white border border-gray-200 rounded-lg text-center">
                        <p class="text-gray-500 text-xs">Written off</p>
                        <p class="text-lg font-bold text-red-900" id="hist_written_off">0</p>
                    </div>
                </div>
            </div>

            {{-- Flags --}}
            <div id="score_flags_container" class="hidden">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Alerts</h4>
                <ul id="score_flags" class="space-y-2 text-sm"></ul>
            </div>

            {{-- Eligibility checks --}}
            <div id="score_checks_container" class="hidden">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Eligibility Checks</h4>
                <ul id="score_checks" class="space-y-1 text-sm"></ul>
            </div>

            {{-- Reasons --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Assessment Notes</h4>
                <ul id="score_reasons" class="list-disc list-inside text-sm text-gray-600 space-y-1"></ul>
            </div>
        </div>
    </div>
</div>
