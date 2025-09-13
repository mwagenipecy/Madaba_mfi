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
}
