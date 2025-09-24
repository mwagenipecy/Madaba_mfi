<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\GeneralLedger;

class ConvertMainAccountsToOrgLevel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:main-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert HQ-tied main accounts to organization-level (branch_id = null) and adjust related opening balance GL entries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Scanning for HQ-tied main accounts...');

        $query = Account::query()
            ->whereNull('parent_account_id') // main category
            ->whereNotNull('branch_id')
            ->where(function ($q) {
                $q->where('metadata->account_type', 'main_category')
                  ->orWhereNull('metadata');
            });

        $count = $query->count();
        if ($count === 0) {
            $this->info('No accounts need conversion.');
            return self::SUCCESS;
        }

        $this->info("Found {$count} main accounts to convert.");

        $affectedAccountIds = [];

        $query->chunkById(200, function ($accounts) use (&$affectedAccountIds) {
            foreach ($accounts as $account) {
                $affectedAccountIds[] = $account->id;
                $account->branch_id = null;
                $metadata = $account->metadata ?? [];
                $metadata['account_type'] = 'main_category';
                unset($metadata['is_hq_account']);
                $account->metadata = $metadata;
                $account->save();
            }
        });

        $this->info('Updated accounts to organization-level. Updating related opening balance GL entries...');

        if (!empty($affectedAccountIds)) {
            GeneralLedger::whereIn('account_id', $affectedAccountIds)
                ->where(function ($q) {
                    $q->where('reference_type', 'opening_balance')
                      ->orWhere('amount', 0.00);
                })
                ->update(['branch_id' => null]);
        }

        $this->info('Conversion complete.');
        return self::SUCCESS;
    }
}


