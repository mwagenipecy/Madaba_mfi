<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use App\Models\Organization;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoanProductsController extends Controller
{
    /**
     * Display a listing of loan products for the current user's organization
     */
    public function index()
    {
        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;

        $loanProducts = LoanProduct::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalProducts = $loanProducts->count();
        $activeProducts = $loanProducts->where('status', 'active')->count();
        $featuredProducts = $loanProducts->where('is_featured', true)->count();
        $avgInterestRate = $totalProducts > 0 ? round($loanProducts->avg('interest_rate'), 2) : 0;

        return view('loan-products.index', compact(
            'loanProducts',
            'totalProducts',
            'activeProducts',
            'featuredProducts',
            'avgInterestRate'
        ));
    }

    /**
     * Show the form for creating a new loan product
     */
    public function create()
    {
        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
        
        // Get accounts for the organization
        $accounts = \App\Models\Account::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->with(['accountType', 'branch'])
            ->orderBy('name')
            ->get();

        return view('loan-products.create', compact('accounts'));
    }

    /**
     * Store a newly created loan product
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:loan_products,code',
            'description' => 'nullable|string',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0|gte:min_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'interest_type' => 'required|in:fixed,variable',
            'interest_calculation_method' => 'required|in:flat,reducing',
            'min_tenure_months' => 'required|integer|min:1',
            'max_tenure_months' => 'required|integer|min:1|gte:min_tenure_months',
            'processing_fee' => 'nullable|numeric|min:0',
            'late_fee' => 'nullable|numeric|min:0',
            'repayment_frequency' => 'required|in:daily,weekly,monthly,quarterly',
            'grace_period_days' => 'nullable|integer|min:0',
            'requires_collateral' => 'boolean',
            'collateral_ratio' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive,suspended',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'disbursement_account_id' => 'required|exists:accounts,id',
            'collection_account_id' => 'required|exists:accounts,id',
            'interest_revenue_account_id' => 'required|exists:accounts,id',
            'principal_account_id' => 'required|exists:accounts,id',
        ]);

        $organizationId = Auth::user()->organization_id;

        $loanProduct = LoanProduct::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'organization_id' => $organizationId,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'interest_rate' => $request->interest_rate,
            'interest_type' => $request->interest_type,
            'interest_calculation_method' => $request->interest_calculation_method,
            'min_tenure_months' => $request->min_tenure_months,
            'max_tenure_months' => $request->max_tenure_months,
            'processing_fee' => $request->processing_fee ?? 0.00,
            'late_fee' => $request->late_fee ?? 0.00,
            'repayment_frequency' => $request->repayment_frequency,
            'grace_period_days' => $request->grace_period_days ?? 0,
            'eligibility_criteria' => $request->eligibility_criteria ? json_decode($request->eligibility_criteria, true) : null,
            'required_documents' => $request->required_documents ? json_decode($request->required_documents, true) : null,
            'requires_collateral' => $request->boolean('requires_collateral'),
            'collateral_ratio' => $request->collateral_ratio,
            'status' => $request->status,
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => $request->sort_order ?? 0,
            'disbursement_account_id' => $request->disbursement_account_id,
            'collection_account_id' => $request->collection_account_id,
            'interest_revenue_account_id' => $request->interest_revenue_account_id,
            'principal_account_id' => $request->principal_account_id,
        ]);

        // Log the action
        SystemLog::log(
            'loan_product_created',
            "Loan product '{$loanProduct->name}' created",
            'info',
            $loanProduct,
            Auth::id(),
            ['product_code' => $loanProduct->code]
        );

        return redirect()->route('loan-products.index')
            ->with('success', 'Loan product created successfully.');
    }

    /**
     * Display the specified loan product
     */
    public function show(LoanProduct $loanProduct)
    {
        // Ensure user can only view products from their organization
        if ($loanProduct->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized access to loan product.');
        }

        return view('loan-products.show', compact('loanProduct'));
    }

    /**
     * Show the form for editing the specified loan product
     */
    public function edit(LoanProduct $loanProduct)
    {
        // Ensure user can only edit products from their organization
        if ($loanProduct->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized access to loan product.');
        }

        return view('loan-products.edit', compact('loanProduct'));
    }

    /**
     * Update the specified loan product
     */
    public function update(Request $request, LoanProduct $loanProduct)
    {
        // Ensure user can only update products from their organization
        if ($loanProduct->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized access to loan product.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:loan_products,code,' . $loanProduct->id,
            'description' => 'nullable|string',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0|gte:min_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'interest_type' => 'required|in:fixed,variable',
            'interest_calculation_method' => 'required|in:flat,reducing',
            'min_tenure_months' => 'required|integer|min:1',
            'max_tenure_months' => 'required|integer|min:1|gte:min_tenure_months',
            'processing_fee' => 'nullable|numeric|min:0',
            'late_fee' => 'nullable|numeric|min:0',
            'repayment_frequency' => 'required|in:daily,weekly,monthly,quarterly',
            'grace_period_days' => 'nullable|integer|min:0',
            'requires_collateral' => 'boolean',
            'collateral_ratio' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive,suspended',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $oldData = $loanProduct->toArray();

        $loanProduct->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'interest_rate' => $request->interest_rate,
            'interest_type' => $request->interest_type,
            'interest_calculation_method' => $request->interest_calculation_method,
            'min_tenure_months' => $request->min_tenure_months,
            'max_tenure_months' => $request->max_tenure_months,
            'processing_fee' => $request->processing_fee ?? 0.00,
            'late_fee' => $request->late_fee ?? 0.00,
            'repayment_frequency' => $request->repayment_frequency,
            'grace_period_days' => $request->grace_period_days ?? 0,
            'eligibility_criteria' => $request->eligibility_criteria ? json_decode($request->eligibility_criteria, true) : null,
            'required_documents' => $request->required_documents ? json_decode($request->required_documents, true) : null,
            'requires_collateral' => $request->boolean('requires_collateral'),
            'collateral_ratio' => $request->collateral_ratio,
            'status' => $request->status,
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        // Log the action
        SystemLog::log(
            'loan_product_updated',
            "Loan product '{$loanProduct->name}' updated",
            'info',
            $loanProduct,
            Auth::id(),
            ['changes' => array_diff_assoc($loanProduct->toArray(), $oldData)]
        );

        return redirect()->route('loan-products.index')
            ->with('success', 'Loan product updated successfully.');
    }

    /**
     * Remove the specified loan product (soft delete)
     */
    public function destroy(LoanProduct $loanProduct)
    {
        // Ensure user can only delete products from their organization
        if ($loanProduct->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized access to loan product.');
        }

        $productName = $loanProduct->name;
        $productCode = $loanProduct->code;

        $loanProduct->delete();

        // Log the action
        SystemLog::log(
            'loan_product_deleted',
            "Loan product '{$productName}' disabled",
            'warning',
            $loanProduct,
            Auth::id(),
            ['product_code' => $productCode]
        );

        return redirect()->route('loan-products.index')
            ->with('success', 'Loan product disabled successfully.');
    }

    /**
     * Generate a unique product code
     */
    public function generateCode()
    {
        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
        $prefix = 'LP';
        $orgCode = str_pad($organizationId, 3, '0', STR_PAD_LEFT);
        $timestamp = now()->format('ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return response($prefix . $orgCode . $timestamp . $random, 200)
            ->header('Content-Type', 'text/plain');
    }
}