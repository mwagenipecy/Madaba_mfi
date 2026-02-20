<?php

namespace App\Http\Controllers;

use App\Models\DailyTillRecord;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyTillController extends Controller
{
    private function baseQuery()
    {
        $user = Auth::user();
        $orgId = $user->organization_id ?? \App\Models\Organization::first()?->id;
        $query = DailyTillRecord::where('organization_id', $orgId);
        if ($user->branch_id) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)->orWhereNull('branch_id');
            });
        }
        return $query;
    }

    /**
     * Daily Till: Open Day, Close Day, Record & Report (tabs).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $orgId = $user->organization_id ?? \App\Models\Organization::first()?->id;
        $today = Carbon::today()->format('Y-m-d');

        $todayRecord = $this->baseQuery()
            ->whereDate('record_date', $today)
            ->with(['openingRecordedBy', 'closingRecordedBy', 'branch'])
            ->first();

        $previousRecord = $this->baseQuery()
            ->whereDate('record_date', '<', $today)
            ->orderBy('record_date', 'desc')
            ->first();

        $records = $this->baseQuery()
            ->with(['openingRecordedBy', 'closingRecordedBy', 'branch'])
            ->orderBy('record_date', 'desc') // Newest first; table arranged by date
            ->limit(100)
            ->get();

        $recordsWithVariance = $records->map(function ($record, $index) use ($records) {
            $prev = $records->get($index + 1);
            $expectedCash = $prev ? (float) $prev->closing_cash : null;
            $expectedMobile = $prev ? (float) $prev->closing_mobile_wallet : null;
            $expectedTotal = ($prev && ($prev->closing_cash !== null || $prev->closing_mobile_wallet !== null))
                ? (float) ($prev->closing_cash ?? 0) + (float) ($prev->closing_mobile_wallet ?? 0)
                : null;
            $openingTotal = (float) ($record->opening_cash ?? 0) + (float) ($record->opening_mobile_wallet ?? 0);
            $varianceTotal = $expectedTotal !== null ? $openingTotal - $expectedTotal : null;
            $varianceCash = $expectedCash !== null ? (float) $record->opening_cash - $expectedCash : null;
            $varianceMobile = $expectedMobile !== null ? (float) $record->opening_mobile_wallet - $expectedMobile : null;

            $expensesTotal = collect($record->expenses ?? [])->sum('amount');
            $calculatedClosing = $openingTotal + (float) ($record->amount_collected ?? 0) - $expensesTotal - (float) ($record->loans_given ?? 0);
            $actualClosing = ($record->closing_cash !== null || $record->closing_mobile_wallet !== null)
                ? (float) ($record->closing_cash ?? 0) + (float) ($record->closing_mobile_wallet ?? 0)
                : null;
            $closingMismatch = $actualClosing !== null && abs($actualClosing - $calculatedClosing) >= 0.01;
            $openingMismatch = $varianceTotal !== null && abs($varianceTotal) >= 0.01;

            return (object) [
                'record' => $record,
                'expected_opening_cash' => $expectedCash,
                'expected_opening_mobile' => $expectedMobile,
                'expected_opening_total' => $expectedTotal,
                'variance_total' => $varianceTotal,
                'variance_cash' => $varianceCash,
                'variance_mobile' => $varianceMobile,
                'calculated_closing' => $calculatedClosing,
                'actual_closing' => $actualClosing,
                'opening_mismatch' => $openingMismatch,
                'closing_mismatch' => $closingMismatch,
            ];
        });

        $recordsWithAbnormalities = $recordsWithVariance->filter(fn ($row) => $row->opening_mismatch || $row->closing_mismatch)->values();

        $branches = Branch::where('organization_id', $orgId)->where('status', 'active')->orderBy('name')->get();

        // Dates that already have opening recorded (for Open Day modal: disable recording)
        $datesWithOpeningRecord = $this->baseQuery()
            ->whereNotNull('opening_recorded_at')
            ->get()
            ->map(fn ($r) => $r->record_date->format('Y-m-d'))
            ->values()
            ->all();
        // Dates that already have closing recorded (for Close Day modal: disable recording)
        $datesWithClosingRecord = $this->baseQuery()
            ->whereNotNull('closing_recorded_at')
            ->get()
            ->map(fn ($r) => $r->record_date->format('Y-m-d'))
            ->values()
            ->all();

        return view('daily-till.index', compact(
            'todayRecord',
            'previousRecord',
            'recordsWithVariance',
            'recordsWithAbnormalities',
            'today',
            'branches',
            'datesWithOpeningRecord',
            'datesWithClosingRecord'
        ));
    }

    /**
     * Open the day: record opening cash and mobile wallet.
     * If date is next day, suggested opening = previous day's closing.
     */
    public function openDay(Request $request)
    {
        $request->validate([
            'record_date' => 'required|date',
            'opening_cash' => 'required|numeric|min:0',
            'opening_mobile_wallet' => 'required|numeric|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'opening_notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $orgId = $user->organization_id ?? \App\Models\Organization::first()?->id;
        $branchId = $request->branch_id ?: $user->branch_id;
        $date = Carbon::parse($request->record_date)->format('Y-m-d');

        $prevDate = Carbon::parse($request->record_date)->subDay()->format('Y-m-d');
        $prevRecord = $this->baseQuery()
            ->where('record_date', $prevDate)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when(!$branchId, fn ($q) => $q->whereNull('branch_id'))
            ->first();

        if ($prevRecord && $prevRecord->closing_recorded_at === null) {
            return redirect()->route('daily-till.index')->with('error', 'Cannot open this day. The previous day (' . Carbon::parse($prevDate)->format('M d, Y') . ') is not closed. Close that day first using Close Day.');
        }

        $existing = DailyTillRecord::where('organization_id', $orgId)
            ->where('record_date', $date)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when(!$branchId, fn ($q) => $q->whereNull('branch_id'))
            ->first();

        if ($existing) {
            $existing->update([
                'opening_cash' => $request->opening_cash,
                'opening_mobile_wallet' => $request->opening_mobile_wallet,
                'opening_notes' => $request->opening_notes,
                'opening_recorded_by' => $user->id,
                'opening_recorded_at' => now(),
            ]);
            $record = $existing;
        } else {
            $record = DailyTillRecord::create([
                'organization_id' => $orgId,
                'branch_id' => $branchId,
                'record_date' => $date,
                'opening_cash' => $request->opening_cash,
                'opening_mobile_wallet' => $request->opening_mobile_wallet,
                'opening_recorded_by' => $user->id,
                'opening_recorded_at' => now(),
                'opening_notes' => $request->opening_notes,
            ]);
        }

        $redirect = redirect()->route('daily-till.index')->with('success', 'Opening amounts recorded for ' . Carbon::parse($date)->format('M d, Y'));

        $prevRecord = $this->baseQuery()
            ->where('record_date', Carbon::parse($date)->subDay()->format('Y-m-d'))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when(!$branchId, fn ($q) => $q->whereNull('branch_id'))
            ->first();

        if ($prevRecord) {
            $prevExpenses = collect($prevRecord->expenses ?? [])->sum('amount');
            $expectedOpening = (float) ($prevRecord->opening_cash ?? 0) + (float) ($prevRecord->opening_mobile_wallet ?? 0)
                + (float) ($prevRecord->amount_collected ?? 0) - $prevExpenses - (float) ($prevRecord->loans_given ?? 0);
            $enteredOpening = (float) $request->opening_cash + (float) $request->opening_mobile_wallet;
            if (abs($enteredOpening - $expectedOpening) >= 0.01) {
                $diff = $enteredOpening - $expectedOpening;
                $redirect->with('warning', sprintf(
                    'Wrong opening amount: expected TZS %s (yesterday\'s closing). You entered TZS %s. Difference: %sTZS %s. Record saved for audit.',
                    number_format($expectedOpening, 2),
                    number_format($enteredOpening, 2),
                    $diff >= 0 ? '+' : '',
                    number_format(abs($diff), 2)
                ));
            }
        }

        return $redirect;
    }

    /**
     * Close the day: record amount collected, expenses, loans given, remaining cash and mobile wallet.
     */
    public function closeDay(Request $request)
    {
        $request->validate([
            'record_date' => 'required|date',
            'amount_collected' => 'nullable|numeric|min:0',
            'loans_given' => 'nullable|numeric|min:0',
            'closing_cash' => 'required|numeric|min:0',
            'closing_mobile_wallet' => 'required|numeric|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'closing_notes' => 'nullable|string|max:1000',
            'expenses' => 'nullable|array',
            'expenses.*.amount' => 'required_with:expenses|numeric|min:0',
            'expenses.*.description' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $orgId = $user->organization_id ?? \App\Models\Organization::first()?->id;
        $branchId = $request->branch_id ?: $user->branch_id;
        $date = Carbon::parse($request->record_date)->format('Y-m-d');

        $record = DailyTillRecord::where('organization_id', $orgId)
            ->where('record_date', $date)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when(!$branchId, fn ($q) => $q->whereNull('branch_id'))
            ->first();

        if (!$record) {
            return redirect()->route('daily-till.index')->with('error', 'No opening record found for this date. Record opening first.');
        }

        $expenses = $request->expenses ?? [];
        $expenses = array_values(array_filter($expenses, fn ($e) => !empty($e['amount']) && (float) $e['amount'] > 0));
        $expenses = array_map(fn ($e) => [
            'amount' => (float) ($e['amount'] ?? 0),
            'description' => trim((string) ($e['description'] ?? '')),
        ], $expenses);

        $record->update([
            'amount_collected' => $request->filled('amount_collected') ? $request->amount_collected : null,
            'loans_given' => $request->filled('loans_given') ? $request->loans_given : null,
            'expenses' => $expenses ?: null,
            'closing_cash' => $request->closing_cash,
            'closing_mobile_wallet' => $request->closing_mobile_wallet,
            'closing_recorded_by' => $user->id,
            'closing_recorded_at' => now(),
            'closing_notes' => $request->closing_notes,
        ]);

        $openingTotal = (float) ($record->opening_cash ?? 0) + (float) ($record->opening_mobile_wallet ?? 0);
        $expensesTotal = collect($expenses)->sum('amount');
        $expectedClosing = $openingTotal + (float) ($request->amount_collected ?? 0) - $expensesTotal - (float) ($request->loans_given ?? 0);
        $actualClosing = (float) $request->closing_cash + (float) $request->closing_mobile_wallet;
        $diff = $actualClosing - $expectedClosing;

        $redirect = redirect()->route('daily-till.index')->with('success', 'Closing amounts recorded for ' . Carbon::parse($date)->format('M d, Y'));

        if (abs($diff) >= 0.01) {
            $redirect->with('warning', sprintf(
                'Balance mismatch: Actual closing (TZS %s) %s than expected (TZS %s) by TZS %s. Record saved for tracking.',
                number_format($actualClosing, 2),
                $diff > 0 ? 'higher' : 'lower',
                number_format($expectedClosing, 2),
                number_format(abs($diff), 2)
            ));
        }

        return $redirect;
    }
}
