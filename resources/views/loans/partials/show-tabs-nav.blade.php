<div class="mt-6 pt-6 border-t border-gray-200" data-default-tab="{{ $loanShowDefaultTab }}">
    <nav class="flex flex-wrap gap-1 border-b border-gray-200" aria-label="Loan detail tabs">
        @foreach($loanShowTabs as $tabNum => $tabLabel)
            <button type="button"
                    class="loan-show-tab-btn px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap"
                    data-loan-tab-btn="{{ $tabNum }}"
                    role="tab"
                    aria-selected="{{ $tabNum === $loanShowDefaultTab ? 'true' : 'false' }}"
                    aria-controls="loan-tab-panel-{{ $tabNum }}">
                {{ $tabLabel }}
            </button>
        @endforeach
    </nav>
    <p id="loan_show_tab_hint" class="mt-3 text-sm text-gray-500">{{ $loanShowTabHints[$loanShowDefaultTab] }}</p>
    @if($loan->status === 'pending')
        <p class="mt-2 text-sm text-amber-700">This loan is pending — use <strong>Start Review</strong> above to begin assessment.</p>
    @elseif($loan->status === 'under_review')
        <p class="mt-2 text-sm text-blue-700">Under review — use the <strong>Review & Assessment</strong> tab for documents and collateral, then click <strong>Assessment Completed</strong> above.</p>
    @elseif($loan->status === 'assessed')
        <p class="mt-2 text-sm text-indigo-700">Assessment done — an admin can <strong>Approve Loan</strong> from the actions above.</p>
    @endif
</div>
