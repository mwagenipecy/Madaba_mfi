<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use App\Models\SystemLog;

class BranchController extends Controller
{
    /**
     * Display a listing of branches
     */


    public function index()
    {
        return view('branches.index');
    }

    /**
     * Show the form for creating a new branch
     */
    public function create()
    {
        return view('branches.create');
    }

    /**
     * Store a newly created branch
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'description' => 'nullable|string',
            'organization_id' => 'required|exists:organizations,id',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
            'established_date' => 'nullable|date',
        ]);

        $branch = Branch::create($validated);

        SystemLog::log(
            'branch_created',
            "Branch created: {$branch->name} ({$branch->code})",
            'info',
            $branch,
            auth()->id()
        );

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    /**
     * Display the specified branch
     */
    public function show(Branch $branch)
    {
        return view('branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the branch
     */
    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified branch
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $branch->id,
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
            'established_date' => 'nullable|date',
        ]);

        $branch->update($validated);

        SystemLog::log(
            'branch_updated',
            "Branch updated: {$branch->name} ({$branch->code})",
            'info',
            $branch,
            auth()->id()
        );

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    /**
     * Disable the specified branch
     */
    public function disable(Branch $branch)
    {
        $branch->delete(); // Soft delete

        SystemLog::log(
            'branch_disabled',
            "Branch disabled: {$branch->name} ({$branch->code})",
            'warning',
            $branch,
            auth()->id()
        );

        return redirect()->back()->with('success', 'Branch has been disabled successfully.');
    }

    /**
     * Show branch users
     */
    public function users(Branch $branch)
    {
        // Load users relationship
        $branch->load('users');
        
        return view('branches.users', compact('branch'));
    }

    /**
     * Show form to create branch user
     */
    public function createUser(Branch $branch)
    {
        return view('branches.create-user', compact('branch'));
    }

    /**
     * Store a new branch user
     */
    public function storeUser(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:super_admin,admin,manager,loan_officer,accountant,cashier,field_agent',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => bcrypt($validated['password']),
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        SystemLog::log(
            'branch_user_created',
            "User created for branch: {$user->first_name} {$user->last_name} ({$user->email}) in {$branch->name}",
            'info',
            $user,
            auth()->id()
        );

        return redirect()->route('branches.users', $branch)
            ->with('success', 'User created successfully.');
    }

    /**
     * Change user status
     */
    public function changeUserStatus(Request $request, Branch $branch, User $user)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if ($user->branch_id === $branch->id) {
            $oldStatus = $user->status;
            $user->update(['status' => $validated['status']]);
            
            SystemLog::log(
                'branch_user_status_changed',
                "User status changed: {$user->full_name} ({$user->email}) from {$oldStatus} to {$validated['status']} in {$branch->name}",
                'warning',
                $user,
                auth()->id()
            );
            
            return redirect()->route('branches.users', $branch)
                ->with('success', 'User status updated successfully.');
        }
        
        return redirect()->route('branches.users', $branch)
            ->with('error', 'User is not assigned to this branch.');
    }
}

