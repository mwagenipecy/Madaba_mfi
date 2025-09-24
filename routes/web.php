<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationFlowController;
use App\Http\Livewire\RegisterFlow;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::get('/', function () {

    return redirect()->route('login');
    //return view('welcome');
});


Route::get('register', function () {

    return redirect()->route('login');
    //return view('welcome');
});

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Static pages for Terms and Privacy
Route::view('/terms', 'terms')->name('terms.show');
Route::view('/policy', 'policy')->name('policy.show');

// OTP Verification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/verify-otp', [App\Http\Controllers\OtpVerificationController::class, 'show'])->name('otp.show');
    Route::post('/verify-otp', [App\Http\Controllers\OtpVerificationController::class, 'verify'])->name('otp.verify');
    Route::post('/resend-otp', [App\Http\Controllers\OtpVerificationController::class, 'resend'])->name('otp.resend');
    Route::post('/otp-logout', [App\Http\Controllers\OtpVerificationController::class, 'logout'])->name('otp.logout');
});

// Custom multi-step registration flow
// Route::get('/register', RegisterFlow::class)->name('register');
// Keep the previous fallback routes if used elsewhere
Route::post('/register/step1', [RegistrationFlowController::class, 'step1Store'])->name('register.step1.store');
Route::get('/register/plan', [RegistrationFlowController::class, 'showPlan'])->name('register.plan');
Route::post('/register/plan', [RegistrationFlowController::class, 'planStore'])->name('register.plan.store');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'otp.verified',
])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Branch Management
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/', [App\Http\Controllers\BranchController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\BranchController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\BranchController::class, 'store'])->name('store');
        Route::get('/{branch}', [App\Http\Controllers\BranchController::class, 'show'])->name('show');
        Route::get('/{branch}/edit', [App\Http\Controllers\BranchController::class, 'edit'])->name('edit');
        Route::put('/{branch}', [App\Http\Controllers\BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [App\Http\Controllers\BranchController::class, 'disable'])->name('disable');
        Route::get('/{branch}/users', [App\Http\Controllers\BranchController::class, 'users'])->name('users');
        Route::get('/{branch}/users/create', [App\Http\Controllers\BranchController::class, 'createUser'])->name('users.create');
    });
    
    // Payments Management
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [App\Http\Controllers\PaymentController::class, 'index'])->name('index');
        Route::get('/fund-transfer/create', [App\Http\Controllers\PaymentController::class, 'createFundTransfer'])->name('fund-transfer.create');
        Route::post('/fund-transfer', [App\Http\Controllers\PaymentController::class, 'storeFundTransfer'])->name('fund-transfer.store');
        Route::get('/account-recharge/create', [App\Http\Controllers\PaymentController::class, 'createAccountRecharge'])->name('account-recharge.create');
        Route::post('/account-recharge', [App\Http\Controllers\PaymentController::class, 'storeAccountRecharge'])->name('account-recharge.store');
    });
    
    // Accounts Management
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', [App\Http\Controllers\AccountsController::class, 'index'])->name('index');
        Route::get('/main', [App\Http\Controllers\AccountsController::class, 'mainAccounts'])->name('main-accounts');
        Route::get('/main/{account}/subaccounts', [App\Http\Controllers\AccountsController::class, 'subAccountsByCategory'])->name('main-accounts.subaccounts');
        Route::get('/branch', [App\Http\Controllers\AccountsController::class, 'branchAccounts'])->name('branch-accounts');
        Route::get('/real', [App\Http\Controllers\AccountsController::class, 'realAccounts'])->name('real-accounts');
        Route::get('/general-ledger', [App\Http\Controllers\AccountsController::class, 'generalLedger'])->name('general-ledger');
        Route::get('/balance-sheet', [App\Http\Controllers\BalanceSheetController::class, 'index'])->name('balance-sheet');
        Route::get('/balance-sheet/export', [App\Http\Controllers\BalanceSheetController::class, 'export'])->name('balance-sheet.export');
        Route::get('/create', [App\Http\Controllers\AccountsController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\AccountsController::class, 'store'])->name('store');
        
        // Real Account Mapping - Must be before {account} routes
        Route::get('/mapped', [App\Http\Controllers\AccountsController::class, 'mappedAccounts'])->name('mapped');
        
        Route::get('/{account}', [App\Http\Controllers\AccountsController::class, 'show'])->name('show');
        Route::get('/{account}/edit', [App\Http\Controllers\AccountsController::class, 'edit'])->name('edit');
        Route::put('/{account}', [App\Http\Controllers\AccountsController::class, 'update'])->name('update');
        Route::delete('/{account}', [App\Http\Controllers\AccountsController::class, 'disable'])->name('disable');
        Route::get('/{account}/real/create', [App\Http\Controllers\AccountsController::class, 'createRealAccount'])->name('real.create');
        Route::post('/{account}/real', [App\Http\Controllers\AccountsController::class, 'storeRealAccount'])->name('real.store');
        Route::get('/{account}/map-real', [App\Http\Controllers\AccountsController::class, 'mapRealAccount'])->name('map-real');
        Route::post('/{account}/map-real', [App\Http\Controllers\AccountsController::class, 'storeRealAccountMapping'])->name('store-real-mapping');
        Route::post('/real/{realAccount}/sync', [App\Http\Controllers\AccountsController::class, 'syncBalance'])->name('real.sync');
    });

    

    // Loan Products Management
    Route::prefix('loan-products')->name('loan-products.')->group(function () {
        Route::get('/', [App\Http\Controllers\LoanProductsController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\LoanProductsController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\LoanProductsController::class, 'store'])->name('store');
        // Place generate-code BEFORE the catch-all {loanProduct} routes to avoid collision
        Route::get('/generate-code', [App\Http\Controllers\LoanProductsController::class, 'generateCode'])->name('generate-code');
        Route::get('/{loanProduct}', [App\Http\Controllers\LoanProductsController::class, 'show'])->name('show');
        Route::get('/{loanProduct}/edit', [App\Http\Controllers\LoanProductsController::class, 'edit'])->name('edit');
        Route::put('/{loanProduct}', [App\Http\Controllers\LoanProductsController::class, 'update'])->name('update');
        Route::delete('/{loanProduct}', [App\Http\Controllers\LoanProductsController::class, 'destroy'])->name('destroy');
    });

    // Client Management
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [App\Http\Controllers\ClientsController::class, 'index'])->name('index');
        Route::get('/individual', [App\Http\Controllers\ClientsController::class, 'individual'])->name('individual');
        Route::get('/business', [App\Http\Controllers\ClientsController::class, 'business'])->name('business');
        Route::get('/create', [App\Http\Controllers\ClientsController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ClientsController::class, 'store'])->name('store');
        Route::get('/{client}', [App\Http\Controllers\ClientsController::class, 'show'])->name('show');
        Route::get('/{client}/edit', [App\Http\Controllers\ClientsController::class, 'edit'])->name('edit');
        Route::put('/{client}', [App\Http\Controllers\ClientsController::class, 'update'])->name('update');
        Route::delete('/{client}', [App\Http\Controllers\ClientsController::class, 'destroy'])->name('destroy');
        Route::patch('/{client}/kyc-status', [App\Http\Controllers\ClientsController::class, 'updateKycStatus'])->name('update-kyc-status');
        Route::get('/generate-client-number', [App\Http\Controllers\ClientsController::class, 'generateClientNumber'])->name('generate-client-number');
    });

    // Loan Management
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\LoansController::class, 'dashboard'])->name('dashboard');
        Route::get('/', [App\Http\Controllers\LoansController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\LoansController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\LoansController::class, 'store'])->name('store');
        Route::get('/applications', [App\Http\Controllers\LoansController::class, 'applications'])->name('applications');
        Route::get('/approvals', [App\Http\Controllers\LoansController::class, 'approvals'])->name('approvals');
        Route::get('/disbursements', [App\Http\Controllers\LoansController::class, 'disbursements'])->name('disbursements');
        Route::get('/repayments', [App\Http\Controllers\LoansController::class, 'repayments'])->name('repayments');
        Route::get('/reports', [App\Http\Controllers\LoansController::class, 'reports'])->name('reports');
        Route::get('/generate-loan-number', [App\Http\Controllers\LoansController::class, 'generateLoanNumber'])->name('generate-loan-number');
        Route::get('/{loan}', [App\Http\Controllers\LoansController::class, 'show'])->name('show');
        Route::get('/{loan}/edit', [App\Http\Controllers\LoansController::class, 'edit'])->name('edit');
        Route::put('/{loan}', [App\Http\Controllers\LoansController::class, 'update'])->name('update');
        Route::delete('/{loan}', [App\Http\Controllers\LoansController::class, 'destroy'])->name('destroy');
        Route::post('/{loan}/upload-document', [App\Http\Controllers\LoansController::class, 'uploadDocument'])->name('upload-document');
        Route::post('/{loan}/add-comment', [App\Http\Controllers\LoansController::class, 'addComment'])->name('add-comment');
        Route::get('/{loan}/download-document/{documentId}', [App\Http\Controllers\LoansController::class, 'downloadDocument'])->name('download-document');
        Route::delete('/{loan}/delete-document/{documentId}', [App\Http\Controllers\LoansController::class, 'deleteDocument'])->name('delete-document');
        Route::post('/{loan}/approve', [App\Http\Controllers\LoansController::class, 'approve'])->name('approve');
        Route::post('/{loan}/reject', [App\Http\Controllers\LoansController::class, 'reject'])->name('reject');
        Route::post('/{loan}/return-to-officer', [App\Http\Controllers\LoansController::class, 'returnToOfficer'])->name('return-to-officer');
        Route::post('/{loan}/under-review', [App\Http\Controllers\LoansController::class, 'putUnderReview'])->name('under-review');
        Route::post('/{loan}/disburse', [App\Http\Controllers\LoansController::class, 'disburse'])->name('disburse');
        Route::post('/{loan}/repayment', [App\Http\Controllers\LoansController::class, 'processRepayment'])->name('repayment');
        Route::post('/{loan}/close', [App\Http\Controllers\LoansController::class, 'closeLoan'])->name('close');
        Route::post('/{loan}/write-off', [App\Http\Controllers\LoansController::class, 'writeOffLoan'])->name('write-off');
        Route::post('/{loan}/restructure', [App\Http\Controllers\LoansController::class, 'restructureLoan'])->name('restructure');
        Route::post('/{loan}/top-up', [App\Http\Controllers\LoansController::class, 'topUpLoan'])->name('top-up');
    });

    // Loan Charges Management
    Route::prefix('loan-charges')->name('loan-charges.')->group(function () {
        Route::get('/', [App\Http\Controllers\LoanChargesController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\LoanChargesController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\LoanChargesController::class, 'store'])->name('store');
        Route::post('/bulk-update', [App\Http\Controllers\LoanChargesController::class, 'bulkUpdate'])->name('bulk-update');
        Route::get('/{loanTransaction}', [App\Http\Controllers\LoanChargesController::class, 'show'])->name('show');
        Route::patch('/{loanTransaction}/status', [App\Http\Controllers\LoanChargesController::class, 'updateStatus'])->name('update-status');
        Route::post('/{loanTransaction}/pay', [App\Http\Controllers\LoanChargesController::class, 'processPayment'])->name('pay');
    });
    
    // Arrears route (temporarily outside the group for testing)
    Route::get('/loan-charges/arrears', [App\Http\Controllers\LoanChargesController::class, 'arrears'])->name('loan-charges.arrears');

    // Approvals Management
    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/pending', [App\Http\Controllers\ApprovalsController::class, 'pending'])->name('pending');
        Route::get('/loans', [App\Http\Controllers\ApprovalsController::class, 'loans'])->name('loans');
        Route::get('/fund-transfers', [App\Http\Controllers\ApprovalsController::class, 'fundTransfers'])->name('fund-transfers');
        Route::get('/account-recharges', [App\Http\Controllers\ApprovalsController::class, 'accountRecharges'])->name('account-recharges');
        Route::get('/expenses', [App\Http\Controllers\ApprovalsController::class, 'expenses'])->name('expenses');
        Route::get('/history', [App\Http\Controllers\ApprovalsController::class, 'history'])->name('history');
        Route::post('/{approval}/approve', [App\Http\Controllers\ApprovalsController::class, 'approve'])->name('approve');
        Route::post('/{approval}/reject', [App\Http\Controllers\ApprovalsController::class, 'reject'])->name('reject');
        Route::post('/expenses/{expenseRequest}/approve', [App\Http\Controllers\ApprovalsController::class, 'approveExpense'])->name('expenses.approve');
        Route::post('/expenses/{expenseRequest}/reject', [App\Http\Controllers\ApprovalsController::class, 'rejectExpense'])->name('expenses.reject');
    });
    
    // Organizations Management
    Route::prefix('organizations')->name('organizations.')->group(function () {
        Route::get('/', [App\Http\Controllers\OrganizationController::class, 'index'])->name('index');
        Route::get('/profile', [App\Http\Controllers\OrganizationController::class, 'profile'])->name('profile');
        Route::get('/{organization}/users', [App\Http\Controllers\OrganizationController::class, 'users'])->name('users');
        Route::get('/{organization}/users/create', [App\Http\Controllers\OrganizationController::class, 'createUser'])->name('users.create');
        Route::get('/{organization}/edit', [App\Http\Controllers\OrganizationController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{organization}', [App\Http\Controllers\OrganizationController::class, 'update'])->name('update');
        Route::patch('/{organization}/deactivate', [App\Http\Controllers\OrganizationController::class, 'deactivate'])->name('deactivate');
        Route::patch('/{organization}/reactivate', [App\Http\Controllers\OrganizationController::class, 'reactivate'])->name('reactivate');
    });

    // Organization Settings (Self-Management)
    Route::prefix('organization-settings')->name('organization-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\OrganizationSettingsController::class, 'index'])->name('index');
        Route::get('/details', [App\Http\Controllers\OrganizationSettingsController::class, 'details'])->name('details');
        Route::get('/users', [App\Http\Controllers\OrganizationSettingsController::class, 'users'])->name('users');
        Route::get('/users/create', [App\Http\Controllers\OrganizationSettingsController::class, 'createUser'])->name('users.create');
        Route::get('/edit', [App\Http\Controllers\OrganizationSettingsController::class, 'edit'])->name('edit');
    });

    // Management (System Administration)
    Route::prefix('management')->name('management.')->group(function () {
        Route::get('/users', [App\Http\Controllers\ManagementController::class, 'users'])->name('users');
        Route::get('/system-logs', [App\Http\Controllers\ManagementController::class, 'systemLogs'])->name('system-logs');
        Route::post('/users/{user}/disable', [App\Http\Controllers\ManagementController::class, 'disableUser'])->name('users.disable');
        Route::post('/users/{userId}/activate', [App\Http\Controllers\ManagementController::class, 'activateUser'])->name('users.activate');
    });
});



