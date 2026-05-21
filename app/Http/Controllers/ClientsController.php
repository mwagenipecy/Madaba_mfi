<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientsController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get client statistics
        $totalClients = Client::where('organization_id', $organizationId)->enabled()->count();
        $individualClients = Client::where('organization_id', $organizationId)
            ->enabled()
            ->where('client_type', 'individual')->count();
        $businessClients = Client::where('organization_id', $organizationId)
            ->enabled()
            ->whereIn('client_type', ['business', 'group'])->count();
        $pendingKyc = Client::where('organization_id', $organizationId)
            ->enabled()
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
            ->enabled()
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
            ->enabled()
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
        // Get user's organization (required)
        $userOrganizationId = auth()->user()->organization_id;
        if (!$userOrganizationId) {
            return redirect()->route('dashboard')->with('error', 'You must be assigned to an organization to create clients.');
        }
        
        $userOrganization = Organization::findOrFail($userOrganizationId);
        
        // Get branches that belong to user's organization
        $branches = Branch::where('organization_id', $userOrganizationId)
                          ->where('status', 'active')
                          ->orderBy('name')
                          ->get();
        
        return view('clients.create', compact('userOrganization', 'branches'));
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        // Get user's organization
        $userOrganizationId = auth()->user()->organization_id;
        if (!$userOrganizationId) {
            return redirect()->route('dashboard')->with('error', 'You must be assigned to an organization to create clients.');
        }
        
        // Prepare validation rules based on client type
        $rules = [
            'client_type' => 'required|in:individual,group,business',
            'branch_id' => 'nullable|exists:branches,id',
            
            // Individual fields
            'first_name' => 'required_if:client_type,individual|string|max:255|min:2',
            'last_name' => 'required_if:client_type,individual|string|max:255|min:2',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required_if:client_type,individual|date|before:today|after:1900-01-01',
            'gender' => 'required_if:client_type,individual|in:male,female,other',
            'national_id' => 'nullable|string|max:50|unique:clients,national_id',
            'passport_number' => 'nullable|string|max:50|unique:clients,passport_number',
        ];

        // Add business/group fields validation only if not individual
        if ($request->client_type !== 'individual') {
            $rules['business_name'] = 'required|string|max:255|min:2';
            $rules['business_type'] = 'required|in:sole_proprietorship,partnership,corporation,cooperative,ngo,other';
        } else {
            // For individual, make these nullable to avoid validation errors
            $rules['business_name'] = 'nullable|string|max:255';
            $rules['business_type'] = 'nullable|in:sole_proprietorship,partnership,corporation,cooperative,ngo,other';
        }
        
        $rules['business_registration_number'] = 'nullable|string|max:100|unique:clients,business_registration_number';
        
        // Contact information
        $rules['phone_number'] = 'required|string|max:20|min:10|unique:clients,phone_number';
        $rules['secondary_phone'] = 'nullable|string|max:20|min:10';
        $rules['email'] = 'nullable|email|max:255|unique:clients,email';
        $rules['physical_address'] = 'required|string|min:10|max:500';
        $rules['city'] = 'required|string|max:100|min:2';
        $rules['region'] = 'required|string|max:100|min:2';
        $rules['country'] = 'nullable|string|max:100';
        $rules['postal_code'] = 'nullable|string|max:20';
        
        // Financial information
        $rules['monthly_income'] = 'nullable|numeric|min:0|max:999999999.99';
        $rules['income_source'] = 'nullable|string|max:255';
        $rules['employer_name'] = 'nullable|string|max:255';
        $rules['employment_address'] = 'nullable|string|max:500';
        $rules['bank_name'] = 'nullable|string|max:255';
        $rules['bank_account_number'] = 'nullable|string|max:100';
        
        // Emergency contact
        $rules['emergency_contact_name'] = 'nullable|string|max:255|min:2';
        $rules['emergency_contact_phone'] = 'nullable|string|max:20|min:10';
        $rules['emergency_contact_relationship'] = 'nullable|string|max:100';
        
        // Additional information
        $rules['marital_status'] = 'nullable|in:single,married,divorced,widowed';
        $rules['dependents'] = 'nullable|integer|min:0|max:50';
        $rules['occupation'] = 'nullable|string|max:255';
        $rules['business_description'] = 'nullable|string|max:1000';
        $rules['years_in_business'] = 'nullable|integer|min:0|max:100';
        $rules['annual_turnover'] = 'nullable|numeric|min:0|max:9999999999.99';
        $rules['notes'] = 'nullable|string|max:1000';
        
        // KYC Documents validation - only validate if files are uploaded
        if ($request->hasFile('kyc_documents')) {
            $rules['kyc_documents'] = 'array';
            $rules['kyc_documents.*'] = 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240'; // 10MB max per file
            $rules['kyc_document_types'] = 'required|array|size:' . count($request->file('kyc_documents'));
            $rules['kyc_document_types.*'] = 'required|string|max:255';
            $rules['kyc_document_descriptions'] = 'nullable|array';
            $rules['kyc_document_descriptions.*'] = 'nullable|string|max:1000';
        }

        $request->validate($rules, [
            'client_type.required' => 'Please select a client type.',
            'client_type.in' => 'Invalid client type selected.',
            'branch_id.exists' => 'Selected branch does not exist or does not belong to your organization.',
            'first_name.required_if' => 'First name is required for individual clients.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'last_name.required_if' => 'Last name is required for individual clients.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'date_of_birth.required_if' => 'Date of birth is required for individual clients.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'date_of_birth.after' => 'Date of birth must be after 1900.',
            'gender.required_if' => 'Gender is required for individual clients.',
            'gender.in' => 'Invalid gender selected.',
            'national_id.unique' => 'This national ID is already registered.',
            'passport_number.unique' => 'This passport number is already registered.',
            'business_name.required_if' => 'Business/Group name is required.',
            'business_name.min' => 'Business/Group name must be at least 2 characters.',
            'business_registration_number.unique' => 'This registration number is already used.',
            'business_type.required_if' => 'Business type is required.',
            'business_type.in' => 'Invalid business type selected.',
            'phone_number.required' => 'Primary phone number is required.',
            'phone_number.min' => 'Phone number must be at least 10 digits.',
            'phone_number.unique' => 'This phone number is already registered.',
            'secondary_phone.min' => 'Secondary phone number must be at least 10 digits.',
            'email.unique' => 'This email address is already registered.',
            'physical_address.required' => 'Physical address is required.',
            'physical_address.min' => 'Physical address must be at least 10 characters.',
            'city.required' => 'City is required.',
            'city.min' => 'City name must be at least 2 characters.',
            'region.required' => 'Region is required.',
            'region.min' => 'Region name must be at least 2 characters.',
            'monthly_income.max' => 'Monthly income cannot exceed 999,999,999.99.',
            'annual_turnover.max' => 'Annual turnover cannot exceed 9,999,999,999.99.',
            'dependents.max' => 'Number of dependents cannot exceed 50.',
            'years_in_business.max' => 'Years in business cannot exceed 100.',
        ]);
        
        // Additional validation: ensure branch belongs to user's organization
        if ($request->branch_id) {
            $branch = Branch::find($request->branch_id);
            if (!$branch || $branch->organization_id !== $userOrganizationId) {
                return redirect()->back()
                    ->withErrors(['branch_id' => 'Selected branch does not belong to your organization.'])
                    ->withInput();
            }
        }

        $client = Client::create([
            'client_number' => Client::generateClientNumber(),
            'client_type' => $request->client_type,
            'organization_id' => $userOrganizationId,
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
            'created_by' => Auth::id(),
        ]);

        // Handle KYC document uploads with compression
        $kycDocuments = [];
        if ($request->hasFile('kyc_documents')) {
            $documentTypes = $request->input('kyc_document_types', []);
            $documentDescriptions = $request->input('kyc_document_descriptions', []);
            
            foreach ($request->file('kyc_documents') as $index => $file) {
                if ($file && $file->isValid()) {
                    $originalSize = $file->getSize();
                    $mimeType = $file->getMimeType();
                    $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                    $extension = $file->getClientOriginalExtension();
                    
                    // Compress images if they are image files
                    if (strpos($mimeType, 'image/') === 0 && in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                        $path = $this->compressAndStoreImage($file, $filename, 'client_kyc_documents');
                        $finalSize = filesize(storage_path('app/public/' . $path));
                    } else {
                        // For non-image files, store as-is
                        $path = $file->storeAs('client_kyc_documents', $filename, 'public');
                        $finalSize = filesize(storage_path('app/public/' . $path));
                    }
                    
                    $kycDocuments[] = [
                        'id' => uniqid(),
                        'name' => $file->getClientOriginalName(),
                        'type' => $documentTypes[$index] ?? 'other',
                        'description' => $documentDescriptions[$index] ?? null,
                        'filename' => $filename,
                        'path' => $path,
                        'size' => $finalSize,
                        'original_size' => $originalSize,
                        'mime_type' => $mimeType,
                        'uploaded_by' => auth()->id(),
                        'uploaded_at' => now()->toISOString(),
                        'status' => 'pending'
                    ];
                }
            }
            
            // Update client with KYC documents
            if (!empty($kycDocuments)) {
                $client->update(['kyc_documents' => $kycDocuments]);
                
                // Log document uploads
                SystemLog::log(
                    'KYC documents uploaded',
                    'Uploaded ' . count($kycDocuments) . ' KYC document(s) for client ' . $client->client_number,
                    'info',
                    $client,
                    Auth::id(),
                    ['uploaded_documents' => array_map(function($doc) {
                        return $doc['name'] ?? 'Unknown';
                    }, $kycDocuments)]
                );
            }
        }

        // Log the client creation
        SystemLog::log(
            'Client created',
            'Client ' . $client->display_name . ' (' . $client->client_number . ') was created',
            'info',
            $client,
            Auth::id(),
            ['client_type' => $client->client_type, 'organization_id' => $client->organization_id]
        );

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client created successfully. KYC verification is pending.');
    }

    /**
     * Display the specified client
     */
    public function show(Client $client)
    {
        $client->load(['organization', 'branch', 'verifiedBy', 'createdBy', 'updatedBy', 'loans.loanProduct']);
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
        // Ensure user can only update clients from their organization
        if ($client->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized access to client.');
        }
        
        // Prepare validation rules
        $rules = [
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
        ];

        // Add business/group fields validation only if not individual
        if ($request->client_type !== 'individual') {
            $rules['business_name'] = 'required|string|max:255';
            $rules['business_type'] = 'required|in:sole_proprietorship,partnership,corporation,cooperative,ngo,other';
        } else {
            // For individual, make these nullable to avoid validation errors
            $rules['business_name'] = 'nullable|string|max:255';
            $rules['business_type'] = 'nullable|in:sole_proprietorship,partnership,corporation,cooperative,ngo,other';
        }
        
        $rules['business_registration_number'] = 'nullable|string|max:100';
        
        // Contact information
        $rules['phone_number'] = 'required|string|max:20';
        $rules['secondary_phone'] = 'nullable|string|max:20';
        $rules['email'] = 'nullable|email|max:255';
        $rules['physical_address'] = 'required|string';
        $rules['city'] = 'required|string|max:100';
        $rules['region'] = 'required|string|max:100';
        $rules['country'] = 'nullable|string|max:100';
        $rules['postal_code'] = 'nullable|string|max:20';
        
        // Financial information
        $rules['monthly_income'] = 'nullable|numeric|min:0';
        $rules['income_source'] = 'nullable|string|max:255';
        $rules['employer_name'] = 'nullable|string|max:255';
        $rules['employment_address'] = 'nullable|string';
        $rules['bank_name'] = 'nullable|string|max:255';
        $rules['bank_account_number'] = 'nullable|string|max:100';
        
        // Emergency contact
        $rules['emergency_contact_name'] = 'nullable|string|max:255';
        $rules['emergency_contact_phone'] = 'nullable|string|max:20';
        $rules['emergency_contact_relationship'] = 'nullable|string|max:100';
        
        // Additional information
        $rules['marital_status'] = 'nullable|in:single,married,divorced,widowed';
        $rules['dependents'] = 'nullable|integer|min:0';
        $rules['occupation'] = 'nullable|string|max:255';
        $rules['business_description'] = 'nullable|string';
        $rules['years_in_business'] = 'nullable|integer|min:0';
        $rules['annual_turnover'] = 'nullable|numeric|min:0';
        $rules['notes'] = 'nullable|string';
        
        // KYC Documents validation - only validate if files are uploaded
        $hasValidFiles = false;
        if ($request->hasFile('kyc_documents')) {
            // Filter out empty file inputs
            $files = array_filter($request->file('kyc_documents'), function($file) {
                return $file && $file->isValid();
            });
            
            if (!empty($files)) {
                $hasValidFiles = true;
                $rules['kyc_documents'] = 'array';
                $rules['kyc_documents.*'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240'; // 10MB max per file, nullable to allow empty inputs
                $rules['kyc_document_types'] = 'nullable|array';
                $rules['kyc_document_types.*'] = 'nullable|string|max:255';
                $rules['kyc_document_descriptions'] = 'nullable|array';
                $rules['kyc_document_descriptions.*'] = 'nullable|string|max:1000';
            }
        }
        
        $request->validate($rules);

        // Handle removed documents first
        $existingDocuments = $client->kyc_documents ?? [];
        $removedIndices = json_decode($request->input('removed_documents', '[]'), true) ?? [];
        
        if (!empty($removedIndices)) {
            $removedDocs = [];
            foreach ($removedIndices as $index) {
                if (isset($existingDocuments[$index])) {
                    $removedDocs[] = $existingDocuments[$index];
                    // Delete the file
                    if (isset($existingDocuments[$index]['path'])) {
                        $filePath = storage_path('app/public/' . $existingDocuments[$index]['path']);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
            }
            
            // Remove documents from array
            $existingDocuments = array_values(array_filter($existingDocuments, function($index) use ($removedIndices) {
                return !in_array($index, $removedIndices);
            }, ARRAY_FILTER_USE_KEY));
            
            // Log document removals
            if (!empty($removedDocs)) {
                SystemLog::log(
                    'KYC documents removed',
                    'Removed ' . count($removedDocs) . ' KYC document(s) from client ' . $client->client_number,
                    'info',
                    $client,
                    Auth::id(),
                    ['removed_documents' => array_map(function($doc) {
                        return $doc['name'] ?? 'Unknown';
                    }, $removedDocs)]
                );
            }
        }

        // Handle new KYC document uploads
        $newDocuments = [];
        if ($hasValidFiles) {
            $documentTypes = $request->input('kyc_document_types', []);
            $documentDescriptions = $request->input('kyc_document_descriptions', []);
            
            // Process each file input
            foreach ($request->file('kyc_documents') as $index => $file) {
                // Skip empty file inputs
                if (!$file || !$file->isValid()) {
                    continue;
                }
                
                try {
                    $originalSize = $file->getSize();
                    $mimeType = $file->getMimeType();
                    $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                    $extension = $file->getClientOriginalExtension();
                    
                    // Compress images if they are image files
                    if (strpos($mimeType, 'image/') === 0 && in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                        $path = $this->compressAndStoreImage($file, $filename, 'client_kyc_documents');
                        $finalSize = filesize(storage_path('app/public/' . $path));
                    } else {
                        // For non-image files, store as-is
                        $path = $file->storeAs('client_kyc_documents', $filename, 'public');
                        $finalSize = filesize(storage_path('app/public/' . $path));
                    }
                    
                    $newDocuments[] = [
                        'id' => uniqid(),
                        'name' => $file->getClientOriginalName(),
                        'type' => isset($documentTypes[$index]) && !empty($documentTypes[$index]) ? $documentTypes[$index] : 'other',
                        'description' => isset($documentDescriptions[$index]) ? $documentDescriptions[$index] : null,
                        'filename' => $filename,
                        'path' => $path,
                        'size' => $finalSize,
                        'original_size' => $originalSize,
                        'mime_type' => $mimeType,
                        'uploaded_by' => auth()->id(),
                        'uploaded_at' => now()->toISOString(),
                        'status' => 'pending'
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error uploading KYC document: ' . $e->getMessage());
                    continue; // Skip this file and continue with others
                }
            }
            
            // Log document uploads
            if (!empty($newDocuments)) {
                SystemLog::log(
                    'KYC documents uploaded',
                    'Uploaded ' . count($newDocuments) . ' new KYC document(s) for client ' . $client->client_number,
                    'info',
                    $client,
                    Auth::id(),
                    ['uploaded_documents' => array_map(function($doc) {
                        return $doc['name'] ?? 'Unknown';
                    }, $newDocuments)]
                );
            }
        }

        // Merge existing and new documents
        $allDocuments = array_merge($existingDocuments, $newDocuments);

        // Prepare update data - include documents in the update
        $updateData = $request->except(['kyc_documents', 'kyc_document_types', 'kyc_document_descriptions', 'removed_documents']);
        $updateData['kyc_documents'] = $allDocuments;
        $updateData['updated_by'] = Auth::id();
        
        // Log document state before update
        \Log::info('Updating client documents', [
            'client_id' => $client->id,
            'existing_count' => count($existingDocuments),
            'new_count' => count($newDocuments),
            'removed_count' => count($removedIndices),
            'total_documents' => count($allDocuments)
        ]);
        
        // Update client with all fields including documents in one go
        $client->update($updateData);
        
        // Refresh to ensure we have the latest data
        $client->refresh();
        
        // Verify documents were saved
        \Log::info('Client documents after update', [
            'client_id' => $client->id,
            'saved_documents_count' => count($client->kyc_documents ?? [])
        ]);

        // Log the client update
        SystemLog::log(
            'Client updated',
            'Client ' . $client->display_name . ' (' . $client->client_number . ') was updated',
            'info',
            $client,
            Auth::id(),
            ['client_type' => $client->client_type, 'changes' => array_keys($updateData)]
        );

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Disable the specified client (sets status to disabled)
     */
    public function disable(Client $client)
    {
        if ($client->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized access to client.');
        }

        if ($client->status === 'disabled') {
            return redirect()->route('clients.show', $client)
                ->with('info', 'Client is already disabled.');
        }

        $client->update([
            'status' => 'disabled',
            'updated_by' => Auth::id(),
        ]);

        SystemLog::log(
            'Client disabled',
            'Client ' . $client->display_name . ' (' . $client->client_number . ') was disabled',
            'warning',
            $client,
            Auth::id(),
            ['client_type' => $client->client_type]
        );

        return redirect()->route('clients.index')
            ->with('success', 'Client has been disabled successfully.');
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
            'info',
            $client,
            Auth::id(),
            ['kyc_status' => $request->kyc_status, 'kyc_notes' => $request->kyc_notes]
        );

        return redirect()->route('clients.show', $client)
            ->with('success', 'KYC status updated successfully.');
    }

    /**
     * Compress and store image file
     */
    private function compressAndStoreImage($file, $filename, $directory = 'client_kyc_documents', $quality = 75, $maxWidth = 1920, $maxHeight = 1920)
    {
        try {
            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                // Fallback: store original if GD is not available
                return $file->storeAs($directory, $filename, 'public');
            }

            $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
            if (!$image) {
                // Fallback: store original if image creation fails
                return $file->storeAs($directory, $filename, 'public');
            }

            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            // Calculate new dimensions maintaining aspect ratio
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);

            // Only resize if image is larger than max dimensions
            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preserve transparency for PNG
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                
                imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                imagedestroy($image);
                $image = $resizedImage;
            }

            // Determine file type and save
            $extension = strtolower($file->getClientOriginalExtension());
            $fullPath = storage_path('app/public/' . $directory . '/' . $filename);
            
            // Ensure directory exists
            Storage::disk('public')->makeDirectory($directory);

            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, $fullPath, $quality);
                    break;
                case 'png':
                    // PNG quality is 0-9, convert from 0-100
                    $pngQuality = (int)(9 - ($quality / 100) * 9);
                    imagepng($image, $fullPath, $pngQuality);
                    break;
                default:
                    // Fallback: store original
                    imagedestroy($image);
                    return $file->storeAs($directory, $filename, 'public');
            }

            imagedestroy($image);
            
            return $directory . '/' . $filename;
        } catch (\Exception $e) {
            // Fallback: store original if compression fails
            \Log::warning('Image compression failed: ' . $e->getMessage());
            return $file->storeAs($directory, $filename, 'public');
        }
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
