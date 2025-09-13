<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class RegistrationFlowController extends Controller
{
    public function step1Store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'terms' => ['accepted'],
        ]);

        // Persist minimal user with pending status; password to be set later after payment
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(str()->random(16)),
            'status' => 'pending',
        ]);

        session(['pending_user_id' => $user->id]);

        return redirect()->route('register.plan');
    }

    public function showPlan(Request $request)
    {
        abort_unless(session()->has('pending_user_id'), 403);

        return view('auth.plan');
    }

    public function planStore(Request $request)
    {
        abort_unless(session()->has('pending_user_id'), 403);

        $validated = $request->validate([
            'plan' => ['required', 'in:basic,standard,premium'],
            'payment_reference' => ['required', 'string', 'max:100'],
        ]);

        $user = User::findOrFail(session('pending_user_id'));
        $user->plan = $validated['plan'];
        $user->payment_reference = $validated['payment_reference'];
        $user->status = 'awaiting_approval';
        $user->save();

        session()->forget('pending_user_id');

        return redirect()->route('login')->with('status', 'Registration submitted. We will approve your access shortly.');
    }
}



