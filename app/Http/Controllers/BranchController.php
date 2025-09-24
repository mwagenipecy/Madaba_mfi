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
        return view('branches.users', compact('branch'));
    }

    /**
     * Show form to create branch user
     */
    public function createUser(Branch $branch)
    {
        return view('branches.create-user', compact('branch'));
    }

    
}