// Expenses management routes
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/repayment', [App\Http\Controllers\ExpensesController::class, 'repayment'])->name('repayment');
        Route::post('/repayment', [App\Http\Controllers\ExpensesController::class, 'storeRepayment'])->name('repayment.store');
        Route::get('/requests', [App\Http\Controllers\ExpensesController::class, 'requests'])->name('requests');
        Route::get('/history', [App\Http\Controllers\ExpensesController::class, 'history'])->name('history');
        Route::get('/{expenseRequest}', [App\Http\Controllers\ExpensesController::class, 'show'])->name('show');
        Route::post('/{expenseRequest}/complete', [App\Http\Controllers\ExpensesController::class, 'complete'])->name('complete');
        Route::get('/{expenseRequest}/receipt', [App\Http\Controllers\ExpensesController::class, 'downloadReceipt'])->name('receipt');
    });
});

// Reports routes
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\ReportsController::class, 'index'])->name('index');
        Route::get('/weekly-payments', [App\Http\Controllers\ReportsController::class, 'weeklyPayments'])->name('weekly-payments');
        Route::get('/arrears', [App\Http\Controllers\ReportsController::class, 'arrears'])->name('arrears');
        Route::get('/par', [App\Http\Controllers\ReportsController::class, 'par'])->name('par');
        Route::get('/loan-disbursements', [App\Http\Controllers\ReportsController::class, 'loanDisbursements'])->name('loan-disbursements');
        Route::get('/loan-collections', [App\Http\Controllers\ReportsController::class, 'loanCollections'])->name('loan-collections');
        Route::get('/expenses', [App\Http\Controllers\ReportsController::class, 'expenses'])->name('expenses');
        Route::get('/customers', [App\Http\Controllers\ReportsController::class, 'customers'])->name('customers');
        Route::get('/repayments', [App\Http\Controllers\ReportsController::class, 'repayments'])->name('repayments');
        Route::get('/crb', [App\Http\Controllers\CrbReportController::class, 'index'])->name('crb');
        Route::get('/crb/export', [App\Http\Controllers\CrbReportController::class, 'export'])->name('crb.export');
    });
});

