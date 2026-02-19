<x-app-shell title="Daily Till" header="Daily Till - Cash & Mobile Wallet">
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">{{ session('success') }}</div>
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
                        'variance_cash' => $row->variance_cash !== null ? (float) $row->variance_cash : null,
                        'variance_mobile' => $row->variance_mobile !== null ? (float) $row->variance_mobile : null,
                        'amount_collected' => $r->amount_collected !== null ? (float) $r->amount_collected : null,
                        'loans_given' => $r->loans_given !== null ? (float) $r->loans_given : null,
                        'expenses' => $r->expenses ?? [],
                        'expenses_total' => $expensesTotal,
                        'calculated_closing' => $calculated_closing,
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
                foreach ($recordsWithVariance as $row) {
                    $r = $row->record;
                    $openingByDate[$r->record_date->format('Y-m-d')] = [
                        'opening_cash' => (float) ($r->opening_cash ?? 0),
                        'opening_mobile_wallet' => (float) ($r->opening_mobile_wallet ?? 0),
                    ];
                }
            @endphp
            <script>window.dailyTillRecordDetails = @json($recordDetails); window.dailyTillOpeningByDate = @json($openingByDate);</script>
            <div x-data="{ openModalOpen: false, closeModalOpen: false, detailModalOpen: false, selectedRecord: null }">
                <!-- Top: Open Day & Close Day buttons -->
                <div class="flex flex-wrap items-center gap-3 mb-6">
                    <button type="button" @click="openModalOpen = true"
                            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Open Day
                    </button>
                    <button type="button" @click="closeModalOpen = true"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        Close Day
                    </button>
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
                                datesWithOpening: @js($datesWithOpeningRecord),
                                datesWithClosing: @js($datesWithClosingRecord),
                                get openDisabled() {
                                    if (!this.openSelectedDate) return false;
                                    return this.datesWithOpening.includes(this.openSelectedDate) || this.datesWithClosing.includes(this.openSelectedDate);
                                }
                             }">
                            <div class="p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Open Day</h4>
                                <p class="text-sm text-gray-600 mb-4">Select a date, then fill opening amounts. Recording is disabled if the selected date is already opened or closed.</p>
                                <form action="{{ route('daily-till.open') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                        <input type="date" name="record_date" x-model="openSelectedDate" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <template x-if="openSelectedDate && openDisabled">
                                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                                            <strong>Recording disabled.</strong> This date already has an opening record or the day is already closed.
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
                                            <input type="number" name="opening_cash" step="0.01" min="0" value="" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                   :disabled="openDisabled" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Opening mobile wallet (TZS) *</label>
                                            <input type="number" name="opening_mobile_wallet" step="0.01" min="0" value="" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                   :disabled="openDisabled" placeholder="0.00">
                                        </div>
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
                                        <p class="text-slate-600 font-medium">Closing = Opening + Collected − Expenses − Loans</p>
                                        <p class="text-gray-900 font-bold tabular-nums mt-0.5" x-text="'TZS ' + (expectedClosing != null ? Number(expectedClosing).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00')"></p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Running balance – Cash (TZS) *</label>
                                            <p class="text-xs text-slate-500 mb-0.5">For reference only; not used in closing calculation.</p>
                                            <input type="number" name="closing_cash" step="0.01" min="0" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                                   :disabled="closeDisabled" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Running balance – Mobile wallet (TZS) *</label>
                                            <p class="text-xs text-slate-500 mb-0.5">For reference only; not used in closing calculation.</p>
                                            <input type="number" name="closing_mobile_wallet" step="0.01" min="0" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                                   :disabled="closeDisabled" placeholder="0.00">
                                        </div>
                                    </div>
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
                                        $vCash = $row->variance_cash;
                                        $vMobile = $row->variance_mobile;
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
                                            @if($vCash !== null || $vMobile !== null)
                                                @if($vCash < 0 || $vMobile < 0)
                                                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-red-50 text-red-700">Deficit</span>
                                                @elseif($vCash > 0 || $vMobile > 0)
                                                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700">Excess</span>
                                                @else
                                                    <span class="text-gray-500 text-xs font-medium">OK</span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right tabular-nums text-gray-900 font-medium">
                                            TZS {{ number_format($calculatedClosing, 0) }}
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
                                        <p class="text-xs font-medium text-slate-600 uppercase tracking-wider mb-0.5">Closing (Opening + Collected − Expenses − Loans)</p>
                                        <p class="text-sm font-bold tabular-nums text-gray-900" x-text="selectedRecord != null ? 'TZS ' + Number(selectedRecord.calculated_closing ?? 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '—'"></p>
                                        <p class="text-xs text-slate-500 mt-1">Running balance (not used in calculation): <span x-text="selectedRecord != null ? 'Cash TZS ' + Number(selectedRecord.closing_cash || 0).toLocaleString('en-US', {maximumFractionDigits: 0}) + ' / Mobile TZS ' + Number(selectedRecord.closing_mobile_wallet || 0).toLocaleString('en-US', {maximumFractionDigits: 0}) : '—'"></span></p>
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
                                        <div class="flex justify-between py-2 border-b border-gray-50" x-show="selectedRecord?.variance_cash != null || selectedRecord?.variance_mobile != null">
                                            <dt class="text-gray-500">Variance</dt>
                                            <dd class="tabular-nums" x-text="selectedRecord ? (Number(selectedRecord.variance_cash ?? 0).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' / ' + Number(selectedRecord.variance_mobile ?? 0).toLocaleString('en-US', {minimumFractionDigits: 2})) : ''"></dd>
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
