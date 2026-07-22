<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Organization;
use App\Models\SmsLog;
use App\Services\SmsCampaignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SmsController extends Controller
{
    public function __construct(protected SmsCampaignService $campaigns)
    {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id ?? Organization::first()?->id;

        $filters = [
            'criteria' => $request->get('criteria', 'has_arrears'),
            'target_date' => $request->get('target_date', now()->toDateString()),
            'days_before' => (int) $request->get('days_before', 7),
            'days_after' => (int) $request->get('days_after', 0),
            'branch_id' => $request->get('branch_id'),
            'min_overdue_days' => $request->get('min_overdue_days'),
            'loan_status' => $request->get('loan_status', 'all'),
            'search' => $request->get('search'),
        ];

        $recipients = collect();
        if ($request->filled('preview') || $request->boolean('auto_preview')) {
            $recipients = $this->campaigns->resolveLoanRecipients((int) $organizationId, $filters);
        }

        $branches = Branch::where('organization_id', $organizationId)->orderBy('name')->get();
        $criteriaOptions = $this->criteriaOptions();
        $defaultMessage = $this->defaultMessageFor($filters['criteria']);

        return view('sms.index', compact(
            'filters',
            'recipients',
            'branches',
            'criteriaOptions',
            'defaultMessage'
        ));
    }

    public function sendBulk(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id ?? Organization::first()?->id;

        $validated = $request->validate([
            'criteria' => ['required', Rule::in(array_keys($this->criteriaOptions()))],
            'target_date' => 'nullable|date',
            'days_before' => 'nullable|integer|min:0|max:90',
            'days_after' => 'nullable|integer|min:0|max:90',
            'branch_id' => 'nullable|integer',
            'min_overdue_days' => 'nullable|integer|min:0',
            'loan_status' => 'nullable|string',
            'search' => 'nullable|string|max:100',
            'message' => 'required|string|min:3|max:640',
            'loan_ids' => 'required|array|min:1',
            'loan_ids.*' => 'integer',
        ]);

        $loans = $this->campaigns->resolveLoanRecipients((int) $organizationId, $validated)
            ->whereIn('id', $validated['loan_ids'])
            ->values();

        if ($loans->isEmpty()) {
            return back()->withInput()->withErrors(['message' => 'No matching recipients found for the selected criteria.']);
        }

        $result = $this->campaigns->sendToLoans(
            $loans,
            $validated['message'],
            $user,
            $validated['criteria'],
            'bulk'
        );

        return redirect()
            ->route('sms.reports', ['batch_id' => $result['batch_id']])
            ->with('success', "SMS batch completed: {$result['sent']} sent, {$result['failed']} failed, {$result['skipped']} skipped.");
    }

    public function manual(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id ?? Organization::first()?->id;

        $search = trim((string) $request->get('search', ''));
        $branchId = $request->get('branch_id');

        $clientsQuery = Client::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('phone_number')
            ->where('phone_number', '!=', '')
            ->with('branch')
            ->orderBy('first_name');

        if ($branchId) {
            $clientsQuery->where('branch_id', $branchId);
        }

        if ($search !== '') {
            $clientsQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('client_number', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $clients = $clientsQuery->paginate(30)->withQueryString();
        $branches = Branch::where('organization_id', $organizationId)->orderBy('name')->get();

        return view('sms.manual', compact('clients', 'branches', 'search', 'branchId'));
    }

    public function sendManual(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id ?? Organization::first()?->id;

        $validated = $request->validate([
            'message' => 'required|string|min:3|max:640',
            'client_ids' => 'nullable|array',
            'client_ids.*' => 'integer',
            'phone_numbers' => 'nullable|string',
        ]);

        $hasClients = ! empty($validated['client_ids']);
        $rawPhones = trim((string) ($validated['phone_numbers'] ?? ''));
        $hasPhones = $rawPhones !== '';

        if (! $hasClients && ! $hasPhones) {
            return back()->withInput()->withErrors([
                'client_ids' => 'Select at least one client or enter phone numbers.',
            ]);
        }

        $results = ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'batch_id' => null];

        if ($hasClients) {
            $clients = $this->campaigns->resolveClientsByIds((int) $organizationId, $validated['client_ids']);
            $clientResult = $this->campaigns->sendToClients(
                $clients,
                $validated['message'],
                $user,
                'manual_selection',
                'manual'
            );
            $results['sent'] += $clientResult['sent'];
            $results['failed'] += $clientResult['failed'];
            $results['skipped'] += $clientResult['skipped'];
            $results['batch_id'] = $clientResult['batch_id'];
        }

        if ($hasPhones) {
            $phones = preg_split('/[\s,;]+/', $rawPhones) ?: [];
            $phones = array_values(array_filter(array_map('trim', $phones)));
            $phoneResult = $this->campaigns->sendToPhones(
                $phones,
                $validated['message'],
                $user,
                'manual_numbers',
                'manual'
            );
            $results['sent'] += $phoneResult['sent'];
            $results['failed'] += $phoneResult['failed'];
            $results['skipped'] += $phoneResult['skipped'];
            $results['batch_id'] = $results['batch_id'] ?: $phoneResult['batch_id'];
        }

        return redirect()
            ->route('sms.reports', array_filter(['batch_id' => $results['batch_id']]))
            ->with('success', "SMS completed: {$results['sent']} sent, {$results['failed']} failed, {$results['skipped']} skipped.");
    }

    public function reports(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id ?? Organization::first()?->id;

        $query = SmsLog::query()
            ->with(['client', 'loan', 'sender', 'branch'])
            ->where('organization_id', $organizationId)
            ->latest();

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('source') && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        if ($request->filled('criteria') && $request->criteria !== 'all') {
            $query->where('criteria', $request->criteria);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('job_id', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('business_name', 'like', "%{$search}%")
                            ->orWhere('client_number', 'like', "%{$search}%");
                    });
            });
        }

        $summaryQuery = clone $query;
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'sent' => (clone $summaryQuery)->where('status', 'sent')->count(),
            'failed' => (clone $summaryQuery)->where('status', 'failed')->count(),
            'cost' => (clone $summaryQuery)->where('status', 'sent')->sum('cost'),
        ];

        $logs = $query->paginate(25)->withQueryString();
        $criteriaOptions = $this->criteriaOptions();

        return view('sms.reports', compact('logs', 'summary', 'criteriaOptions'));
    }

    /**
     * @return array<string, string>
     */
    protected function criteriaOptions(): array
    {
        return [
            'maturity_today' => 'Loan end date is today',
            'near_maturity' => 'Loan end date near a specific date',
            'due_today' => 'Installment due today',
            'near_due_date' => 'Installment due near a specific date',
            'has_arrears' => 'Has arrears',
            'overdue_loans' => 'Overdue loans',
            'active_loans' => 'Active loans',
            'all_with_phone' => 'All clients with active loans & phone',
        ];
    }

    protected function defaultMessageFor(string $criteria): string
    {
        return match ($criteria) {
            'maturity_today', 'near_maturity' => 'Habari {client_name}, mkopo wako {loan_number} unakamilika tarehe {maturity_date}. Tafadhali wasiliana nasi kwa malipo. Asante - WIBOOK.',
            'due_today', 'near_due_date' => 'Habari {client_name}, unakumbushwa malipo ya mkopo {loan_number}. Deni lililosalia: TZS {outstanding_balance}. Asante - WIBOOK.',
            'has_arrears', 'overdue_loans' => 'Habari {client_name}, mkopo {loan_number} una malimbikizo ya siku {overdue_days} (TZS {overdue_amount}). Tafadhali lipa haraka. Asante - WIBOOK.',
            default => 'Habari {client_name}, ujumbe kutoka WIBOOK kuhusu mkopo {loan_number}. Asante.',
        };
    }
}
