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

        // Use AccountingService for consistent balance calculation
        $accountingService = app(\App\Services\AccountingService::class);

        // Get accounts with their balances - EXCLUDE external accounts
        $query = Account::with(['accountType', 'mappedRealAccounts'])
            ->where('organization_id', $organizationId)
            ->where(function($q) {
                // Exclude external accounts
                $q->where('account_classification', '!=', 'external')
                  ->where(function($subQ) {
                      $subQ->whereNull('account_number')
                           ->orWhere('account_number', 'not like', 'EXT-%');
                  });
            });

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $accounts = $query->get();

        // Calculate balances for each account and group by category
        $assets = [];
        $liabilities = [];
        $equity = [];
        $revenue = [];
        $expenses = [];

        foreach ($accounts as $account) {
            // Skip if account type is missing
            if (!$account->accountType) {
                continue;
            }

            $balance = $accountingService->calculateAccountBalance($account, $asOfDate);
            $category = strtolower($account->accountType->category ?? '');
            $accountTypeName = $account->accountType->name;

            // Include all accounts (even zero balances) to ensure balance sheet balances
            // Zero balances will be filtered out in display if needed

            $accountData = [
                'account' => $account,
                'balance' => $balance,
                'account_type' => $accountTypeName,
                'account_type_id' => $account->accountType->id,
            ];

            // Group by category
            switch ($category) {
                case 'asset':
                    $assets[] = $accountData;
                    break;
                case 'liability':
                    $liabilities[] = $accountData;
                    break;
                case 'equity':
                    $equity[] = $accountData;
                    break;
                case 'income':
                case 'revenue':
                    $revenue[] = $accountData;
                    break;
                case 'expense':
                    $expenses[] = $accountData;
                    break;
            }
        }

        // Prepare balance sheet sections
        $assetsSection = $this->prepareAssets(collect($assets), $asOfDate);
        $liabilitiesSection = $this->prepareLiabilities(collect($liabilities), $asOfDate);
        $equitySection = $this->prepareEquity(collect($equity), $asOfDate, collect($revenue), collect($expenses));

        // Calculate totals
        $totalAssets = $assetsSection['total'];
        $totalLiabilities = $liabilitiesSection['total'];
        $totalEquity = $equitySection['total'];
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return [
            'as_of_date' => $asOfDate,
            'assets' => $assetsSection,
            'liabilities' => $liabilitiesSection,
            'equity' => $equitySection,
            'totals' => [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
                'is_balanced' => abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01,
                'difference' => $totalAssets - $totalLiabilitiesAndEquity,
            ],
            'organization' => auth()->user()->organization,
            'branch' => $branchId ? \App\Models\Branch::find($branchId) : null,
        ];
    }


    /**
     * Prepare assets section.
     */
    private function prepareAssets($assetsCollection, $asOfDate)
    {
        $assets = [
            'current_assets' => [],
            'fixed_assets' => [],
            'other_assets' => [],
            'total' => 0,
        ];

        // Group by account type name
        $groupedByType = $assetsCollection->groupBy('account_type');

        foreach ($groupedByType as $accountTypeName => $accounts) {
            $total = $accounts->sum('balance');
            
            // Categorize based on account type name (you can adjust these)
            $accountTypeLower = strtolower($accountTypeName);
            
            if (str_contains($accountTypeLower, 'cash') || 
                str_contains($accountTypeLower, 'bank') || 
                str_contains($accountTypeLower, 'receivable') ||
                str_contains($accountTypeLower, 'inventory') ||
                str_contains($accountTypeLower, 'prepaid')) {
                $assets['current_assets'][] = [
                    'type' => $accountTypeName,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            } elseif (str_contains($accountTypeLower, 'equipment') || 
                      str_contains($accountTypeLower, 'furniture') ||
                      str_contains($accountTypeLower, 'vehicle') ||
                      str_contains($accountTypeLower, 'building') ||
                      str_contains($accountTypeLower, 'land') ||
                      str_contains($accountTypeLower, 'fixed')) {
                $assets['fixed_assets'][] = [
                    'type' => $accountTypeName,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            } else {
                $assets['other_assets'][] = [
                    'type' => $accountTypeName,
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
    private function prepareLiabilities($liabilitiesCollection, $asOfDate)
    {
        $liabilities = [
            'current_liabilities' => [],
            'long_term_liabilities' => [],
            'total' => 0,
        ];

        // Group by account type name
        $groupedByType = $liabilitiesCollection->groupBy('account_type');

        foreach ($groupedByType as $accountTypeName => $accounts) {
            $total = $accounts->sum('balance');
            $accountTypeLower = strtolower($accountTypeName);
            
            if (str_contains($accountTypeLower, 'long') || 
                str_contains($accountTypeLower, 'mortgage') ||
                str_contains($accountTypeLower, 'bond')) {
                $liabilities['long_term_liabilities'][] = [
                    'type' => $accountTypeName,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            } else {
                // Default to current liabilities
                $liabilities['current_liabilities'][] = [
                    'type' => $accountTypeName,
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
    private function prepareEquity($equityCollection, $asOfDate, $revenueCollection = null, $expensesCollection = null)
    {
        $equity = [
            'owner_equity' => [],
            'retained_earnings' => [],
            'net_income' => 0,
            'total' => 0,
        ];

        // Group equity accounts by account type name
        $groupedByType = $equityCollection->groupBy('account_type');

        foreach ($groupedByType as $accountTypeName => $accounts) {
            $total = $accounts->sum('balance');
            $accountTypeLower = strtolower($accountTypeName);
            
            if (str_contains($accountTypeLower, 'retained') || 
                str_contains($accountTypeLower, 'earnings') ||
                str_contains($accountTypeLower, 'profit') ||
                str_contains($accountTypeLower, 'loss')) {
                $equity['retained_earnings'][] = [
                    'type' => $accountTypeName,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            } else {
                // Default to owner equity (capital, share capital, etc.)
                $equity['owner_equity'][] = [
                    'type' => $accountTypeName,
                    'accounts' => $accounts->toArray(),
                    'total' => $total,
                ];
            }
        }

        // Calculate net income from revenue and expenses (if provided)
        if ($revenueCollection && $expensesCollection) {
            $totalRevenue = $revenueCollection->sum('balance');
            $totalExpenses = $expensesCollection->sum('balance');
            $equity['net_income'] = $totalRevenue - $totalExpenses;
            
            // Add net income to retained earnings if not zero
            if (abs($equity['net_income']) > 0.01) {
                $equity['retained_earnings'][] = [
                    'type' => 'Net Income (Revenue - Expenses)',
                    'accounts' => [],
                    'total' => $equity['net_income'],
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