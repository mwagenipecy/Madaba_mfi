<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrganizationSettingController extends Controller
{
    public function showOnboardingForm(){

        return view('admin.organization.organization-onboarding');
    }
}
