<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Loan;
use App\Models\SmsLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class SmsCampaignService
{
    public function __construct(protected SmsService $sms)
    {
    }

    /**
     * Resolve loan-based recipients for bulk SMS filters.
     *
     * @param  array{
     *     criteria?: string,
     *     target_date?: string|null,
     *     days_before?: int|null,
     *     days_after?: int|null,
     *     branch_id?: int|null,
     *     min_overdue_days?: int|null,
     *     loan_status?: string|null,
     *     search?: string|null
     * }  $filters
     */
    public function resolveLoanRecipients(int $organizationId, array $filters): Collection
    {
        $criteria = $filters['criteria'] ?? 'has_arrears';
        $today = Carbon::today();

        $query = Loan::query()
            ->with(['client', 'branch', 'loanProduct'])
            ->where('organization_id', $organizationId)
            ->whereHas('client', function ($q) {
                $q->whereNotNull('phone_number')
                    ->where('phone_number', '!=', '');
            });

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['loan_status']) && $filters['loan_status'] !== 'all') {
            $query->where('status', $filters['loan_status']);
        } else {
            $query->whereIn('status', ['active', 'overdue', 'disbursed']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('business_name', 'like', "%{$search}%")
                            ->orWhere('client_number', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        match ($criteria) {
            'maturity_today' => $query->whereDate('maturity_date', $today),
            'due_today' => $query->whereHas('schedules', function ($q) use ($today) {
                $q->whereDate('due_date', $today)
                    ->whereIn('status', ['pending', 'partial', 'overdue']);
            }),
            'near_maturity' => $this->applyNearDateFilter(
                $query,
                'maturity_date',
                $filters['target_date'] ?? $today->toDateString(),
                (int) ($filters['days_before'] ?? 7),
                (int) ($filters['days_after'] ?? 0)
            ),
            'near_due_date' => $query->whereHas('schedules', function ($q) use ($filters, $today) {
                $target = Carbon::parse($filters['target_date'] ?? $today->toDateString())->startOfDay();
                $before = (int) ($filters['days_before'] ?? 7);
                $after = (int) ($filters['days_after'] ?? 0);
                $q->whereDate('due_date', '>=', $target->copy()->subDays($before))
                    ->whereDate('due_date', '<=', $target->copy()->addDays($after))
                    ->whereIn('status', ['pending', 'partial', 'overdue']);
            }),
            'has_arrears' => $query->where(function ($q) {
                $q->where('overdue_days', '>', 0)
                    ->orWhere('overdue_amount', '>', 0)
                    ->orWhere('status', 'overdue');
            }),
            'active_loans' => $query->whereIn('status', ['active', 'disbursed']),
            'overdue_loans' => $query->where(function ($q) {
                $q->where('status', 'overdue')
                    ->orWhere('overdue_days', '>', 0);
            }),
            'all_with_phone' => null,
            default => $query->where(function ($q) {
                $q->where('overdue_days', '>', 0)
                    ->orWhere('overdue_amount', '>', 0);
            }),
        };

        if (! empty($filters['min_overdue_days'])) {
            $query->where('overdue_days', '>=', (int) $filters['min_overdue_days']);
        }

        return $query->orderBy('maturity_date')
            ->get()
            ->unique(fn (Loan $loan) => $loan->client_id)
            ->values();
    }

    /**
     * @param  array<int, int>  $clientIds
     */
    public function resolveClientsByIds(int $organizationId, array $clientIds): Collection
    {
        return Client::query()
            ->where('organization_id', $organizationId)
            ->whereIn('id', $clientIds)
            ->whereNotNull('phone_number')
            ->where('phone_number', '!=', '')
            ->with(['branch'])
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Send personalized SMS to loan recipients and persist logs.
     *
     * @param  Collection<int, Loan>  $loans
     * @return array{batch_id: string, sent: int, failed: int, skipped: int}
     */
    public function sendToLoans(
        Collection $loans,
        string $messageTemplate,
        User $user,
        string $criteria,
        string $source = 'bulk'
    ): array {
        $batchId = (string) Str::uuid();
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($loans as $loan) {
            $client = $loan->client;
            $phone = $client?->phone_number;

            if (! $client || ! $phone || ! $this->sms->normalizePhone($phone)) {
                $skipped++;
                continue;
            }

            $content = $this->personalize($messageTemplate, $client, $loan);
            $result = $this->dispatchAndLog([
                'batch_id' => $batchId,
                'organization_id' => $user->organization_id,
                'branch_id' => $loan->branch_id ?? $client->branch_id,
                'client_id' => $client->id,
                'loan_id' => $loan->id,
                'sent_by' => $user->id,
                'recipient' => $phone,
                'content' => $content,
                'criteria' => $criteria,
                'source' => $source,
            ]);

            $result === 'sent' ? $sent++ : $failed++;
        }

        return ['batch_id' => $batchId, 'sent' => $sent, 'failed' => $failed, 'skipped' => $skipped];
    }

    /**
     * @param  Collection<int, Client>  $clients
     * @return array{batch_id: string, sent: int, failed: int, skipped: int}
     */
    public function sendToClients(
        Collection $clients,
        string $messageTemplate,
        User $user,
        string $criteria = 'manual_selection',
        string $source = 'manual'
    ): array {
        $batchId = (string) Str::uuid();
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($clients as $client) {
            $phone = $client->phone_number;

            if (! $phone || ! $this->sms->normalizePhone($phone)) {
                $skipped++;
                continue;
            }

            $content = $this->personalize($messageTemplate, $client);
            $result = $this->dispatchAndLog([
                'batch_id' => $batchId,
                'organization_id' => $user->organization_id,
                'branch_id' => $client->branch_id,
                'client_id' => $client->id,
                'loan_id' => null,
                'sent_by' => $user->id,
                'recipient' => $phone,
                'content' => $content,
                'criteria' => $criteria,
                'source' => $source,
            ]);

            $result === 'sent' ? $sent++ : $failed++;
        }

        return ['batch_id' => $batchId, 'sent' => $sent, 'failed' => $failed, 'skipped' => $skipped];
    }

    /**
     * @param  array<int, string>  $phones
     * @return array{batch_id: string, sent: int, failed: int, skipped: int}
     */
    public function sendToPhones(
        array $phones,
        string $message,
        User $user,
        string $criteria = 'manual_numbers',
        string $source = 'manual'
    ): array {
        $batchId = (string) Str::uuid();
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        $unique = [];
        foreach ($phones as $phone) {
            $normalized = $this->sms->normalizePhone((string) $phone);
            if ($normalized) {
                $unique[$normalized] = (string) $phone;
            } else {
                $skipped++;
            }
        }

        foreach ($unique as $normalized => $original) {
            $client = Client::query()
                ->where('organization_id', $user->organization_id)
                ->where(function ($q) use ($original, $normalized) {
                    $q->where('phone_number', $original)
                        ->orWhere('phone_number', $normalized)
                        ->orWhere('phone_number', '0'.substr($normalized, 3));
                })
                ->first();

            $result = $this->dispatchAndLog([
                'batch_id' => $batchId,
                'organization_id' => $user->organization_id,
                'branch_id' => $client?->branch_id,
                'client_id' => $client?->id,
                'loan_id' => null,
                'sent_by' => $user->id,
                'recipient' => $original,
                'content' => $message,
                'criteria' => $criteria,
                'source' => $source,
            ]);

            $result === 'sent' ? $sent++ : $failed++;
        }

        return ['batch_id' => $batchId, 'sent' => $sent, 'failed' => $failed, 'skipped' => $skipped];
    }

    public function personalize(string $template, Client $client, ?Loan $loan = null): string
    {
        $branchName = $loan?->branch?->name
            ?? $client->branch?->name
            ?? 'Madaba';

        $replacements = [
            '{client_name}' => $client->display_name,
            '{name}' => $client->display_name,
            '{client_number}' => $client->client_number ?? '',
            '{phone}' => $client->phone_number ?? '',
            '{branch_name}' => $branchName,
            '{loan_number}' => $loan?->loan_number ?? '',
            '{overdue_days}' => (string) ($loan?->overdue_days ?? 0),
            '{overdue_amount}' => number_format((float) ($loan?->overdue_amount ?? 0), 0),
            '{outstanding_balance}' => number_format((float) ($loan?->outstanding_balance ?? 0), 0),
            '{maturity_date}' => $loan?->maturity_date?->format('d/m/Y') ?? '',
            '{loan_amount}' => number_format((float) ($loan?->approved_amount ?? $loan?->loan_amount ?? 0), 0),
            '{approved_amount}' => number_format((float) ($loan?->approved_amount ?? $loan?->loan_amount ?? 0), 0),
        ];

        return strtr($template, $replacements);
    }

    /**
     * Send one transactional SMS to a client and persist the log.
     *
     * @return array{status: string, batch_id: string|null, error?: string}
     */
    public function notifyClient(
        Client $client,
        string $messageTemplate,
        ?User $user = null,
        ?Loan $loan = null,
        string $criteria = 'transactional',
        string $source = 'system'
    ): array {
        $phone = $client->phone_number;

        if (! $phone || ! $this->sms->normalizePhone($phone)) {
            return ['status' => 'skipped', 'batch_id' => null, 'error' => 'Client has no valid phone number.'];
        }

        if ($loan) {
            $loan->loadMissing('branch');
        }
        $client->loadMissing('branch');

        $batchId = (string) Str::uuid();
        $content = $this->personalize($messageTemplate, $client, $loan);

        $result = $this->dispatchAndLog([
            'batch_id' => $batchId,
            'organization_id' => $client->organization_id,
            'branch_id' => $loan?->branch_id ?? $client->branch_id,
            'client_id' => $client->id,
            'loan_id' => $loan?->id,
            'sent_by' => $user?->id,
            'recipient' => $phone,
            'content' => $content,
            'criteria' => $criteria,
            'source' => $source,
        ]);

        return ['status' => $result, 'batch_id' => $batchId];
    }

    public function clientWelcomeMessage(Client $client): string
    {
        $client->loadMissing('branch');
        $branchName = $client->branch?->name ?: 'Madaba';

        return "Habari {$client->display_name} karibu Madaba Microfinance tawi la {$branchName}, umepokea sms hii sababu umeunganishwa kwa huduma ulio omba, asante na karibu sana";
    }

    public function loanApprovedMessage(Client $client, Loan $loan): string
    {
        $client->loadMissing('branch');
        $loan->loadMissing('branch');

        $amount = number_format((float) ($loan->approved_amount ?? $loan->loan_amount ?? 0), 0);
        $maturityDate = $loan->maturity_date
            ? $loan->maturity_date->format('d/m/Y')
            : 'itakayotolewa';
        $branchName = $loan->branch?->name
            ?? $client->branch?->name
            ?: 'Keko';

        return "Habari {$client->display_name},\n\n"
            ."Ombi lako la mkopo wa TZS {$amount} limekubaliwa na umepokea fedha kutoka Madaba Microfinance tawi la {$branchName}.\n\n"
            ."Mwisho wa marejesho: {$maturityDate}\n\n"
            ."Kumbuka: Kuchelewa kurejesha ni kosa. Utatozwa TZS 2,000 kwa kila siku utakayochelewesha malipo.\n\n"
            .'Asante.';
    }

    /**
     * @param  array<string, mixed>  $logData
     */
    protected function dispatchAndLog(array $logData): string
    {
        $senderId = (string) config('services.briq.default_sender', 'WIBOOK');
        $normalized = $this->sms->normalizePhone($logData['recipient']);

        $log = SmsLog::create([
            ...$logData,
            'recipient' => $normalized ?? $logData['recipient'],
            'sender_id' => $senderId,
            'status' => 'pending',
        ]);

        try {
            $response = $this->sms->sendTo($logData['recipient'], $logData['content'], $senderId);

            $log->update([
                'status' => 'sent',
                'job_id' => $response['job_id'] ?? null,
                'provider_status' => $response['status'] ?? null,
                'cost' => data_get($response, 'stats.cost'),
                'meta' => $response,
            ]);

            return 'sent';
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return 'failed';
        }
    }

    protected function applyNearDateFilter($query, string $column, string $targetDate, int $daysBefore, int $daysAfter)
    {
        $target = Carbon::parse($targetDate)->startOfDay();

        return $query->whereDate($column, '>=', $target->copy()->subDays(max(0, $daysBefore)))
            ->whereDate($column, '<=', $target->copy()->addDays(max(0, $daysAfter)));
    }
}
