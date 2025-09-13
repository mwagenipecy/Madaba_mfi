<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientsController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get client statistics
        $totalClients = Client::where('organization_id', $organizationId)->count();
        $individualClients = Client::where('organization_id', $organizationId)
            ->where('client_type', 'individual')->count();
        $businessClients = Client::where('organization_id', $organizationId)
            ->whereIn('client_type', ['business', 'group'])->count();
        $pendingKyc = Client::where('organization_id', $organizationId)
            ->where('kyc_status', 'pending')->count();
        
        return view('clients.index', compact('totalClients', 'individualClients', 'businessClients', 'pendingKyc'));
    }

    /**
     * Display a listing of individual clients
     */
    public function individual()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        $clients = Client::where('organization_id', $organizationId)
            ->where('client_type', 'individual')
            ->with(['organization', 'branch', 'verifiedBy'])
            ->latest()
            ->paginate(20);
            
        return view('clients.list', compact('clients'))->with('clientType', 'Individual');
    }

    /**
     * Display a listing of business clients
     */
    public function business()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        $clients = Client::where('organization_id', $organizationId)
            ->whereIn('client_type', ['business', 'group'])
            ->with(['organization', 'branch', 'verifiedBy'])
            ->latest()
            ->paginate(20);
            
        return view('clients.list', compact('clients'))->with('clientType', 'Business');
    }

    /**
     * Show the form for creating a new client
     */
    public function create()
    {
        $organizations = Organization::active()->get();
        $branches = Branch::active()->get();
        
        return view('clients.create', compact('organizations', 'branches'));
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_type' => 'required|in:individual,group,business',
            'organization_id' => 'required|exists:organizations,id',
            'branch_id' => 'nullable|exists:branches,id',
            
            // Individual fields
            'first_name' => 'required_if:client_type,individual|string|max:255',
            'last_name' => 'required_if:client_type,individual|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required_if:client_type,individual|date|before:today',
            'gender' => 'required_if:client_type,individual|in:male,female,other',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            
            // Business/Group fields
            'business_name' => 'required_if:client_type,business,group|string|max:255',
            'business_registration_number' => 'nullable|string|max:100',
            'business_type' => 'required_if:client_type,business,group|in:sole_proprietorship,partnership,corporation,cooperative,ngo,other',
            
            // Contact information
            'phone_number' => 'required|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'physical_address' => 'required|string',
            'city' => 'required|string|max:100',
            'region' => 'required|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            
            // Financial information
            'monthly_income' => 'nullable|numeric|min:0',
            'income_source' => 'nullable|string|max:255',
            'employer_name' => 'nullable|string|max:255',
            'employment_address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            
            // Emergency contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            
            // Additional information
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'dependents' => 'nullable|integer|min:0',
            'occupation' => 'nullable|string|max:255',
            'business_description' => 'nullable|string',
            'years_in_business' => 'nullable|integer|min:0',
            'annual_turnover' => 'nullable|numeric|min:0',
            
            'notes' => 'nullable|string',
        ]);

        $client = Client::create([
            'client_number' => Client::generateClientNumber(),
            'client_type' => $request->client_type,
            'organization_id' => $request->organization_id,
            'branch_id' => $request->branch_id,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'national_id' => $request->national_id,
            'passport_number' => $request->passport_number,
            'business_name' => $request->business_name,
            'business_registration_number' => $request->business_registration_number,
            'business_type' => $request->business_type,
            'phone_number' => $request->phone_number,
            'secondary_phone' => $request->secondary_phone,
            'email' => $request->email,
            'physical_address' => $request->physical_address,
            'city' => $request->city,
            'region' => $request->region,
            'country' => $request->country ?? 'Tanzania',
            'postal_code' => $request->postal_code,
            'monthly_income' => $request->monthly_income,
            'income_source' => $request->income_source,
            'employer_name' => $request->employer_name,
            'employment_address' => $request->employment_address,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_relationship' => $request->emergency_contact_relationship,
            'marital_status' => $request->marital_status,
            'dependents' => $request->dependents ?? 0,
            'occupation' => $request->occupation,
            'business_description' => $request->business_description,
            'years_in_business' => $request->years_in_business,
            'annual_turnover' => $request->annual_turnover,
            'notes' => $request->notes,
            'kyc_status' => 'pending',
        ]);

        // Log the client creation
        SystemLog::log(
            'Client created',
            'Client ' . $client->display_name . ' (' . $client->client_number . ') was created',
            $client,
            'client_created'
        );

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client created successfully. KYC verification is pending.');
    }

    /**
     * Display the specified client
     */
    public function show(Client $client)
    {
        $client->load(['organization', 'branch', 'verifiedBy', 'loans.loanProduct']);
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified client
     */
    public function edit(Client $client)
    {
        $organizations = Organization::active()->get();
        $branches = Branch::active()->get();
        
        return view('clients.edit', compact('client', 'organizations', 'branches'));
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'client_type' => 'required|in:individual,group,business',
            'organization_id' => 'required|exists:organizations,id',
            'branch_id' => 'nullable|exists:branches,id',
            
            // Individual fields
            'first_name' => 'required_if:client_type,individual|string|max:255',
            'last_name' => 'required_if:client_type,individual|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required_if:client_type,individual|date|before:today',
            'gender' => 'required_if:client_type,individual|in:male,female,other',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            
            // Business/Group fields
            'business_name' => 'required_if:client_type,business,group|string|max:255',
            'business_registration_number' => 'nullable|string|max:100',
            'business_type' => 'required_if:client_type,business,group|in:sole_proprietorship,partnership,corporation,cooperative,ngo,other',
            
            // Contact information
            'phone_number' => 'required|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'physical_address' => 'required|string',
            'city' => 'required|string|max:100',
            'region' => 'required|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            
            // Financial information
            'monthly_income' => 'nullable|numeric|min:0',
            'income_source' => 'nullable|string|max:255',
            'employer_name' => 'nullable|string|max:255',
            'employment_address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            
            // Emergency contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            
            // Additional information
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'dependents' => 'nullable|integer|min:0',
            'occupation' => 'nullable|string|max:255',
            'business_description' => 'nullable|string',
            'years_in_business' => 'nullable|integer|min:0',
            'annual_turnover' => 'nullable|numeric|min:0',
            
            'notes' => 'nullable|string',
        ]);

        $client->update($request->all());

        // Log the client update
        SystemLog::log(
            'Client updated',
            'Client ' . $client->display_name . ' (' . $client->client_number . ') was updated',
            $client,
            'client_updated'
        );

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client (soft delete)
     */
    public function destroy(Client $client)
    {
        $client->delete();

        // Log the client deletion
        SystemLog::log(
            'Client deleted',
            'Client ' . $client->display_name . ' (' . $client->client_number . ') was deleted',
            $client,
            'client_deleted'
        );

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    /**
     * Update KYC status
     */
    public function updateKycStatus(Request $request, Client $client)
    {
        $request->validate([
            'kyc_status' => 'required|in:pending,verified,rejected,expired',
            'kyc_notes' => 'nullable|string',
        ]);

        $client->update([
            'kyc_status' => $request->kyc_status,
            'kyc_verification_date' => $request->kyc_status === 'verified' ? now() : null,
            'verified_by' => $request->kyc_status === 'verified' ? Auth::id() : null,
            'kyc_notes' => $request->kyc_notes,
        ]);

        // Log the KYC status change
        SystemLog::log(
            'KYC status updated',
            'Client ' . $client->display_name . ' (' . $client->client_number . ') KYC status changed to ' . $request->kyc_status,
            $client,
            'kyc_status_updated'
        );

        return redirect()->route('clients.show', $client)
            ->with('success', 'KYC status updated successfully.');
    }

    /**
     * Generate new client number
     */
    public function generateClientNumber(Request $request)
    {
        return response()->json([
            'client_number' => Client::generateClientNumber()
        ]);
    }
}
