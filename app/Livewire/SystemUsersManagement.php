<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SystemUsersManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';
    public $organizationFilter = '';

    // Modal properties
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showResetPasswordModal = false;
    public $showDeleteModal = false;
    public $selectedUserId = null;
    public $selectedUserName = '';

    // Create/Edit user properties
    public $firstName = '';
    public $lastName = '';
    public $email = '';
    public $phone = '';
    public $role = 'user';
    public $organizationId = '';
    public $employeeId = '';
    public $status = 'active';

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
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
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

    public function mount()
    {
        // Set default organization to current user's organization
        $this->organizationId = Auth::user()->organization_id;
    }

    public function openCreateModal()
    {
        $this->reset(['firstName', 'lastName', 'email', 'phone', 'role', 'employeeId', 'status']);
        $this->organizationId = Auth::user()->organization_id;
        $this->role = 'user'; // Default role
        $this->status = 'active'; // Default status
        $this->showCreateModal = true;
    }

    public function openEditModal($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $this->selectedUserId = $userId;
            $this->firstName = $user->first_name;
            $this->lastName = $user->last_name;
            $this->email = $user->email;
            $this->phone = $user->phone;
            $this->role = $user->role;
            $this->organizationId = $user->organization_id;
            $this->employeeId = $user->employee_id;
            $this->status = $user->status;
            $this->showEditModal = true;
        }
    }

    public function openResetPasswordModal($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $this->selectedUserId = $userId;
            $this->selectedUserName = $user->first_name . ' ' . $user->last_name;
            $this->showResetPasswordModal = true;
        }
    }

    public function openDeleteModal($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $this->selectedUserId = $userId;
            $this->selectedUserName = $user->first_name . ' ' . $user->last_name;
            $this->showDeleteModal = true;
        }
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showResetPasswordModal = false;
        $this->showDeleteModal = false;
        $this->reset(['firstName', 'lastName', 'email', 'phone', 'role', 'employeeId', 'status', 'selectedUserId', 'selectedUserName']);
    }

    public function createUser()
    {
        // Force organization to current user's organization
        $this->organizationId = Auth::user()->organization_id;
        
        $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:admin,manager,officer,user',
            'employeeId' => 'nullable|string|max:50',
            'status' => 'required|string|in:active,inactive',
        ]);

        $user = User::create([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'organization_id' => $this->organizationId, // Always use current user's organization
            'employee_id' => $this->employeeId,
            'status' => $this->status,
            'password' => Hash::make('password123'), // Default password
            'email_verified_at' => $this->status === 'active' ? now() : null,
        ]);

        session()->flash('message', 'User created successfully with default organization.');
        $this->closeModals();
    }

    public function updateUser()
    {
        $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->selectedUserId,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:admin,manager,officer,user',
            'organizationId' => 'required|exists:organizations,id',
            'employeeId' => 'nullable|string|max:50',
            'status' => 'required|string|in:active,inactive',
        ]);

        $user = User::find($this->selectedUserId);
        if ($user) {
            $user->update([
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'phone' => $this->phone,
                'role' => $this->role,
                'organization_id' => $this->organizationId,
                'employee_id' => $this->employeeId,
                'status' => $this->status,
                'email_verified_at' => $this->status === 'active' ? now() : null,
            ]);

            session()->flash('message', 'User updated successfully.');
            $this->closeModals();
        }
    }

    public function resetUserPassword()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
        ]);

        $user = User::find($this->selectedUserId);
        if ($user) {
            $user->update([
                'password' => Hash::make('password123'), // Reset to default password
            ]);

            session()->flash('message', 'Password reset successfully. New password: password123');
            $this->closeModals();
        }
    }

    public function activateUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->update([
                'email_verified_at' => now(),
                'status' => 'active'
            ]);
            session()->flash('message', 'User activated successfully.');
        }
    }

    public function suspendUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->update([
                'email_verified_at' => null,
                'status' => 'inactive'
            ]);
            session()->flash('message', 'User suspended successfully.');
        }
    }

    public function deleteUser()
    {
        $user = User::find($this->selectedUserId);
        if ($user) {
            $user->delete();
            session()->flash('message', 'User deleted successfully.');
            $this->closeModals();
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
