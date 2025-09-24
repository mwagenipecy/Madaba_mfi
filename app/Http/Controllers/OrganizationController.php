<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
 use App\Models\Organization;
use App\Models\User;

class OrganizationController extends Controller
{
    /**
     * Display a listing of all organizations
     */
    public function index()
    {
        return view('organizations.index');
    }

    /**
     * Display organization profile/details
     */
    public function profile()
    {
        return view('organizations.profile');
    }

    /**
     * Show organization users
     */
    public function users(Organization $organization)
    {
        return view('organizations.users', compact('organization'));
    }

    /**
     * Show form to create organization user
     */
    public function createUser(Organization $organization)
    {
        return view('organizations.create-user', compact('organization'));
    }

    /**
     * Show form to edit organization
     */
    public function edit(Organization $organization)
    {
        return view('organizations.edit', compact('organization'));
    }

    /**
     * Update organization details
     */
    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'registration_number' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:255',
            'state' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|max:255',
            'postal_code' => 'sometimes|string|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        $organization->update($validated);

        return redirect()->route('organizations.index')->with('success', 'Organization updated successfully.');
    }

    /**
     * Deactivate (suspend) organization
     */
    public function deactivate(Organization $organization)
    {
        $organization->suspend();
        return back()->with('success', 'Organization deactivated successfully.');
    }

    /**
     * Reactivate organization
     */
    public function reactivate(Organization $organization)
    {
        $organization->activate();
        return back()->with('success', 'Organization reactivated successfully.');
    }
}
