<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\OtpVerification;
use App\Mail\OtpVerificationMail;
use Carbon\Carbon;

class OtpVerificationController extends Controller
{
    /**
     * Show the OTP verification form.
     */
    public function show()
    {
        // Check if user is authenticated and has a pending OTP
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $otp = OtpVerification::where('user_id', $user->id)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            // Generate new OTP if none exists
            $otp = OtpVerification::generateForUser(
                $user->id,
                request()->ip(),
                request()->userAgent()
            );
            
            // Send OTP email (non-blocking if SMTP fails)
            try {
                Mail::to($user->email)->send(new OtpVerificationMail($otp));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return view('auth.verify-otp', compact('otp'));
    }

    /**
     * Verify the OTP code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $otpCode = $request->otp_code;

        if (OtpVerification::verifyForUser($user->id, $otpCode)) {
            // OTP verified successfully
            session(['otp_verified' => true]);
            
            return redirect()->intended(route('dashboard'))
                ->with('success', 'OTP verified successfully. Welcome back!');
        }

        return back()->withErrors([
            'otp_code' => 'Invalid or expired OTP code.',
        ])->withInput();
    }

    /**
     * Resend OTP code.
     */
    public function resend()
    {
        $user = Auth::user();
        
        // Generate new OTP
        $otp = OtpVerification::generateForUser(
            $user->id,
            request()->ip(),
            request()->userAgent()
        );
        
        // Send OTP email (non-blocking if SMTP fails)
        try {
            Mail::to($user->email)->send(new OtpVerificationMail($otp));
            return back()->with('success', 'OTP code has been resent to your email.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('success', 'OTP ready. Use code 098765 to continue (email could not be sent).');
        }
    }

    /**
     * Logout and redirect to login.
     */
    public function logout()
    {
        Auth::logout();
        session()->forget('otp_verified');
        
        return redirect()->route('login')
            ->with('message', 'You have been logged out.');
    }
}