// Super Admin Routes
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/organizations', [App\Http\Controllers\SuperAdminController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [App\Http\Controllers\SuperAdminController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [App\Http\Controllers\SuperAdminController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{organization}', [App\Http\Controllers\SuperAdminController::class, 'show'])->name('organizations.show');
    Route::get('/organizations/{organization}/edit', [App\Http\Controllers\SuperAdminController::class, 'edit'])->name('organizations.edit');
    Route::put('/organizations/{organization}', [App\Http\Controllers\SuperAdminController::class, 'update'])->name('organizations.update');
    Route::patch('/organizations/{organization}/deactivate', [App\Http\Controllers\SuperAdminController::class, 'deactivate'])->name('organizations.deactivate');
    Route::patch('/organizations/{organization}/reactivate', [App\Http\Controllers\SuperAdminController::class, 'reactivate'])->name('organizations.reactivate');
    Route::get('/organizations/{organization}/statistics', [App\Http\Controllers\SuperAdminController::class, 'statistics'])->name('organizations.statistics');
    
    // Mapped Account Balance Views
    Route::get('/mapped-account-balances', [App\Http\Controllers\SuperAdminController::class, 'mappedAccountBalances'])->name('mapped-account-balances');
    Route::get('/organizations/{organization}/mapped-accounts', [App\Http\Controllers\SuperAdminController::class, 'organizationMappedAccounts'])->name('organizations.mapped-accounts');
});

