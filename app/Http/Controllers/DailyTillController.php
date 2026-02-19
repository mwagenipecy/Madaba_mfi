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
            $varianceCash = $expectedCash !== null ? (float) $record->opening_cash - $expectedCash : null;
            $varianceMobile = $expectedMobile !== null ? (float) $record->opening_mobile_wallet - $expectedMobile : null;
            return (object) [
                'record' => $record,
                'expected_opening_cash' => $expectedCash,
                'expected_opening_mobile' => $expectedMobile,
                'variance_cash' => $varianceCash,
                'variance_mobile' => $varianceMobile,
            ];
        });

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

        return redirect()->route('daily-till.index')->with('success', 'Opening amounts recorded for ' . Carbon::parse($date)->format('M d, Y'));
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

        return redirect()->route('daily-till.index')->with('success', 'Closing amounts recorded for ' . Carbon::parse($date)->format('M d, Y'));
    }
}
