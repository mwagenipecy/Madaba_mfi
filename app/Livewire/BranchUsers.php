<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Branch;

class BranchUsers extends Component
{
    public $branchId;

    public function mount($branchId = null)
    {
        $this->branchId = $branchId;
    }

    public function render()
    {
        $query = User::whereNotNull('branch_id');
        
        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }
        
        $users = $query->with('branch')->latest()->paginate(10);
        
        return view('livewire.branch-users', [
            'users' => $users
        ]);
    }

    public function activateUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->update(['email_verified_at' => now()]);
            session()->flash('message', 'User activated successfully.');
        }
    }

    public function suspendUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->update(['email_verified_at' => null]);
            session()->flash('message', 'User suspended successfully.');
        }
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->delete();
            session()->flash('message', 'User deleted successfully.');
        }
    }
}
