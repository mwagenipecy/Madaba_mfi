<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Client;
use App\Models\ExpenseRequest;
use App\Models\GeneralLedger;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        // Get basic statistics
        $totalLoans = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->count();

        $activeLoans = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('status', 'active')
            ->count();

        $totalClients = Client::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->count();

        $totalExpenses = ExpenseRequest::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('status', 'completed')
            ->sum('amount');

        // Get loan portfolio value
        $totalPortfolioValue = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('status', 'active')
            ->sum('approved_amount');

        // Get overdue loans count
        $overdueLoans = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId)
              ->where('status', 'active');
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'overdue')
        ->where('due_date', '<', Carbon::now())
        ->count();

        // Get PAR data
        $par30 = $this->calculatePAR($organizationId, $branchId, 30);
        $par60 = $this->calculatePAR($organizationId, $branchId, 60);
        $par90 = $this->calculatePAR($organizationId, $branchId, 90);

        // Get monthly disbursements for chart (last 6 months)
        $monthlyDisbursements = $this->getMonthlyDisbursements($organizationId, $branchId);
        
        // Get monthly collections for chart (last 6 months)
        $monthlyCollections = $this->getMonthlyCollections($organizationId, $branchId);

        // Get loan status distribution
        $loanStatusDistribution = $this->getLoanStatusDistribution($organizationId, $branchId);

        // Get expense trends (last 6 months)
        $monthlyExpenses = $this->getMonthlyExpenses($organizationId, $branchId);

        // Get recent activities
        $recentActivities = $this->getRecentActivities($organizationId, $branchId);

        // Calculate collection rate
        $collectionRate = $this->calculateCollectionRate($organizationId, $branchId);

        // Get account balances
        $accountBalances = $this->getAccountBalances($organizationId, $branchId);

        // Get critical activities and alerts
        $criticalActivities = $this->getCriticalActivities($organizationId, $branchId);

        // Get performance metrics
        $performanceMetrics = $this->getPerformanceMetrics($organizationId, $branchId);

        // Get account type distribution
        $accountTypeDistribution = $this->getAccountTypeDistribution($organizationId, $branchId);

        // Get branch performance
        $branchPerformance = $this->getBranchPerformance($organizationId, $branchId);

        return view('reports.index', compact(
            'totalLoans', 'activeLoans', 'totalClients', 'totalExpenses',
            'totalPortfolioValue', 'overdueLoans', 'par30', 'par60', 'par90',
            'monthlyDisbursements', 'monthlyCollections', 'loanStatusDistribution',
            'monthlyExpenses', 'recentActivities', 'collectionRate',
            'accountBalances', 'criticalActivities', 'performanceMetrics',
            'accountTypeDistribution', 'branchPerformance'
        ));
    }

    /**
     * Calculate Portfolio at Risk (PAR) for given days
     */
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

        $totalOutstanding = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId)
              ->where('status', 'active');
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'pending')
        ->sum('total_amount');

        return $totalOutstanding > 0 ? ($overdueAmount / $totalOutstanding) * 100 : 0;
    }

    /**
     * Get monthly disbursements for chart
     */
    private function getMonthlyDisbursements($organizationId, $branchId)
    {
        $query = Loan::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->where('disbursement_date', '>=', Carbon::now()->subMonths(6));

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->selectRaw('DATE_FORMAT(disbursement_date, "%Y-%m") as month, SUM(approved_amount) as amount')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get monthly collections for chart
     */
    private function getMonthlyCollections($organizationId, $branchId)
    {
        $query = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'paid')
        ->where('paid_date', '>=', Carbon::now()->subMonths(6));

        return $query->selectRaw('DATE_FORMAT(paid_date, "%Y-%m") as month, SUM(paid_amount) as amount')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get loan status distribution
     */
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

    /**
     * Get monthly expenses for chart
     */
    private function getMonthlyExpenses($organizationId, $branchId)
    {
        $query = ExpenseRequest::where('organization_id', $organizationId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', Carbon::now()->subMonths(6));

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->selectRaw('DATE_FORMAT(completed_at, "%Y-%m") as month, SUM(amount) as amount')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get recent activities
     */
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
                    'type' => 'loan_disbursement',
                    'description' => "New loan disbursed to {$loan->client->first_name} {$loan->client->last_name}",
                    'amount' => $loan->approved_amount,
                    'date' => $loan->disbursement_date,
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                    'color' => 'text-green-600'
                ];
            });

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
        ->map(function($payment) {
                return [
                    'type' => 'payment',
                    'description' => "Payment received from {$payment->loan->client->first_name} {$payment->loan->client->last_name}",
                    'amount' => $payment->paid_amount,
                    'date' => $payment->paid_date,
                    'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                    'color' => 'text-blue-600'
                ];
        });

        return $activities->merge($recentLoans)->merge($recentPayments)
            ->sortByDesc('date')
            ->take(10);
    }

    /**
     * Calculate collection rate
     */
    private function calculateCollectionRate($organizationId, $branchId)
    {
        $totalDue = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'pending')
        ->sum('total_amount');

        $totalPaid = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'paid')
        ->sum('paid_amount');

        $totalAmount = $totalDue + $totalPaid;
        
        return $totalAmount > 0 ? ($totalPaid / $totalAmount) * 100 : 0;
    }

    /**
     * Get account balances for banks and mobile money
     */
    private function getAccountBalances($organizationId, $branchId)
    {
        $query = Account::where('organization_id', $organizationId)
            ->where('account_type_id', '!=', null)
            ->with(['accountType', 'realAccounts']);

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
            $balance = $account->realAccounts->sum('last_balance') ?? 0;
            $balances['total_balance'] += $balance;

            if (str_contains(strtolower($account->name), 'bank')) {
                $balances['banks'][] = [
                    'name' => $account->name,
                    'balance' => $balance,
                    'account_type' => $account->accountType->name ?? 'Bank Account'
                ];
            } elseif (str_contains(strtolower($account->name), 'mobile') || 
                     str_contains(strtolower($account->name), 'mno') ||
                     str_contains(strtolower($account->name), 'mpesa') ||
                     str_contains(strtolower($account->name), 'tigo') ||
                     str_contains(strtolower($account->name), 'airtel')) {
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

    /**
     * Get critical activities and alerts
     */
    private function getCriticalActivities($organizationId, $branchId)
    {
        $activities = collect();

        // Overdue loans
        $overdueCount = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId)
              ->where('status', 'active');
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'overdue')
        ->where('due_date', '<', Carbon::now())
        ->count();

        if ($overdueCount > 0) {
            $activities->push([
                'type' => 'overdue_loans',
                'title' => 'Overdue Loans',
                'message' => "{$overdueCount} loans are overdue and require immediate attention",
                'severity' => 'critical',
                'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
                'color' => 'text-red-600',
                'bg_color' => 'bg-red-50'
            ]);
        }

        // High PAR
        $par30 = $this->calculatePAR($organizationId, $branchId, 30);
        if ($par30 > 5) {
            $activities->push([
                'type' => 'high_par',
                'title' => 'High Portfolio at Risk',
                'message' => "PAR 30 is {$par30}% - above recommended threshold of 5%",
                'severity' => 'warning',
                'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
                'color' => 'text-yellow-600',
                'bg_color' => 'bg-yellow-50'
            ]);
        }

        // Low account balances
        $lowBalanceAccounts = Account::where('organization_id', $organizationId)
            ->whereHas('realAccounts', function($q) {
                $q->where('balance', '<', 100000); // Less than 100,000 TZS
            })
            ->with(['realAccounts', 'accountType'])
            ->get();

        if ($lowBalanceAccounts->count() > 0) {
            $activities->push([
                'type' => 'low_balance',
                'title' => 'Low Account Balances',
                'message' => "{$lowBalanceAccounts->count()} accounts have low balances",
                'severity' => 'info',
                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                'color' => 'text-blue-600',
                'bg_color' => 'bg-blue-50'
            ]);
        }

        // Pending approvals
        $pendingApprovals = \App\Models\Approval::where('organization_id', $organizationId)
            ->where('status', 'pending')
            ->count();

        if ($pendingApprovals > 0) {
            $activities->push([
                'type' => 'pending_approvals',
                'title' => 'Pending Approvals',
                'message' => "{$pendingApprovals} items are pending approval",
                'severity' => 'info',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'text-purple-600',
                'bg_color' => 'bg-purple-50'
            ]);
        }

        return $activities;
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics($organizationId, $branchId)
    {
        $metrics = [];

        // Loan disbursement rate (this month vs last month)
        $thisMonthDisbursements = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->whereMonth('disbursement_date', Carbon::now()->month)
            ->whereYear('disbursement_date', Carbon::now()->year)
            ->sum('approved_amount');

        $lastMonthDisbursements = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->whereMonth('disbursement_date', Carbon::now()->subMonth()->month)
            ->whereYear('disbursement_date', Carbon::now()->subMonth()->year)
            ->sum('approved_amount');

        $disbursementGrowth = $lastMonthDisbursements > 0 ? 
            (($thisMonthDisbursements - $lastMonthDisbursements) / $lastMonthDisbursements) * 100 : 0;

        $metrics['disbursement_growth'] = $disbursementGrowth;

        // Collection efficiency
        $totalDue = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'pending')
        ->sum('total_amount');

        $totalPaid = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'paid')
        ->sum('paid_amount');

        $collectionEfficiency = ($totalDue + $totalPaid) > 0 ? 
            ($totalPaid / ($totalDue + $totalPaid)) * 100 : 0;

        $metrics['collection_efficiency'] = $collectionEfficiency;

        // Average loan size
        $averageLoanSize = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('status', 'active')
            ->avg('approved_amount');

        $metrics['average_loan_size'] = $averageLoanSize ?? 0;

        // Client growth rate
        $thisMonthClients = Client::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $lastMonthClients = Client::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        $clientGrowth = $lastMonthClients > 0 ? 
            (($thisMonthClients - $lastMonthClients) / $lastMonthClients) * 100 : 0;

        $metrics['client_growth'] = $clientGrowth;

        return $metrics;
    }

    /**
     * Get account type distribution
     */
    private function getAccountTypeDistribution($organizationId, $branchId)
    {
        $query = Account::join('real_accounts', 'accounts.id', '=', 'real_accounts.account_id')
            ->where('accounts.organization_id', $organizationId);

        if ($branchId) {
            $query->where('accounts.branch_id', $branchId);
        }

        return $query->selectRaw('accounts.account_type_id, SUM(real_accounts.last_balance) as total_balance')
            ->groupBy('accounts.account_type_id')
            ->with('accountType')
            ->get();
    }

    /**
     * Get branch performance
     */
    private function getBranchPerformance($organizationId, $branchId)
    {
        if ($branchId) {
            // If specific branch, return that branch's performance
            $branch = \App\Models\Branch::find($branchId);
            if (!$branch) return collect();

            return collect([$this->calculateBranchMetrics($branch, $organizationId)]);
        }

        // Get all branches performance
        $branches = \App\Models\Branch::where('organization_id', $organizationId)->get();
        
        return $branches->map(function($branch) use ($organizationId) {
            return $this->calculateBranchMetrics($branch, $organizationId);
        });
    }

    /**
     * Calculate branch metrics
     */
    private function calculateBranchMetrics($branch, $organizationId)
    {
        $loans = Loan::where('organization_id', $organizationId)
            ->where('branch_id', $branch->id)
            ->get();

        $activeLoans = $loans->where('status', 'active')->count();
        $totalPortfolio = $loans->where('status', 'active')->sum('approved_amount');
        $clients = Client::where('organization_id', $organizationId)
            ->where('branch_id', $branch->id)
            ->count();

        return [
            'branch' => $branch,
            'active_loans' => $activeLoans,
            'total_portfolio' => $totalPortfolio,
            'clients' => $clients
        ];
    }

    /**
     * Weekly payments report - who to pay in the next week
     */
    public function weeklyPayments(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        $startDate = $request->get('start_date', Carbon::now()->startOfWeek());
        $endDate = $request->get('end_date', Carbon::now()->endOfWeek());

        // Get payments due in the week
        $query = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId)
              ->where('status', 'active');
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->whereBetween('due_date', [$startDate, $endDate])
        ->whereIn('status', ['pending', 'overdue', 'partial'])
        ->with(['loan.client', 'loan.loanProduct', 'loan.branch']);

        $weeklyPayments = $query->orderBy('due_date')->get();

        // Group by due date
        $paymentsByDate = $weeklyPayments->groupBy(function($item) {
            return Carbon::parse($item->due_date)->format('Y-m-d');
        });

        // Get summary statistics
        $totalAmount = $weeklyPayments->sum('total_amount');
        $paidAmount = $weeklyPayments->sum('paid_amount');
        $outstandingAmount = $weeklyPayments->sum('outstanding_amount');
        $overdueCount = $weeklyPayments->where('status', 'overdue')->count();
        $pendingCount = $weeklyPayments->where('status', 'pending')->count();
        $partialCount = $weeklyPayments->where('status', 'partial')->count();

        // Get daily breakdown
        $dailyBreakdown = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayPayments = $weeklyPayments->where('due_date', $date->format('Y-m-d'));
            $dailyBreakdown[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'count' => $dayPayments->count(),
                'total_amount' => $dayPayments->sum('total_amount'),
                'paid_amount' => $dayPayments->sum('paid_amount'),
                'outstanding_amount' => $dayPayments->sum('outstanding_amount'),
            ];
        }

        // Get top clients by amount
        $topClients = $weeklyPayments->groupBy('loan.client_id')
            ->map(function($clientPayments) {
                $client = $clientPayments->first()->loan->client;
                return [
                    'client_name' => "{$client->first_name} {$client->last_name}",
                    'total_amount' => $clientPayments->sum('total_amount'),
                    'outstanding_amount' => $clientPayments->sum('outstanding_amount'),
                    'payment_count' => $clientPayments->count(),
                ];
            })
            ->sortByDesc('total_amount')
            ->take(10);

        return view('reports.weekly-payments', compact(
            'weeklyPayments', 
            'paymentsByDate', 
            'totalAmount', 
            'startDate', 
            'endDate',
            'paidAmount',
            'outstandingAmount',
            'overdueCount',
            'pendingCount',
            'partialCount',
            'dailyBreakdown',
            'topClients'
        ));
    }

    /**
     * Arrears report - people behind on payments
     */
    public function arrears(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        $daysOverdue = $request->get('days_overdue', 30);

        // Get overdue payments
        $query = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId)
              ->where('status', 'active');
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'overdue')
        ->where('due_date', '<', Carbon::now()->subDays($daysOverdue))
        ->with(['loan.client', 'loan.loanProduct', 'loan.branch']);

        $arrears = $query->orderBy('due_date')->get();

        // Group by client
        $arrearsByClient = $arrears->groupBy('loan.client_id');

        // Get summary statistics
        $totalArrears = $arrears->sum('total_amount');
        $totalOutstanding = $arrears->sum('outstanding_amount');
        $totalPaid = $arrears->sum('paid_amount');
        $clientCount = $arrearsByClient->count();
        $paymentCount = $arrears->count();

        // Get arrears by days overdue
        $arrearsByDays = $arrears->groupBy(function($item) {
            $daysOverdue = Carbon::now()->diffInDays($item->due_date);
            if ($daysOverdue <= 30) return '1-30 days';
            if ($daysOverdue <= 60) return '31-60 days';
            if ($daysOverdue <= 90) return '61-90 days';
            return '90+ days';
        });

        // Get top clients by arrears amount
        $topClients = $arrearsByClient->map(function($clientArrears) {
            $client = $clientArrears->first()->loan->client;
            $totalAmount = $clientArrears->sum('total_amount');
            $outstandingAmount = $clientArrears->sum('outstanding_amount');
            $daysOverdue = $clientArrears->map(function($arrear) {
                return Carbon::now()->diffInDays($arrear->due_date);
            })->max();

            return [
                'client_name' => "{$client->first_name} {$client->last_name}",
                'client_phone' => $client->phone_number ?? 'N/A',
                'total_amount' => $totalAmount,
                'outstanding_amount' => $outstandingAmount,
                'days_overdue' => $daysOverdue,
                'payment_count' => $clientArrears->count(),
            ];
        })
        ->sortByDesc('outstanding_amount')
        ->take(20);

        // Get arrears by branch (if admin)
        $arrearsByBranch = collect();
        if ($user->role === 'admin') {
            $arrearsByBranch = $arrears->groupBy('loan.branch.name')
                ->map(function($branchArrears) {
                    return [
                        'branch_name' => $branchArrears->first()->loan->branch->name ?? 'Unknown',
                        'total_amount' => $branchArrears->sum('total_amount'),
                        'outstanding_amount' => $branchArrears->sum('outstanding_amount'),
                        'client_count' => $branchArrears->groupBy('loan.client_id')->count(),
                        'payment_count' => $branchArrears->count(),
                    ];
                })
                ->sortByDesc('outstanding_amount');
        }

        return view('reports.arrears', compact(
            'arrears', 
            'arrearsByClient', 
            'totalArrears', 
            'daysOverdue',
            'totalOutstanding',
            'totalPaid',
            'clientCount',
            'paymentCount',
            'arrearsByDays',
            'topClients',
            'arrearsByBranch'
        ));
    }

    /**
     * Portfolio at Risk (PAR) report
     */
    public function par(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        $parDays = $request->get('par_days', 30);

        $query = Loan::where('organization_id', $organizationId)
            ->where('status', 'active');
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $loans = $query->with(['client', 'loanProduct', 'branch', 'schedules' => function($q) {
            $q->where('status', 'overdue');
        }])->get();

        // Calculate PAR for each loan
        $parLoans = $loans->filter(function($loan) use ($parDays) {
            $overdueAmount = $loan->schedules->where('due_date', '<', Carbon::now()->subDays($parDays))->sum('amount_due');
            return $overdueAmount > 0;
        })->map(function($loan) use ($parDays) {
            $overdueAmount = $loan->schedules->where('due_date', '<', Carbon::now()->subDays($parDays))->sum('amount_due');
            $totalOutstanding = $loan->schedules->where('status', 'pending')->sum('amount_due');
            $parPercentage = $totalOutstanding > 0 ? ($overdueAmount / $totalOutstanding) * 100 : 0;
            
            return [
                'loan' => $loan,
                'overdue_amount' => $overdueAmount,
                'total_outstanding' => $totalOutstanding,
                'par_percentage' => $parPercentage,
                'days_overdue' => $loan->schedules->where('status', 'overdue')->min('due_date') ? 
                    Carbon::now()->diffInDays(Carbon::parse($loan->schedules->where('status', 'overdue')->min('due_date'))) : 0
            ];
        })->sortByDesc('par_percentage');

        $totalParAmount = $parLoans->sum('overdue_amount');
        $totalOutstanding = $parLoans->sum('total_outstanding');
        $overallParPercentage = $totalOutstanding > 0 ? ($totalParAmount / $totalOutstanding) * 100 : 0;

        // Get PAR by product
        $parByProduct = $parLoans->groupBy('loan.loanProduct.name')
            ->map(function($productLoans) {
                $totalAmount = $productLoans->sum('overdue_amount');
                $totalOutstanding = $productLoans->sum('total_outstanding');
                return [
                    'product_name' => $productLoans->first()['loan']->loanProduct->name ?? 'Unknown',
                    'par_amount' => $totalAmount,
                    'total_outstanding' => $totalOutstanding,
                    'par_percentage' => $totalOutstanding > 0 ? ($totalAmount / $totalOutstanding) * 100 : 0,
                    'loan_count' => $productLoans->count(),
                ];
            })
            ->sortByDesc('par_amount');

        // Get PAR by branch (if admin)
        $parByBranch = collect();
        if ($user->role === 'admin') {
            $parByBranch = $parLoans->groupBy('loan.branch.name')
                ->map(function($branchLoans) {
                    $totalAmount = $branchLoans->sum('overdue_amount');
                    $totalOutstanding = $branchLoans->sum('total_outstanding');
                    return [
                        'branch_name' => $branchLoans->first()['loan']->branch->name ?? 'Unknown',
                        'par_amount' => $totalAmount,
                        'total_outstanding' => $totalOutstanding,
                        'par_percentage' => $totalOutstanding > 0 ? ($totalAmount / $totalOutstanding) * 100 : 0,
                        'loan_count' => $branchLoans->count(),
                    ];
                })
                ->sortByDesc('par_amount');
        }

        // Get PAR trends (last 6 months)
        $parTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthPar = $this->calculatePAR($organizationId, $branchId, $parDays, $date);
            $parTrends[] = [
                'month' => $date->format('M Y'),
                'par_percentage' => $monthPar,
            ];
        }

        // Get top clients by PAR amount
        $topParClients = $parLoans->groupBy('loan.client_id')
            ->map(function($clientLoans) {
                $client = $clientLoans->first()['loan']->client;
                $totalParAmount = $clientLoans->sum('overdue_amount');
                $totalOutstanding = $clientLoans->sum('total_outstanding');
                $maxDaysOverdue = $clientLoans->max('days_overdue');
                
                return [
                    'client_name' => "{$client->first_name} {$client->last_name}",
                    'client_phone' => $client->phone_number ?? 'N/A',
                    'par_amount' => $totalParAmount,
                    'total_outstanding' => $totalOutstanding,
                    'par_percentage' => $totalOutstanding > 0 ? ($totalParAmount / $totalOutstanding) * 100 : 0,
                    'max_days_overdue' => $maxDaysOverdue,
                    'loan_count' => $clientLoans->count(),
                ];
            })
            ->sortByDesc('par_amount')
            ->take(20);

        return view('reports.par', compact(
            'parLoans', 
            'totalParAmount', 
            'totalOutstanding', 
            'overallParPercentage', 
            'parDays',
            'parByProduct',
            'parByBranch',
            'parTrends',
            'topParClients'
        ));
    }

    /**
     * Loan disbursements report
     */
    public function loanDisbursements(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        $query = Loan::where('organization_id', $organizationId)
            ->whereBetween('disbursement_date', [$startDate, $endDate])
            ->where('status', 'active');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $disbursements = $query->with(['client', 'loanProduct', 'branch'])
            ->orderBy('disbursement_date', 'desc')
            ->get();

        // Get summary statistics
        $totalDisbursed = $disbursements->sum('approved_amount');
        $loanCount = $disbursements->count();
        $averageLoanSize = $loanCount > 0 ? $totalDisbursed / $loanCount : 0;

        // Get disbursements by product
        $disbursementsByProduct = $disbursements->groupBy('loanProduct.name')
            ->map(function($productDisbursements) {
                return [
                    'product_name' => $productDisbursements->first()->loanProduct->name ?? 'Unknown',
                    'total_amount' => $productDisbursements->sum('approved_amount'),
                    'loan_count' => $productDisbursements->count(),
                    'average_amount' => $productDisbursements->count() > 0 ? $productDisbursements->sum('approved_amount') / $productDisbursements->count() : 0,
                ];
            })
            ->sortByDesc('total_amount');

        // Get disbursements by branch (if admin)
        $disbursementsByBranch = collect();
        if ($user->role === 'admin') {
            $disbursementsByBranch = $disbursements->groupBy('branch.name')
                ->map(function($branchDisbursements) {
                    return [
                        'branch_name' => $branchDisbursements->first()->branch->name ?? 'Unknown',
                        'total_amount' => $branchDisbursements->sum('approved_amount'),
                        'loan_count' => $branchDisbursements->count(),
                        'average_amount' => $branchDisbursements->count() > 0 ? $branchDisbursements->sum('approved_amount') / $branchDisbursements->count() : 0,
                    ];
                })
                ->sortByDesc('total_amount');
        }

        // Get daily disbursements
        $dailyDisbursements = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayDisbursements = $disbursements->where('disbursement_date', $date->format('Y-m-d'));
            $dailyDisbursements[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'amount' => $dayDisbursements->sum('approved_amount'),
                'count' => $dayDisbursements->count(),
            ];
        }

        // Get top clients by disbursement amount
        $topClients = $disbursements->groupBy('client_id')
            ->map(function($clientDisbursements) {
                $client = $clientDisbursements->first()->client;
                return [
                    'client_name' => "{$client->first_name} {$client->last_name}",
                    'client_phone' => $client->phone_number ?? 'N/A',
                    'total_amount' => $clientDisbursements->sum('approved_amount'),
                    'loan_count' => $clientDisbursements->count(),
                    'average_amount' => $clientDisbursements->count() > 0 ? $clientDisbursements->sum('approved_amount') / $clientDisbursements->count() : 0,
                ];
            })
            ->sortByDesc('total_amount')
            ->take(20);

        return view('reports.loan-disbursements', compact(
            'disbursements', 
            'totalDisbursed', 
            'startDate', 
            'endDate',
            'loanCount',
            'averageLoanSize',
            'disbursementsByProduct',
            'disbursementsByBranch',
            'dailyDisbursements',
            'topClients'
        ));
    }

    /**
     * Loan collections report
     */
    public function loanCollections(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        $query = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'paid')
        ->whereBetween('paid_date', [$startDate, $endDate])
        ->with(['loan.client', 'loan.loanProduct', 'loan.branch']);

        $collections = $query->orderBy('paid_date', 'desc')->get();

        // Get summary statistics
        $totalCollected = $collections->sum('paid_amount');
        $collectionCount = $collections->count();
        $averageCollection = $collectionCount > 0 ? $totalCollected / $collectionCount : 0;

        // Get collections by product
        $collectionsByProduct = $collections->groupBy('loan.loanProduct.name')
            ->map(function($productCollections) {
                return [
                    'product_name' => $productCollections->first()->loan->loanProduct->name ?? 'Unknown',
                    'total_amount' => $productCollections->sum('paid_amount'),
                    'collection_count' => $productCollections->count(),
                    'average_amount' => $productCollections->count() > 0 ? $productCollections->sum('paid_amount') / $productCollections->count() : 0,
                ];
            })
            ->sortByDesc('total_amount');

        // Get collections by branch (if admin)
        $collectionsByBranch = collect();
        if ($user->role === 'admin') {
            $collectionsByBranch = $collections->groupBy('loan.branch.name')
                ->map(function($branchCollections) {
                    return [
                        'branch_name' => $branchCollections->first()->loan->branch->name ?? 'Unknown',
                        'total_amount' => $branchCollections->sum('paid_amount'),
                        'collection_count' => $branchCollections->count(),
                        'average_amount' => $branchCollections->count() > 0 ? $branchCollections->sum('paid_amount') / $branchCollections->count() : 0,
                    ];
                })
                ->sortByDesc('total_amount');
        }

        // Get daily collections
        $dailyCollections = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayCollections = $collections->where('paid_date', $date->format('Y-m-d'));
            $dailyCollections[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'amount' => $dayCollections->sum('paid_amount'),
                'count' => $dayCollections->count(),
            ];
        }

        // Get top clients by collection amount
        $topClients = $collections->groupBy('loan.client_id')
            ->map(function($clientCollections) {
                $client = $clientCollections->first()->loan->client;
                return [
                    'client_name' => "{$client->first_name} {$client->last_name}",
                    'client_phone' => $client->phone_number ?? 'N/A',
                    'total_amount' => $clientCollections->sum('paid_amount'),
                    'collection_count' => $clientCollections->count(),
                    'average_amount' => $clientCollections->count() > 0 ? $clientCollections->sum('paid_amount') / $clientCollections->count() : 0,
                ];
            })
            ->sortByDesc('total_amount')
            ->take(20);

        // Get collection efficiency (collections vs disbursements)
        $totalDisbursed = Loan::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('status', 'active')
            ->sum('approved_amount');

        $collectionEfficiency = $totalDisbursed > 0 ? ($totalCollected / $totalDisbursed) * 100 : 0;

        return view('reports.loan-collections', compact(
            'collections', 
            'totalCollected', 
            'startDate', 
            'endDate',
            'collectionCount',
            'averageCollection',
            'collectionsByProduct',
            'collectionsByBranch',
            'dailyCollections',
            'topClients',
            'collectionEfficiency',
            'totalDisbursed'
        ));
    }

    /**
     * Expense reports
     */
    public function expenses(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        $query = ExpenseRequest::where('organization_id', $organizationId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $expenses = $query->with(['requester', 'expenseAccount', 'paymentAccount'])
            ->orderBy('completed_at', 'desc')
            ->get();

        // Get summary statistics
        $totalExpenses = $expenses->sum('amount');
        $expenseCount = $expenses->count();
        $averageExpense = $expenseCount > 0 ? $totalExpenses / $expenseCount : 0;

        // Group by expense account
        $expensesByAccount = $expenses->groupBy('expenseAccount.name')
            ->map(function($accountExpenses) {
                return [
                    'account_name' => $accountExpenses->first()->expenseAccount->name ?? 'Unknown',
                    'total_amount' => $accountExpenses->sum('amount'),
                    'expense_count' => $accountExpenses->count(),
                    'average_amount' => $accountExpenses->count() > 0 ? $accountExpenses->sum('amount') / $accountExpenses->count() : 0,
                ];
            })
            ->sortByDesc('total_amount');

        // Get expenses by branch (if admin)
        $expensesByBranch = collect();
        if ($user->role === 'admin') {
            $expensesByBranch = $expenses->groupBy('branch.name')
                ->map(function($branchExpenses) {
                    return [
                        'branch_name' => $branchExpenses->first()->branch->name ?? 'Unknown',
                        'total_amount' => $branchExpenses->sum('amount'),
                        'expense_count' => $branchExpenses->count(),
                        'average_amount' => $branchExpenses->count() > 0 ? $branchExpenses->sum('amount') / $branchExpenses->count() : 0,
                    ];
                })
                ->sortByDesc('total_amount');
        }

        // Get daily expenses
        $dailyExpenses = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayExpenses = $expenses->where('completed_at', $date->format('Y-m-d'));
            $dailyExpenses[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'amount' => $dayExpenses->sum('amount'),
                'count' => $dayExpenses->count(),
            ];
        }

        // Get top requesters by expense amount
        $topRequesters = $expenses->groupBy('requester_id')
            ->map(function($requesterExpenses) {
                $requester = $requesterExpenses->first()->requester;
                return [
                    'requester_name' => "{$requester->first_name} {$requester->last_name}",
                    'requester_email' => $requester->email ?? 'N/A',
                    'total_amount' => $requesterExpenses->sum('amount'),
                    'expense_count' => $requesterExpenses->count(),
                    'average_amount' => $requesterExpenses->count() > 0 ? $requesterExpenses->sum('amount') / $requesterExpenses->count() : 0,
                ];
            })
            ->sortByDesc('total_amount')
            ->take(20);

        // Get expense categories breakdown
        $expenseCategories = $expenses->groupBy('category')
            ->map(function($categoryExpenses) {
                return [
                    'category' => $categoryExpenses->first()->category ?? 'Uncategorized',
                    'total_amount' => $categoryExpenses->sum('amount'),
                    'expense_count' => $categoryExpenses->count(),
                    'average_amount' => $categoryExpenses->count() > 0 ? $categoryExpenses->sum('amount') / $categoryExpenses->count() : 0,
                ];
            })
            ->sortByDesc('total_amount');

        return view('reports.expenses', compact(
            'expenses', 
            'expensesByAccount', 
            'totalExpenses', 
            'startDate', 
            'endDate',
            'expenseCount',
            'averageExpense',
            'expensesByBranch',
            'dailyExpenses',
            'topRequesters',
            'expenseCategories'
        ));
    }

    /**
     * Customer reports
     */
    public function customers(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        $query = Client::where('organization_id', $organizationId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Filter by client type
        if ($request->has('client_type')) {
            $query->where('client_type', $request->client_type);
        }

        $clients = $query->with(['loans' => function($q) {
            $q->where('status', 'active');
        }])
        ->orderBy('created_at', 'desc')
        ->get();

        // Calculate client statistics
        $clientsWithLoans = $clients->filter(function($client) {
            return $client->loans->count() > 0;
        });

        $totalClients = $clients->count();
        $clientsWithActiveLoans = $clientsWithLoans->count();
        $totalLoanAmount = $clientsWithLoans->sum(function($client) {
            return $client->loans->sum('approved_amount');
        });

        // Get clients by type
        $clientsByType = $clients->groupBy('client_type')
            ->map(function($typeClients) use ($totalClients) {
                return [
                    'type' => ucfirst($typeClients->first()->client_type ?? 'Unknown'),
                    'count' => $typeClients->count(),
                    'percentage' => $totalClients > 0 ? ($typeClients->count() / $totalClients) * 100 : 0,
                ];
            })
            ->sortByDesc('count');

        // Get clients by branch (if admin)
        $clientsByBranch = collect();
        if ($user->role === 'admin') {
            $clientsByBranch = $clients->groupBy('branch.name')
                ->map(function($branchClients) {
                    $activeLoans = $branchClients->filter(function($client) {
                        return $client->loans->count() > 0;
                    });
                    return [
                        'branch_name' => $branchClients->first()->branch->name ?? 'Unknown',
                        'total_clients' => $branchClients->count(),
                        'active_loans' => $activeLoans->count(),
                        'total_loan_amount' => $activeLoans->sum(function($client) {
                            return $client->loans->sum('approved_amount');
                        }),
                    ];
                })
                ->sortByDesc('total_clients');
        }

        // Get top clients by loan amount
        $topClients = $clientsWithLoans->map(function($client) {
            $totalLoanAmount = $client->loans->sum('approved_amount');
            $activeLoanCount = $client->loans->count();
            $averageLoanSize = $activeLoanCount > 0 ? $totalLoanAmount / $activeLoanCount : 0;
            
            return [
                'client_name' => "{$client->first_name} {$client->last_name}",
                'client_phone' => $client->phone_number ?? 'N/A',
                'client_type' => ucfirst($client->client_type ?? 'Unknown'),
                'total_loan_amount' => $totalLoanAmount,
                'active_loan_count' => $activeLoanCount,
                'average_loan_size' => $averageLoanSize,
                'registration_date' => $client->created_at,
            ];
        })
        ->sortByDesc('total_loan_amount')
        ->take(50);

        // Get client registration trends (last 12 months)
        $registrationTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthClients = $clients->where('created_at', '>=', $date->startOfMonth())
                ->where('created_at', '<=', $date->endOfMonth());
            $registrationTrends[] = [
                'month' => $date->format('M Y'),
                'count' => $monthClients->count(),
            ];
        }

        // Get client loan status distribution
        $loanStatusDistribution = $clients->map(function($client) {
            $loans = $client->loans;
            if ($loans->count() === 0) return 'no_loans';
            
            $hasOverdue = $loans->where('status', 'overdue')->count() > 0;
            $hasActive = $loans->where('status', 'active')->count() > 0;
            
            if ($hasOverdue) return 'overdue';
            if ($hasActive) return 'active';
            return 'completed';
        })
        ->groupBy(function($status) {
            return $status;
        })
        ->map(function($statusClients) {
            return [
                'status' => ucfirst(str_replace('_', ' ', $statusClients->first())),
                'count' => $statusClients->count(),
            ];
        });

        return view('reports.customers', compact(
            'clients', 
            'totalClients', 
            'clientsWithActiveLoans', 
            'totalLoanAmount',
            'clientsByType',
            'clientsByBranch',
            'topClients',
            'registrationTrends',
            'loanStatusDistribution'
        ));
    }

    /**
     * Repayment reports (existing method)
     */
    public function repayments(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id ?? null;

        $query = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'paid')
        ->with(['loan.client', 'loan.loanProduct', 'loan.loanOfficer']);

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('paid_date', [$request->date_from, $request->date_to]);
        }

        $repayments = $query->orderBy('paid_date', 'desc')->paginate(20);

        // Calculate totals
        $totalRepayments = $repayments->sum('paid_amount');
        $totalPrincipal = $repayments->sum('principal_amount');
        $totalInterest = $repayments->sum('interest_amount');
        
        // This month's total
        $thisMonthTotal = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
            $q->where('organization_id', $organizationId);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
        ->where('status', 'paid')
        ->whereMonth('paid_date', Carbon::now()->month)
        ->whereYear('paid_date', Carbon::now()->year)
        ->sum('paid_amount');

        // Get trends for last 6 months
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $amount = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
                $q->where('organization_id', $organizationId);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->where('status', 'paid')
            ->whereMonth('paid_date', $date->month)
            ->whereYear('paid_date', $date->year)
            ->sum('paid_amount');
            
            $count = LoanSchedule::whereHas('loan', function($q) use ($organizationId, $branchId) {
                $q->where('organization_id', $organizationId);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->where('status', 'paid')
            ->whereMonth('paid_date', $date->month)
            ->whereYear('paid_date', $date->year)
            ->count();
            
            $trends[] = [
                'month' => $date->format('M Y'),
                'amount' => $amount,
                'count' => $count
            ];
        }

        // Get loan officers for filter
        $loanOfficers = \App\Models\User::where('organization_id', $organizationId)
            ->where('role', 'loan_officer')
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->get();

        // Get clients for filter
        $clients = \App\Models\Client::where('organization_id', $organizationId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->get()
            ->map(function($client) {
                $client->display_name = $client->first_name . ' ' . $client->last_name;
                return $client;
            });

        return view('reports.repayments', compact(
            'repayments', 'totalRepayments', 'totalPrincipal', 'totalInterest', 
            'thisMonthTotal', 'trends', 'loanOfficers', 'clients'
        ));
    }
}