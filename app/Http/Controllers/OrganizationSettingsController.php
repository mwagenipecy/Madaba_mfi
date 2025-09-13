<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OrganizationSettingsController extends Controller
{
    /**
     * Display organization settings dashboard
     */
    public function index()
    {
        return view('organization-settings.index');
    }

    /**
     * Display organization details (self-view)
     */
    public function details()
    {
        return view('organization-settings.details');
    }

    /**
     * Show organization's own users
     */
    public function users()
    {
        return view('organization-settings.users');
    }

    /**
     * Show form to create user for own organization
     */
    public function createUser()
    {
        return view('organization-settings.create-user');
    }

    /**
     * Show form to edit own organization
     */
    public function edit()
    {
        return view('organization-settings.edit');
    }
}
