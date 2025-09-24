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

    public function activateUser($userId)
    {
        $user = User::find($userId);
        if ($user && $this->ownsUser($user)) {
            $user->update(['email_verified_at' => now()]);
            session()->flash('message', 'User activated successfully.');
        }
    }

    public function suspendUser($userId)
    {
        $user = User::find($userId);
        if ($user && $this->ownsUser($user)) {
            $user->update(['email_verified_at' => null]);
            session()->flash('message', 'User suspended successfully.');
        }
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if ($user && $this->ownsUser($user)) {
            $user->delete();
            session()->flash('message', 'User deleted successfully.');
        }
    }

    private function ownsUser(User $user): bool
    {
        return Auth::user()?->organization_id === $user->organization_id;
    }
}
