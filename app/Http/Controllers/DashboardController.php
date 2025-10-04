<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ExpenseRequest;
use App\Models\GeneralLedger;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Organization;
use App\Models\RealAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        // Get basic statistics
        $stats = $this->getBasicStats($organizationId, $branchId);
        
        // Get portfolio at risk data
        $parData = $this->getPARData($organizationId, $branchId);
        
        // Get monthly performance data
        $monthlyData = $this->getMonthlyPerformance($organizationId, $branchId);
        
        // Get loan status distribution
        $loanStatusDistribution = $this->getLoanStatusDistribution($organizationId, $branchId);
        
        // Get account balances
        $accountBalances = $this->getAccountBalances($organizationId, $branchId);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($organizationId, $branchId);
        
        // Get critical alerts
        $criticalAlerts = $this->getCriticalAlerts($organizationId, $branchId);
        
        // Get performance metrics
        $performanceMetrics = $this->getPerformanceMetrics($organizationId, $branchId);
        
        // Get branch performance (if user is admin)
        $branchPerformance = null;
        if ($user->role === 'admin') {
            $branchPerformance = $this->getBranchPerformance($organizationId);
        }

        // Get upcoming payments (3 days ago to 3 days ahead)
        $upcomingPayments = $this->getUpcomingPayments($organizationId, $branchId);

        return view('dashboard', compact(
            'stats',
            'parData',
            'monthlyData',
            'loanStatusDistribution',
            'accountBalances',
            'recentActivities',
            'criticalAlerts',
            'performanceMetrics',
            'branchPerformance',
            'upcomingPayments'
        ));
    }

    private function getBasicStats($organizationId, $branchId)
    {
        $query = Loan::where('organization_id', $organizationId);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $totalLoans = $query->count();
        $activeLoans = $query->where('status', 'active')->count();
        $overdueLoans = $query->where('status', 'overdue')->count();
        $totalClients = Client::where('organization_id', $organizationId)
            ->when($branchId, function($q) use ($branchId) {
                return $q->where('branch_id', $branchId);
            })->count();

        $totalPortfolio = $query->where('status', 'active')->sum('approved_amount');
        $totalDisbursed = $query->where('status', '!=', 'pending')->sum('approved_amount');

        return [
            'total_loans' => $totalLoans,
            'active_loans' => $activeLoans,
            'overdue_loans' => $overdueLoans,
            'total_clients' => $totalClients,
            'total_portfolio' => $totalPortfolio,
            'total_disbursed' => $totalDisbursed,
        ];
    }

    private function getPARData($organizationId, $branchId)
    {
        $par30 = $this->calculatePAR($organizationId, $branchId, 30);
        $par60 = $this->calculatePAR($organizationId, $branchId, 60);
        $par90 = $this->calculatePAR($organizationId, $branchId, 90);

        return [
            'par30' => $par30,
            'par60' => $par60,
            'par90' => $par90,
        ];
    }

    private function calculatePAR($organizationId, $branchId, $days)
    {
        $query = Loan::where('organization_id', $organizationId)
            ->where('status', 'active');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $loans = $query->with(['schedules' => function($q) use ($days) {
            $q->where('status', 'overdue')
              ->where('due_date', '<', Carbon::now()->subDays($days));
        }])->get();

        $overdueAmount = $loans->sum(function($loan) {
            return $loan->schedules->sum('total_amount');
        });

        $totalPortfolio = $query->sum('approved_amount');

        return $totalPortfolio > 0 ? ($overdueAmount / $totalPortfolio) * 100 : 0;
    }

    private function getMonthlyPerformance($organizationId, $branchId)
    {
        $months = [];
        $disbursements = [];
        $collections = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            // Disbursements
            $disbursementQuery = Loan::where('organization_id', $organizationId)
                ->where('status', '!=', 'pending')
                ->whereYear('disbursement_date', $date->year)
                ->whereMonth('disbursement_date', $date->month);

            if ($branchId) {
                $disbursementQuery->where('branch_id', $branchId);
            }

            $disbursements[] = $disbursementQuery->sum('approved_amount');

            // Collections
            $collectionQuery = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
                $q->where('organization_id', $organizationId);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })->where('status', 'paid')
            ->whereYear('paid_date', $date->year)
            ->whereMonth('paid_date', $date->month);

            $collections[] = $collectionQuery->sum('paid_amount');
        }

        return [
            'months' => $months,
            'disbursements' => $disbursements,
            'collections' => $collections,
        ];
    }

    private function getLoanStatusDistribution($organizationId, $branchId)
    {
        $query = Loan::where('organization_id', $organizationId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
    }

    private function getAccountBalances($organizationId, $branchId)
    {
        $query = Account::where('organization_id', $organizationId)
            ->where('account_type_id', '!=', null)
            ->with(['accountType', 'mappedRealAccounts']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $accounts = $query->get();

        $balances = [
            'banks' => [],
            'mobile_money' => [],
            'cash' => [],
            'total_balance' => 0
        ];

        foreach ($accounts as $account) {
            $balance = $account->mappedRealAccounts->sum('last_balance') ?? 0;
            $balances['total_balance'] += $balance;

            if (str_contains(strtolower($account->name), 'bank')) {
                $balances['banks'][] = [
                    'name' => $account->name,
                    'balance' => $balance,
                    'account_type' => $account->accountType->name ?? 'Bank Account'
                ];
            } elseif (str_contains(strtolower($account->name), 'mobile') || 
                     str_contains(strtolower($account->name), 'mno')) {
                $balances['mobile_money'][] = [
                    'name' => $account->name,
                    'balance' => $balance,
                    'account_type' => $account->accountType->name ?? 'Mobile Money'
                ];
            } else {
                $balances['cash'][] = [
                    'name' => $account->name,
                    'balance' => $balance,
                    'account_type' => $account->accountType->name ?? 'Cash Account'
                ];
            }
        }

        return $balances;
    }

    private function getRecentActivities($organizationId, $branchId)
    {
        $activities = collect();

        // Recent loan disbursements
        $recentLoans = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->with(['client', 'loanProduct'])
            ->latest('disbursement_date')
            ->limit(5)
            ->get()
            ->map(function($loan) {
                return [
                    'type' => 'disbursement',
                    'title' => 'Loan Disbursed',
                    'description' => "{$loan->client->first_name} {$loan->client->last_name} - " . number_format($loan->approved_amount, 2),
                    'date' => $loan->disbursement_date,
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'
                ];
            });

        $activities = $activities->merge($recentLoans);

        // Recent payments
        $recentPayments = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'paid')
        ->with(['loan.client'])
        ->latest('paid_date')
        ->limit(5)
        ->get()
        ->map(function($schedule) {
            return [
                'type' => 'payment',
                'title' => 'Payment Received',
                'description' => "{$schedule->loan->client->first_name} {$schedule->loan->client->last_name} - " . number_format($schedule->paid_amount, 2),
                'date' => $schedule->paid_date,
                'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'
            ];
        });

        $activities = $activities->merge($recentPayments);

        return $activities->sortByDesc('date')->take(10);
    }

    private function getCriticalAlerts($organizationId, $branchId)
    {
        $alerts = [];

        // Overdue loans
        $overdueCount = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('status', 'overdue')
            ->count();

        if ($overdueCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Overdue Loans',
                'message' => "You have {$overdueCount} overdue loans that need attention.",
                'count' => $overdueCount
            ];
        }

        // High PAR
        $par30 = $this->calculatePAR($organizationId, $branchId, 30);
        if ($par30 > 5) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'High Portfolio at Risk',
                'message' => "Your PAR 30 is {$par30}%, which is above the recommended 5%.",
                'count' => $par30
            ];
        }

        // Low account balances
        $totalBalance = Account::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->with('mappedRealAccounts')
            ->get()
            ->sum(function($account) {
                return $account->mappedRealAccounts->sum('last_balance');
            });

        if ($totalBalance < 10000) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Account Balance',
                'message' => "Your total account balance is low: " . number_format($totalBalance, 2),
                'count' => $totalBalance
            ];
        }

        return $alerts;
    }

    private function getPerformanceMetrics($organizationId, $branchId)
    {
        // This month vs last month
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $thisMonthDisbursements = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('status', '!=', 'pending')
            ->where('disbursement_date', '>=', $thisMonth)
            ->sum('approved_amount');

        $lastMonthDisbursements = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('status', '!=', 'pending')
            ->whereBetween('disbursement_date', [$lastMonth, $thisMonth])
            ->sum('approved_amount');

        $disbursementGrowth = $lastMonthDisbursements > 0 
            ? (($thisMonthDisbursements - $lastMonthDisbursements) / $lastMonthDisbursements) * 100 
            : 0;

        return [
            'disbursement_growth' => $disbursementGrowth,
            'this_month_disbursements' => $thisMonthDisbursements,
            'last_month_disbursements' => $lastMonthDisbursements,
        ];
    }

    private function getBranchPerformance($organizationId)
    {
        $branches = Branch::where('organization_id', $organizationId)->get();

        return $branches->map(function($branch) use ($organizationId) {
            $activeLoans = Loan::where('organization_id', $organizationId)
                ->where('branch_id', $branch->id)
                ->where('status', 'active')
                ->count();

            $totalPortfolio = Loan::where('organization_id', $organizationId)
                ->where('branch_id', $branch->id)
                ->where('status', 'active')
                ->sum('approved_amount');

            $totalClients = Client::where('organization_id', $organizationId)
                ->where('branch_id', $branch->id)
                ->count();

            return [
                'name' => $branch->name,
                'active_loans' => $activeLoans,
                'total_portfolio' => $totalPortfolio,
                'total_clients' => $totalClients,
            ];
        });
    }

    private function getUpcomingPayments($organizationId, $branchId)
    {
        $startDate = Carbon::now()->subDays(3);
        $endDate = Carbon::now()->addDays(3);

        $query = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->whereBetween('due_date', [$startDate, $endDate])
        ->whereIn('status', ['pending', 'overdue', 'partial'])
        ->with(['loan.client', 'loan.loanProduct'])
        ->orderBy('due_date')
        ->get();

        return $query->map(function($schedule) {
            $daysDiff = Carbon::now()->diffInDays($schedule->due_date, false);
            
            return [
                'id' => $schedule->id,
                'client_name' => "{$schedule->loan->client->first_name} {$schedule->loan->client->last_name}",
                'client_phone' => $schedule->loan->client->phone_number ?? 'N/A',
                'loan_product' => $schedule->loan->loanProduct->name ?? 'N/A',
                'installment_number' => $schedule->installment_number,
                'due_date' => $schedule->due_date,
                'total_amount' => $schedule->total_amount,
                'paid_amount' => $schedule->paid_amount,
                'outstanding_amount' => $schedule->outstanding_amount,
                'status' => $schedule->status,
                'days_diff' => $daysDiff,
                'is_overdue' => $daysDiff < 0,
                'is_due_soon' => $daysDiff >= 0 && $daysDiff <= 3,
                'is_due_today' => $daysDiff === 0,
            ];
        });
    }
}
