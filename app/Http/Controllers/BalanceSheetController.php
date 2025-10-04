<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\RealAccount;
use App\Models\GeneralLedger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BalanceSheetController extends Controller
{
    /**
     * Display the balance sheet.
     */
    public function index(Request $request)
    {
        $organizationId = auth()->user()->organization_id;
        $branchId = $request->get('branch_id');
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        // Get balance sheet data
        $balanceSheetData = $this->prepareBalanceSheet($organizationId, $branchId, $asOfDate);

        // Get branches for filter
        $branches = \App\Models\Branch::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('accounts.balance-sheet', compact('balanceSheetData', 'branches', 'branchId', 'asOfDate'));
    }

    /**
     * Prepare balance sheet data.
     */
    private function prepareBalanceSheet($organizationId, $branchId = null, $asOfDate = null)
    {
        $asOfDate = $asOfDate ? Carbon::parse($asOfDate) : now();

        // Get all account types
        $accountTypes = AccountType::orderBy('name')->get();

        // Get accounts with their balances
        $query = Account::with(['accountType', 'mappedRealAccounts'])
            ->where('organization_id', $organizationId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $accounts = $query->get();

        // Calculate balances for each account
        $accountBalances = [];
        foreach ($accounts as $account) {
            $balance = $this->calculateAccountBalance($account, $asOfDate);
            if ($balance != 0) {
                $accountBalances[] = [
                    'account' => $account,
                    'balance' => $balance,
                    'account_type' => $account->accountType->name,
                    'account_type_id' => $account->accountType->id,
                ];
            }
        }

        // Group by account type
        $groupedBalances = collect($accountBalances)->groupBy('account_type_id');

        // Prepare balance sheet sections
        $assets = $this->prepareAssets($groupedBalances, $asOfDate);
        $liabilities = $this->prepareLiabilities($groupedBalances, $asOfDate);
        $equity = $this->prepareEquity($groupedBalances, $asOfDate);

        // Calculate totals
        $totalAssets = $assets['total'];
        $totalLiabilities = $liabilities['total'];
        $totalEquity = $equity['total'];
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return [
            'as_of_date' => $asOfDate,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totals' => [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
                'is_balanced' => abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01,
            ],
            'organization' => auth()->user()->organization,
            'branch' => $branchId ? \App\Models\Branch::find($branchId) : null,
        ];
    }

    /**
     * Calculate account balance as of specific date.
     */
    private function calculateAccountBalance($account, $asOfDate)
    {
        // Get mapped real account balances
        $realAccountBalance = $account->mappedRealAccounts->sum('last_balance');

        // Get general ledger entries up to the as of date
        // Use the balance_after from the last transaction entry for accurate balance
        $lastTransaction = GeneralLedger::where('account_id', $account->id)
            ->where('transaction_date', '<=', $asOfDate)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            return $lastTransaction->balance_after;
        }

        // If no transactions, return the account's current balance
        return $account->balance;
    }

    /**
     * Prepare assets section.
     */
    private function prepareAssets($groupedBalances, $asOfDate)
    {
        $assets = [
            'current_assets' => [],
            'fixed_assets' => [],
            'other_assets' => [],
            'total' => 0,
        ];

        // Define asset account types (you may need to adjust these based on your account types)
        $currentAssetTypes = ['Cash', 'Bank', 'Accounts Receivable', 'Inventory', 'Prepaid Expenses'];
        $fixedAssetTypes = ['Equipment', 'Furniture', 'Vehicles', 'Buildings', 'Land'];
        $otherAssetTypes = ['Investments', 'Intangible Assets', 'Other Assets'];

        foreach ($groupedBalances as $accountTypeId => $accounts) {
            $accountType = $accounts->first()['account_type'];
            $total = $accounts->sum('balance');

            if (in_array($accountType, $currentAssetTypes)) {
                $assets['current_assets'][] = [
                    'type' => $accountType,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            } elseif (in_array($accountType, $fixedAssetTypes)) {
                $assets['fixed_assets'][] = [
                    'type' => $accountType,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            } else {
                $assets['other_assets'][] = [
                    'type' => $accountType,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            }
        }

        // Calculate totals
        $assets['current_assets_total'] = collect($assets['current_assets'])->sum('total');
        $assets['fixed_assets_total'] = collect($assets['fixed_assets'])->sum('total');
        $assets['other_assets_total'] = collect($assets['other_assets'])->sum('total');
        $assets['total'] = $assets['current_assets_total'] + $assets['fixed_assets_total'] + $assets['other_assets_total'];

        return $assets;
    }

    /**
     * Prepare liabilities section.
     */
    private function prepareLiabilities($groupedBalances, $asOfDate)
    {
        $liabilities = [
            'current_liabilities' => [],
            'long_term_liabilities' => [],
            'total' => 0,
        ];

        // Define liability account types
        $currentLiabilityTypes = ['Accounts Payable', 'Accrued Expenses', 'Short-term Loans', 'Tax Payable'];
        $longTermLiabilityTypes = ['Long-term Loans', 'Bonds Payable', 'Mortgage Payable'];

        foreach ($groupedBalances as $accountTypeId => $accounts) {
            $accountType = $accounts->first()['account_type'];
            $total = $accounts->sum('balance');

            if (in_array($accountType, $currentLiabilityTypes)) {
                $liabilities['current_liabilities'][] = [
                    'type' => $accountType,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            } elseif (in_array($accountType, $longTermLiabilityTypes)) {
                $liabilities['long_term_liabilities'][] = [
                    'type' => $accountType,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            }
        }

        // Calculate totals
        $liabilities['current_liabilities_total'] = collect($liabilities['current_liabilities'])->sum('total');
        $liabilities['long_term_liabilities_total'] = collect($liabilities['long_term_liabilities'])->sum('total');
        $liabilities['total'] = $liabilities['current_liabilities_total'] + $liabilities['long_term_liabilities_total'];

        return $liabilities;
    }

    /**
     * Prepare equity section.
     */
    private function prepareEquity($groupedBalances, $asOfDate)
    {
        $equity = [
            'owner_equity' => [],
            'retained_earnings' => [],
            'total' => 0,
        ];

        // Define equity account types
        $ownerEquityTypes = ['Owner Capital', 'Share Capital', 'Paid-in Capital'];
        $retainedEarningsTypes = ['Retained Earnings', 'Current Earnings', 'Profit/Loss'];

        foreach ($groupedBalances as $accountTypeId => $accounts) {
            $accountType = $accounts->first()['account_type'];
            $total = $accounts->sum('balance');

            if (in_array($accountType, $ownerEquityTypes)) {
                $equity['owner_equity'][] = [
                    'type' => $accountType,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            } elseif (in_array($accountType, $retainedEarningsTypes)) {
                $equity['retained_earnings'][] = [
                    'type' => $accountType,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            }
        }

        // Calculate totals
        $equity['owner_equity_total'] = collect($equity['owner_equity'])->sum('total');
        $equity['retained_earnings_total'] = collect($equity['retained_earnings'])->sum('total');
        $equity['total'] = $equity['owner_equity_total'] + $equity['retained_earnings_total'];

        return $equity;
    }

    /**
     * Export balance sheet as PDF.
     */
    public function export(Request $request)
    {
        $organizationId = auth()->user()->organization_id;
        $branchId = $request->get('branch_id');
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        $balanceSheetData = $this->prepareBalanceSheet($organizationId, $branchId, $asOfDate);

        // You can implement PDF export here using a package like dompdf or tcpdf
        // For now, return a view that can be printed
        return view('accounts.balance-sheet-print', compact('balanceSheetData'));
    }
}