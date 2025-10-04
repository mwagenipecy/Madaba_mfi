<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OrganizationSettingsUsers extends Component
{
    use WithPagination;

    public $branchId = null;
    public $status = null; // active/pending
    
    // Modal properties
    public $showActivateModal = false;
    public $showSuspendModal = false;
    public $selectedUserId = null;
    public $selectedUserName = '';
    
    // Force refresh property
    public $refreshKey = 0;

    public function render()
    {
        $orgId = Auth::user()?->organization_id;

        $query = User::query()
            ->when($orgId, fn($q) => $q->where('organization_id', $orgId))
            ->with('branch')
            ->orderByDesc('created_at');

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->status === 'active') {
            $query->whereNotNull('email_verified_at');
        } elseif ($this->status === 'pending') {
            $query->whereNull('email_verified_at');
        }

        $users = $query->paginate(10);

        return view('livewire.organization-settings-users', [
            'users' => $users,
        ]);
    }


    public function confirmActivateUser($userId)
    {
        $user = User::find($userId);
        if ($user && $this->ownsUser($user)) {
            $this->selectedUserId = $userId;
            $this->selectedUserName = $user->first_name . ' ' . $user->last_name;
            $this->showActivateModal = true;
        }
    }

    public function confirmSuspendUser($userId)
    {
        $user = User::find($userId);
        if ($user && $this->ownsUser($user)) {
            $this->selectedUserId = $userId;
            $this->selectedUserName = $user->first_name . ' ' . $user->last_name;
            $this->showSuspendModal = true;
        }
    }

    public function activateUser($userId)
    {
        try {
            $user = User::find($userId);
            if ($user && $this->ownsUser($user)) {
                $oldStatus = $user->email_verified_at ? 'Active' : 'Pending';
                
                // Use direct database update to ensure it's saved
                User::where('id', $userId)->update(['email_verified_at' => now()]);
                
                // Verify the change
                $updatedUser = User::find($userId);
                $newStatus = $updatedUser->email_verified_at ? 'Active' : 'Pending';
                
                session()->flash('message', "User activated successfully. Status changed from {$oldStatus} to {$newStatus}.");
                $this->resetModal();
                // Force component refresh
                $this->refreshKey++;
            } else {
                session()->flash('error', 'User not found or unauthorized.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to activate user: ' . $e->getMessage());
        }
    }

    public function suspendUser($userId)
    {
        try {
            $user = User::find($userId);
            if ($user && $this->ownsUser($user)) {
                $oldStatus = $user->email_verified_at ? 'Active' : 'Pending';
                
                // Use direct database update to ensure it's saved
                User::where('id', $userId)->update(['email_verified_at' => null]);
                
                // Verify the change
                $updatedUser = User::find($userId);
                $newStatus = $updatedUser->email_verified_at ? 'Active' : 'Pending';
                
                session()->flash('message', "User suspended successfully. Status changed from {$oldStatus} to {$newStatus}.");
                $this->resetModal();
                // Force component refresh
                $this->refreshKey++;
            } else {
                session()->flash('error', 'User not found or unauthorized.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to suspend user: ' . $e->getMessage());
        }
    }

    private function resetModal()
    {
        $this->showActivateModal = false;
        $this->showSuspendModal = false;
        $this->selectedUserId = null;
        $this->selectedUserName = '';
    }

    public function refreshUsers()
    {
        // This method can be called to force refresh the users data
        $this->render();
    }

    private function ownsUser(User $user): bool
    {
        return Auth::user()?->organization_id === $user->organization_id;
    }
}
