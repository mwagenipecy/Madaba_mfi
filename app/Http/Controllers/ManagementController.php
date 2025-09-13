<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SystemLog;

class ManagementController extends Controller
{
    /**
     * Display system users management
     */
    public function users()
    {
        return view('management.users');
    }

    /**
     * Display system logs
     */
    public function systemLogs()
    {
        return view('management.system-logs');
    }

    /**
     * Disable a user
     */
    public function disableUser(User $user)
    {
        $user->delete(); // Soft delete
        
        SystemLog::log(
            'user_disabled',
            "User {$user->name} ({$user->email}) has been disabled",
            'warning',
            $user,
            auth()->id()
        );

        return redirect()->back()->with('success', 'User has been disabled successfully.');
    }

    /**
     * Activate a user
     */
    public function activateUser($userId)
    {
        $user = User::withTrashed()->findOrFail($userId);
        $user->restore(); // Restore from soft delete
        
        SystemLog::log(
            'user_activated',
            "User {$user->name} ({$user->email}) has been activated",
            'info',
            $user,
            auth()->id()
        );

        return redirect()->back()->with('success', 'User has been activated successfully.');
    }
}