/// organization onboarding and registering 

Route::prefix('organization')->name('organization.')->group(function () {
    Route::get('/onboarding', [App\Http\Controllers\OrganizationSettingController::class, 'showOnboardingForm'])->name('onboarding');
    Route::post('/onboarding', [App\Http\Controllers\OrganizationSettingController::class, 'processOnboarding'])->name('onboarding.process');
    Route::get('/register', [App\Http\Controllers\OrganizationSettingController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [App\Http\Controllers\OrganizationSettingController::class, 'processRegistration'])->name('register.process');
});

// Test routes for error pages (remove in production)
if (config('app.debug')) {
    Route::get('/test-error/{code}', function ($code) {
        switch ($code) {
            case '404':
                abort(404);
            case '403':
                abort(403);
            case '419':
                abort(419);
            case '500':
                abort(500);
            case '503':
                abort(503);
            default:
                abort(500, 'Test error message');
        }
    })->name('test.error');
    
    // Test Livewire layout
    Route::get('/test-livewire-layout', function () {
        return view('test-livewire-layout');
    })->name('test.livewire.layout');
    
    // Test arrears route
    Route::get('/test-arrears', function () {
        return 'Arrears test route works!';
    })->name('test.arrears');
    
    // Debug arrears route
    Route::get('/debug-arrears', function () {
        return response()->json([
            'route_exists' => Route::has('loan-charges.arrears'),
            'route_url' => route('loan-charges.arrears'),
            'user_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'organization_id' => auth()->user()->organization_id ?? 'null'
        ]);
    })->name('debug.arrears');
}



