<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Organization;

class SystemUsersManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';
    public $organizationFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'organizationFilter' => ['except' => ''],
    ];

    public function render()
    {
        $query = User::with('organization');

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Apply role filter
        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        // Apply status filter
        if ($this->statusFilter) {
            if ($this->statusFilter === 'active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($this->statusFilter === 'inactive') {
                $query->whereNull('email_verified_at');
            }
        }

        // Apply organization filter
        if ($this->organizationFilter) {
            $query->where('organization_id', $this->organizationFilter);
        }

        $users = $query->latest()->paginate(15);
        $organizations = Organization::all();

        return view('livewire.system-users-management', [
            'users' => $users,
            'organizations' => $organizations
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingOrganizationFilter()
    {
        $this->resetPage();
    }
}
