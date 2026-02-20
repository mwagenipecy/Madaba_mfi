<x-app-shell title="Daily Till" header="Daily Till - Cash & Mobile Wallet">
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="mb-4 p-4 bg-amber-50 border border-amber-300 rounded-lg text-amber-900">{{ session('warning') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">{{ session('error') }}</div>
            @endif

            @php
                $recordDetails = [];
                foreach ($recordsWithVariance as $row) {
                    $r = $row->record;
                    $opening = (float) ($r->opening_cash ?? 0) + (float) ($r->opening_mobile_wallet ?? 0);
                    $collected = (float) ($r->amount_collected ?? 0);
                    $expensesTotal = collect($r->expenses ?? [])->sum('amount');
                    $loans = (float) ($r->loans_given ?? 0);
                    $calculated_closing = $opening + $collected - $expensesTotal - $loans;
                    $recordDetails[$r->id] = [
                        'date' => $r->record_date->format('M d, Y'),
                        'opening_cash' => (float) ($r->opening_cash ?? 0),
                        'opening_mobile_wallet' => (float) ($r->opening_mobile_wallet ?? 0),
                        'expected_cash' => $row->expected_opening_cash !== null ? (float) $row->expected_opening_cash : null,
                        'expected_mobile' => $row->expected_opening_mobile !== null ? (float) $row->expected_opening_mobile : null,
                        'expected_opening_total' => $row->expected_opening_total ?? null,
                        'variance_total' => $row->variance_total !== null ? (float) $row->variance_total : null,
                        'variance_cash' => $row->variance_cash !== null ? (float) $row->variance_cash : null,
                        'variance_mobile' => $row->variance_mobile !== null ? (float) $row->variance_mobile : null,
                        'amount_collected' => $r->amount_collected !== null ? (float) $r->amount_collected : null,
                        'loans_given' => $r->loans_given !== null ? (float) $r->loans_given : null,
                        'expenses' => $r->expenses ?? [],
                        'expenses_total' => $expensesTotal,
                        'calculated_closing' => $calculated_closing,
                        'actual_closing' => ($r->closing_cash !== null || $r->closing_mobile_wallet !== null) ? (float) ($r->closing_cash ?? 0) + (float) ($r->closing_mobile_wallet ?? 0) : null,
                        'closing_cash' => $r->closing_cash !== null ? (float) $r->closing_cash : null,
                        'closing_mobile_wallet' => $r->closing_mobile_wallet !== null ? (float) $r->closing_mobile_wallet : null,
                        'opening_notes' => $r->opening_notes ?? '',
                        'closing_notes' => $r->closing_notes ?? '',
                        'opened_by' => $r->openingRecordedBy ? trim($r->openingRecordedBy->first_name . ' ' . $r->openingRecordedBy->last_name) : '',
                        'closed_by' => $r->closingRecordedBy ? trim($r->closingRecordedBy->first_name . ' ' . $r->closingRecordedBy->last_name) : '',
                        'branch' => $r->branch->name ?? null,
                    ];
                }
                $openingByDate = [];
                $previousDayClosingByDate = [];
                foreach ($recordsWithVariance as $row) {
                    $r = $row->record;
                    $openingByDate[$r->record_date->format('Y-m-d')] = [
                        'opening_cash' => (float) ($r->opening_cash ?? 0),
                        'opening_mobile_wallet' => (float) ($r->opening_mobile_wallet ?? 0),
                    ];
                    $nextDay = $r->record_date->copy()->addDay()->format('Y-m-d');
                    $previousDayClosingByDate[$nextDay] = $recordDetails[$r->id]['actual_closing'];
                }
            @endphp
            <script>window.dailyTillRecordDetails = @json($recordDetails); window.dailyTillOpeningByDate = @json($openingByDate); window.dailyTillPreviousDayClosingByDate = @json($previousDayClosingByDate);</script>
            <div x-data="{ openModalOpen: false, closeModalOpen: false, detailModalOpen: false, selectedRecord: null }">
                <!-- Top: Open Day & Close Day buttons -->
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <button type="button" @click="openModalOpen = true"
                            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Open Day
                    </button>
                    <button type="button" @click="closeModalOpen = true"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        Close Day
                    </button>
                </div>
                <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700">
                    <p class="font-semibold text-slate-800 mb-2">Formula summary</p>
                    <p><strong>Opening total</strong> = Opening Cash + Opening Mobile</p>
                    <p><strong>Expected closing</strong> = Opening total + Amount collected − Expenses − Loans given</p>
                    <p><strong>Actual closing</strong> = Closing Cash + Closing Mobile</p>
                    <p class="mt-1 text-slate-600">Today’s opening should match yesterday’s closing. The system alerts on mismatch but allows saving for audit.</p>
                </div>

                <!-- Open Day Modal: empty form, select date; disable if selected date already open or closed -->
                <div x-show="openModalOpen" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @keydown.escape.window="openModalOpen = false">
                    <div class="flex min-h-screen items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50" @click="openModalOpen = false"></div>
                        <div x-show="openModalOpen"
                             x-transition:enter="ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="relative w-full max-w-xl rounded-xl bg-white shadow-xl"
                             @click.stop
                             x-data="{
                                openSelectedDate: '',
                                openingCash: '',
                                openingMobile: '',
                                datesWithOpening: @js($datesWithOpeningRecord),
                                datesWithClosing: @js($datesWithClosingRecord),
                                get previousDayDate() {
                                    if (!this.openSelectedDate) return null;
                                    const d = new Date(this.openSelectedDate + 'T12:00:00');
                                    d.setDate(d.getDate() - 1);
                                    return d.toISOString().slice(0, 10);
                                },
                                get previousDayNotClosed() {
                                    if (!this.openSelectedDate || !this.previousDayDate) return false;
                                    return this.datesWithOpening.includes(this.previousDayDate) && !this.datesWithClosing.includes(this.previousDayDate);
                                },
                                get openDisabled() {
                                    if (!this.openSelectedDate) return false;
                                    if (this.previousDayNotClosed) return true;
                                    return this.datesWithOpening.includes(this.openSelectedDate) || this.datesWithClosing.includes(this.openSelectedDate);
                                },
                                get openDisabledReason() {
                                    if (!this.openSelectedDate) return null;
                                    if (this.previousDayNotClosed) return 'previous_not_closed';
                                    if (this.datesWithOpening.includes(this.openSelectedDate) || this.datesWithClosing.includes(this.openSelectedDate)) return 'already_recorded';
                                    return null;
                                },
                                get expectedOpening() {
                                    return (typeof window.dailyTillPreviousDayClosingByDate !== 'undefined' && this.openSelectedDate) ? window.dailyTillPreviousDayClosingByDate[this.openSelectedDate] : null;
                                },
                                get openingTotal() {
                                    return Number(this.openingCash || 0) + Number(this.openingMobile || 0);
                                },
                                get openingMismatch() {
                                    if (this.expectedOpening == null || !this.openSelectedDate) return null;
                                    const expected = Number(this.expectedOpening);
                                    const entered = this.openingTotal;
                                    if (Math.abs(entered - expected) < 0.01) return null;
                                    return { expected, entered, diff: entered - expected };
                                }
                             }">
                            <div class="p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Open Day</h4>
                                <p class="text-sm text-gray-600 mb-4">Select a date, then fill opening amounts. Opening should match yesterday's closing for a continuous chain.</p>
                                <form action="{{ route('daily-till.open') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                        <input type="date" name="record_date" x-model="openSelectedDate" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <template x-if="openSelectedDate && openDisabledReason === 'previous_not_closed'">
                                        <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                                            <strong>Cannot open this day.</strong> The previous day (<span x-text="previousDayDate ? new Date(previousDayDate + 'T12:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) : ''"></span>) is not closed. Close that day first using <strong>Close Day</strong>, then you can open this date.
                                        </div>
                                    </template>
                                    <template x-if="openSelectedDate && openDisabledReason === 'already_recorded'">
                                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                                            <strong>Recording disabled.</strong> This date already has an opening record or the day is already closed.
                                        </div>
                                    </template>
                                    <div class="p-3 rounded-lg bg-blue-50 border border-blue-200 text-sm" x-show="expectedOpening != null && !openDisabled">
                                        <p class="text-blue-800 font-medium">Expected opening (yesterday's closing): <span class="tabular-nums font-bold" x-text="'TZS ' + Number(expectedOpening).toLocaleString('en-US', {minimumFractionDigits: 2})"></span></p>
                                    </div>
                                    <template x-if="openingMismatch">
                                        <div class="p-3 bg-amber-50 border border-amber-300 rounded-lg text-sm text-amber-900">
                                            <p class="font-semibold">Wrong opening amount</p>
                                            <p class="mt-1">Expected (yesterday's closing): <span class="tabular-nums font-medium" x-text="'TZS ' + Number(openingMismatch.expected).toLocaleString('en-US', {minimumFractionDigits: 2})"></span></p>
                                            <p>You entered: <span class="tabular-nums font-medium" x-text="'TZS ' + Number(openingMismatch.entered).toLocaleString('en-US', {minimumFractionDigits: 2})"></span></p>
                                            <p>Difference: <span class="tabular-nums font-medium" :class="openingMismatch.diff > 0 ? 'text-green-700' : 'text-red-700'" x-text="(openingMismatch.diff > 0 ? '+' : '') + 'TZS ' + Number(openingMismatch.diff).toLocaleString('en-US', {minimumFractionDigits: 2})"></span></p>
                                            <p class="text-amber-700 text-xs mt-1">You may still save for audit tracking.</p>
                                        </div>
                                    </template>
                                    @if($branches->count() > 1)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                        <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                :disabled="openDisabled">
                                            <option value="">— All / HQ —</option>
                                            @foreach($branches as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Opening cash (TZS) *</label>
                                            <input type="number" name="opening_cash" step="0.01" min="0" x-model="openingCash" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                   :disabled="openDisabled" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Opening mobile wallet (TZS) *</label>
                                            <input type="number" name="opening_mobile_wallet" step="0.01" min="0" x-model="openingMobile" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                   :disabled="openDisabled" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="text-xs text-slate-500" x-show="openingCash !== '' || openingMobile !== ''">
                                        Total opening: <span class="tabular-nums font-medium text-gray-700" x-text="'TZS ' + Number(openingTotal).toLocaleString('en-US', {minimumFractionDigits: 2})"></span>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                        <textarea name="opening_notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                  :disabled="openDisabled" placeholder="Optional notes"></textarea>
                                    </div>
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button type="button" @click="openModalOpen = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50 disabled:pointer-events-none"
                                                :disabled="openDisabled">Save opening</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Close Day Modal: empty form, select date; disable if selected date already closed or not opened -->
                <div x-show="closeModalOpen" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @keydown.escape.window="closeModalOpen = false">
                    <div class="flex min-h-screen items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50" @click="closeModalOpen = false"></div>
                        <div x-show="closeModalOpen"
                             x-transition:enter="ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="relative w-full max-w-2xl rounded-xl bg-white shadow-xl"
                             @click.stop
                             x-data="{
                                closeSelectedDate: '',
                                amountCollected: '',
                                loansGiven: '',
                                datesWithOpening: @js($datesWithOpeningRecord),
                                datesWithClosing: @js($datesWithClosingRecord),
                                expenses: [{ amount: '', description: '' }],
                                addExpense() { this.expenses.push({ amount: '', description: '' }); },
                                removeExpense(i) { if (this.expenses.length > 1) this.expenses.splice(i, 1); },
                                get closeDisabled() {
                                    if (!this.closeSelectedDate) return false;
                                    if (this.datesWithClosing.includes(this.closeSelectedDate)) return true;
                                    if (!this.datesWithOpening.includes(this.closeSelectedDate)) return true;
                                    return false;
                                },
                                get closeReason() {
                                    if (!this.closeSelectedDate) return '';
                                    if (this.datesWithClosing.includes(this.closeSelectedDate)) return 'closed';
                                    if (!this.datesWithOpening.includes(this.closeSelectedDate)) return 'notopened';
                                    return '';
                                },
                                get expectedClosing() {
                                    const open = typeof window.dailyTillOpeningByDate !== 'undefined' && this.closeSelectedDate ? window.dailyTillOpeningByDate[this.closeSelectedDate] : null;
                                    if (!open) return null;
                                    const expTotal = this.expenses.reduce((s, e) => s + Number(e.amount || 0), 0);
                                    return open.opening_cash + open.opening_mobile_wallet + Number(this.amountCollected || 0) - expTotal - Number(this.loansGiven || 0);
                                },
                                closingCash: '',
                                closingMobile: '',
                                get actualClosing() { return Number(this.closingCash || 0) + Number(this.closingMobile || 0); },
                                get closingMismatch() {
                                    if (this.expectedClosing == null || this.closeDisabled) return null;
                                    const expected = Number(this.expectedClosing);
                                    const actual = this.actualClosing;
                                    if (actual === 0 && expected === 0) return null;
                                    if (Math.abs(actual - expected) < 0.01) return null;
                                    return { expected, actual, diff: actual - expected };
                                }
                             }">
                            <div class="p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Close Day</h4>
                                <p class="text-sm text-gray-600 mb-4">Select a date, then fill closing details. Recording is disabled if the date is already closed or was not opened.</p>
                                <form action="{{ route('daily-till.close') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                        <input type="date" name="record_date" x-model="closeSelectedDate" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    </div>
                                    <template x-if="closeSelectedDate && closeReason === 'closed'">
                                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                                            <strong>Recording disabled.</strong> This date is already closed.
                                        </div>
                                    </template>
                                    <template x-if="closeSelectedDate && closeReason === 'notopened'">
                                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                                            <strong>Recording disabled.</strong> This date has no opening record. Open the day first.
                                        </div>
                                    </template>
                                    @if($branches->count() > 1)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                        <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                                :disabled="closeDisabled">
                                            <option value="">— All / HQ —</option>
                                            @foreach($branches as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount collected (TZS)</label>
                                        <input type="number" name="amount_collected" step="0.01" min="0" x-model="amountCollected"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                               :disabled="closeDisabled" placeholder="0.00">
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-sm font-medium text-gray-700">Expenses (if any)</label>
                                            <button type="button" @click="addExpense()" class="text-sm text-orange-600 hover:text-orange-700 font-medium"
                                                    :disabled="closeDisabled">+ Add row</button>
                                        </div>
                                        <div class="space-y-2 border border-gray-200 rounded-lg p-3 bg-gray-50/50">
                                            <template x-for="(exp, i) in expenses" :key="i">
                                                <div class="flex gap-2 items-center">
                                                    <input type="number" step="0.01" min="0" :name="'expenses['+i+'][amount]'" x-model="expenses[i].amount"
                                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Amount (TZS)"
                                                           :disabled="closeDisabled">
                                                    <input type="text" :name="'expenses['+i+'][description]'" x-model="expenses[i].description"
                                                           class="flex-[2] px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Description"
                                                           :disabled="closeDisabled">
                                                    <button type="button" @click="removeExpense(i)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Remove" :disabled="closeDisabled">✕</button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount of loan given (TZS)</label>
                                        <input type="number" name="loans_given" step="0.01" min="0" x-model="loansGiven"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                               :disabled="closeDisabled" placeholder="0.00">
                                    </div>
                                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-sm" x-show="expectedClosing !== null && !closeDisabled">
                                        <p class="text-slate-600 font-medium">Expected closing = Opening + Collected − Expenses − Loans</p>
                                        <p class="text-gray-900 font-bold tabular-nums mt-0.5" x-text="'TZS ' + (expectedClosing != null ? Number(expectedClosing).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00')"></p>
                                    </div>
                                    <template x-if="closingMismatch">
                                        <div class="p-3 rounded-lg border-2 border-amber-400 bg-amber-50 text-sm text-amber-900">
                                            <p class="font-bold">Balance mismatch detected</p>
                                            <p class="mt-1">Expected closing: <span class="tabular-nums font-medium" x-text="'TZS ' + Number(closingMismatch.expected).toLocaleString('en-US', {minimumFractionDigits: 2})"></span></p>
                                            <p>Actual closing (Cash + Mobile): <span class="tabular-nums font-medium" x-text="'TZS ' + Number(closingMismatch.actual).toLocaleString('en-US', {minimumFractionDigits: 2})"></span></p>
                                            <p>Difference: <span class="tabular-nums font-bold" :class="closingMismatch.diff > 0 ? 'text-green-700' : 'text-red-700'" x-text="(closingMismatch.diff > 0 ? 'Higher than expected by ' : 'Lower than expected by ') + 'TZS ' + Math.abs(closingMismatch.diff).toLocaleString('en-US', {minimumFractionDigits: 2})"></span></p>
                                            <p class="text-amber-700 text-xs mt-1">You may still save for tracking purposes.</p>
                                        </div>
                                    </template>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Closing cash (TZS) *</label>
                                            <p class="text-xs text-slate-500 mb-0.5">Actual closing = Cash + Mobile; compare with expected above.</p>
                                            <input type="number" name="closing_cash" step="0.01" min="0" x-model="closingCash" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                                   :disabled="closeDisabled" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Closing mobile wallet (TZS) *</label>
                                            <p class="text-xs text-slate-500 mb-0.5">Actual closing = Cash + Mobile.</p>
                                            <input type="number" name="closing_mobile_wallet" step="0.01" min="0" x-model="closingMobile" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                                   :disabled="closeDisabled" placeholder="0.00">
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-500" x-show="closingCash !== '' || closingMobile !== ''">Actual closing total: <span class="tabular-nums font-medium text-gray-700" x-text="'TZS ' + Number(actualClosing).toLocaleString('en-US', {minimumFractionDigits: 2})"></span></p>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                        <textarea name="closing_notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                                  :disabled="closeDisabled" placeholder="Optional notes"></textarea>
                                    </div>
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button type="button" @click="closeModalOpen = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                                        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50 disabled:pointer-events-none"
                                                :disabled="closeDisabled">Save closing</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Record & Report table: by date (newest first), minimal columns; click row for full summary -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white">
                        <h3 class="text-base font-semibold text-gray-900">Record & Report</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Sorted by date (newest first). Click a row to open the day summary.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th scope="col" class="sticky left-0 z-10 bg-slate-50 px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Opening</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Variance</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Closing</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recordsWithVariance as $row)
                                    @php
                                        $r = $row->record;
                                        $vTotal = $row->variance_total;
                                        $expensesTotal = collect($r->expenses ?? [])->sum('amount');
                                        $calculatedClosing = ($r->opening_cash ?? 0) + ($r->opening_mobile_wallet ?? 0) + ($r->amount_collected ?? 0) - $expensesTotal - ($r->loans_given ?? 0);
                                    @endphp
                                    <tr class="cursor-pointer hover:bg-slate-50/90 active:bg-slate-100 transition-colors border-b border-gray-50"
                                        data-record-id="{{ $r->id }}"
                                        @click="selectedRecord = window.dailyTillRecordDetails && $event.currentTarget.dataset.recordId ? (window.dailyTillRecordDetails[$event.currentTarget.dataset.recordId] || null) : null; detailModalOpen = true">
                                        <td class="sticky left-0 z-[1] bg-white hover:bg-slate-50/90 active:bg-slate-100 px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap border-r border-gray-100">{{ $r->record_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-right tabular-nums text-gray-800">
                                            TZS {{ number_format(($r->opening_cash ?? 0) + ($r->opening_mobile_wallet ?? 0), 0) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            @if($vTotal !== null)
                                                @if(abs($vTotal) < 0.01)
                                                    <span class="text-gray-500 text-xs font-medium">OK</span>
                                                @elseif($vTotal < 0)
                                                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-red-50 text-red-700">Deficit</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700">Excess</span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right tabular-nums text-gray-900 font-medium">
                                            @if($r->closing_cash !== null || $r->closing_mobile_wallet !== null)
                                                TZS {{ number_format(($r->closing_cash ?? 0) + ($r->closing_mobile_wallet ?? 0), 0) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-12 text-center text-gray-500 text-sm">No records yet. Use Open Day to record opening amounts.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Days with abnormalities (opening or closing mismatch) -->
                <div class="mt-8 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-amber-50/50">
                        <h3 class="text-base font-semibold text-gray-900">Days with abnormalities</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Days where opening did not match previous closing, or recorded closing did not match expected (Opening + Collected − Expenses − Loans).</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recordsWithAbnormalities as $row)
                                    @php
                                        $r = $row->record;
                                        $types = [];
                                        if ($row->opening_mismatch) $types[] = 'Opening variance';
                                        if ($row->closing_mismatch) $types[] = 'Closing mismatch';
                                    @endphp
                                    <tr class="cursor-pointer hover:bg-slate-50/80"
                                        data-record-id="{{ $r->id }}"
                                        @click="selectedRecord = window.dailyTillRecordDetails && $event.currentTarget.dataset.recordId ? (window.dailyTillRecordDetails[$event.currentTarget.dataset.recordId] || null) : null; detailModalOpen = true">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $r->record_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @foreach($types as $t)
                                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $t === 'Opening variance' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800' }}">{{ $t }}</span>
                                            @endforeach
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            @if($row->opening_mismatch)
                                                <span>Opening total TZS {{ number_format(($r->opening_cash ?? 0) + ($r->opening_mobile_wallet ?? 0), 0) }} vs expected (prev. closing) TZS {{ number_format($row->expected_opening_total ?? 0, 0) }} — {{ $row->variance_total > 0 ? 'Excess' : 'Deficit' }} TZS {{ number_format(abs($row->variance_total), 0) }}</span>
                                            @endif
                                            @if($row->opening_mismatch && $row->closing_mismatch)<br>@endif
                                            @if($row->closing_mismatch)
                                                <span>Expected closing TZS {{ number_format($row->calculated_closing, 0) }} vs actual TZS {{ number_format($row->actual_closing, 0) }} — {{ ($row->actual_closing - $row->calculated_closing) > 0 ? 'Higher' : 'Lower' }} by TZS {{ number_format(abs($row->actual_closing - $row->calculated_closing), 0) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">No abnormalities. All days balance correctly.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Row detail modal: summary card at top + full details -->
                <div x-show="detailModalOpen" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     @keydown.escape.window="detailModalOpen = false">
                    <div class="flex min-h-screen items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50" @click="detailModalOpen = false"></div>
                        <div x-show="detailModalOpen && selectedRecord"
                             x-transition:enter="ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl max-h-[90vh] overflow-y-auto border border-gray-100"
                             @click.stop>
                            <div class="p-5" x-show="selectedRecord">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-bold text-gray-900" x-text="selectedRecord?.date || 'Day summary'"></h4>
                                    <button type="button" @click="detailModalOpen = false" class="p-2 -m-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">✕</button>
                                </div>

                                <!-- Summary card at top -->
                                <div class="grid grid-cols-2 gap-3 mb-5">
                                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-0.5">Opening</p>
                                        <p class="text-sm font-semibold tabular-nums text-gray-900 leading-tight" x-text="selectedRecord ? 'TZS ' + (Number(selectedRecord.opening_cash || 0) + Number(selectedRecord.opening_mobile_wallet || 0)).toLocaleString('en-US', {maximumFractionDigits: 0}) : '—'"></p>
                                        <p class="text-xs text-slate-500 mt-0.5" x-text="selectedRecord ? 'Cash ' + Number(selectedRecord.opening_cash || 0).toLocaleString('en-US', {maximumFractionDigits: 0}) + ' / Mobile ' + Number(selectedRecord.opening_mobile_wallet || 0).toLocaleString('en-US', {maximumFractionDigits: 0}) : ''"></p>
                                    </div>
                                    <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-3">
                                        <p class="text-xs font-medium text-emerald-600 uppercase tracking-wider mb-0.5">Collected</p>
                                        <p class="text-sm font-semibold tabular-nums text-emerald-800" x-text="selectedRecord?.amount_collected != null ? 'TZS ' + Number(selectedRecord.amount_collected).toLocaleString('en-US', {maximumFractionDigits: 0}) : '—'"></p>
                                    </div>
                                    <div class="rounded-xl bg-amber-50 border border-amber-100 p-3">
                                        <p class="text-xs font-medium text-amber-600 uppercase tracking-wider mb-0.5">Loans given</p>
                                        <p class="text-sm font-semibold tabular-nums text-amber-800" x-text="selectedRecord?.loans_given != null ? 'TZS ' + Number(selectedRecord.loans_given).toLocaleString('en-US', {maximumFractionDigits: 0}) : '—'"></p>
                                    </div>
                                    <div class="rounded-xl bg-rose-50 border border-rose-100 p-3">
                                        <p class="text-xs font-medium text-rose-600 uppercase tracking-wider mb-0.5">Expenses</p>
                                        <p class="text-sm font-semibold tabular-nums text-rose-800" x-text="selectedRecord?.expenses?.length ? 'TZS ' + (selectedRecord.expenses.reduce((s,e) => s + Number(e.amount||0), 0)).toLocaleString('en-US', {maximumFractionDigits: 0}) : '—'"></p>
                                    </div>
                                    <div class="col-span-2 rounded-xl bg-slate-100 border border-slate-200 p-3">
                                        <p class="text-xs font-medium text-slate-600 uppercase tracking-wider mb-0.5">Expected closing (Opening + Collected − Expenses − Loans)</p>
                                        <p class="text-sm font-bold tabular-nums text-gray-900" x-text="selectedRecord != null ? 'TZS ' + Number(selectedRecord.calculated_closing ?? 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '—'"></p>
                                        <p class="text-xs text-slate-600 mt-1">Actual closing (Cash + Mobile): <span class="tabular-nums font-medium" x-text="selectedRecord?.actual_closing != null ? 'TZS ' + Number(selectedRecord.actual_closing).toLocaleString('en-US', {minimumFractionDigits: 2}) : '—'"></span></p>
                                        <template x-if="selectedRecord && selectedRecord.actual_closing != null && Math.abs(Number(selectedRecord.actual_closing) - Number(selectedRecord.calculated_closing ?? 0)) > 0.01">
                                            <p class="text-xs mt-1 font-semibold text-amber-700">Balance mismatch: <span x-text="(Number(selectedRecord.actual_closing) - Number(selectedRecord.calculated_closing ?? 0)) > 0 ? 'Higher' : 'Lower'"></span> than expected by TZS <span class="tabular-nums" x-text="Math.abs(Number(selectedRecord.actual_closing) - Number(selectedRecord.calculated_closing ?? 0)).toLocaleString('en-US', {minimumFractionDigits: 2})"></span></p>
                                        </template>
                                        <p class="text-xs text-slate-500 mt-0.5">Cash / Mobile: <span x-text="selectedRecord != null ? 'TZS ' + Number(selectedRecord.closing_cash || 0).toLocaleString('en-US', {maximumFractionDigits: 0}) + ' / TZS ' + Number(selectedRecord.closing_mobile_wallet || 0).toLocaleString('en-US', {maximumFractionDigits: 0}) : '—'"></span></p>
                                    </div>
                                </div>

                                <!-- Full details section -->
                                <div class="border-t border-gray-100 pt-4">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Full details</p>
                                    <dl class="space-y-0 text-sm">
                                        <div class="flex justify-between py-2 border-b border-gray-50" x-show="selectedRecord?.expected_cash != null">
                                            <dt class="text-gray-500">Expected (prev. closing)</dt>
                                            <dd class="tabular-nums text-gray-600" x-text="selectedRecord && selectedRecord.expected_cash != null ? 'TZS ' + Number(selectedRecord.expected_cash).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' / ' + Number(selectedRecord.expected_mobile ?? 0).toLocaleString('en-US', {minimumFractionDigits: 2}) : ''"></dd>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-50" x-show="selectedRecord?.variance_total != null">
                                            <dt class="text-gray-500">Variance (opening vs prev. closing total)</dt>
                                            <dd class="tabular-nums" x-text="selectedRecord && selectedRecord.variance_total != null ? (Math.abs(selectedRecord.variance_total) < 0.01 ? 'OK' : (selectedRecord.variance_total > 0 ? 'Excess TZS ' : 'Deficit TZS ') + Math.abs(selectedRecord.variance_total).toLocaleString('en-US', {minimumFractionDigits: 2})) : ''"></dd>
                                        </div>
                                        <div class="py-2 border-b border-gray-50" x-show="selectedRecord?.expenses?.length">
                                            <dt class="text-gray-500 mb-1.5">Expense items</dt>
                                            <dd class="space-y-1">
                                                <template x-for="(exp, i) in (selectedRecord?.expenses || [])" :key="i">
                                                    <div class="flex justify-between text-xs py-1 bg-gray-50 rounded px-2">
                                                        <span x-text="exp.description || '—'"></span>
                                                        <span class="tabular-nums font-medium" x-text="'TZS ' + Number(exp.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})"></span>
                                                    </div>
                                                </template>
                                            </dd>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-50" x-show="selectedRecord?.opened_by">
                                            <dt class="text-gray-500">Opened by</dt>
                                            <dd x-text="selectedRecord?.opened_by || '—'"></dd>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-50" x-show="selectedRecord?.closed_by">
                                            <dt class="text-gray-500">Closed by</dt>
                                            <dd x-text="selectedRecord?.closed_by || '—'"></dd>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-50" x-show="selectedRecord?.branch">
                                            <dt class="text-gray-500">Branch</dt>
                                            <dd x-text="selectedRecord?.branch || '—'"></dd>
                                        </div>
                                        <div class="pt-2" x-show="selectedRecord?.opening_notes">
                                            <dt class="text-gray-500 mb-0.5 text-xs">Opening notes</dt>
                                            <dd class="text-gray-700 text-xs mt-0.5" x-text="selectedRecord?.opening_notes || ''"></dd>
                                        </div>
                                        <div class="pt-2" x-show="selectedRecord?.closing_notes">
                                            <dt class="text-gray-500 mb-0.5 text-xs">Closing notes</dt>
                                            <dd class="text-gray-700 text-xs mt-0.5" x-text="selectedRecord?.closing_notes || ''"></dd>
                                        </div>
                                    </dl>
                                </div>
                                <div class="mt-5 flex justify-end">
                                    <button type="button" @click="detailModalOpen = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium text-sm transition-colors">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>[x-cloak] { display: none !important; }</style>
</x-app-shell>
