<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateOrganizationUserSettings extends Component
{
    use WithFileUploads;

    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $password;
    public $password_confirmation;
    public $role = 'field_agent';
    public $branch_id;
    public $employee_id;
    public $status = 'active';

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|max:20',
        'password' => 'required|string|min:8|confirmed',
        'role' => 'required|in:super_admin,admin,manager,loan_officer,accountant,cashier,field_agent',
        'branch_id' => 'required|exists:branches,id',
        'employee_id' => 'nullable|string|max:50',
        'status' => 'required|in:active,inactive',
    ];

    public function mount()
    {
        // Set default branch to HQ branch of current user's organization
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->organization_id) {
            $hqBranch = Branch::where('organization_id', $currentUser->organization_id)
                ->where('is_hq', true)
                ->first();
            if ($hqBranch) {
                $this->branch_id = $hqBranch->id;
            }
        }
    }

    public function createUser()
    {
        $this->validate();

        $currentUser = Auth::user();
        
        if (!$currentUser || !$currentUser->organization_id) {
            session()->flash('error', 'Unable to determine organization.');
            return;
        }

        // Verify the selected branch belongs to the current user's organization
        $branch = Branch::where('id', $this->branch_id)
            ->where('organization_id', $currentUser->organization_id)
            ->first();

        if (!$branch) {
            session()->flash('error', 'Selected branch does not belong to your organization.');
            return;
        }

        // Generate employee ID if not provided
        if (empty($this->employee_id)) {
            $orgId = $currentUser->organization_id;
            $userCount = User::where('organization_id', $orgId)->count() + 1;
            $this->employee_id = 'EMP-' . str_pad($orgId, 3, '0', STR_PAD_LEFT) . '-' . str_pad($userCount, 4, '0', STR_PAD_LEFT);
        }

        $userData = [
            'organization_id' => $currentUser->organization_id,
            'branch_id' => $this->branch_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'employee_id' => $this->employee_id,
            'status' => $this->status,
            'permissions' => $this->getDefaultPermissions(),
        ];

        User::create($userData);

        session()->flash('success', 'User created successfully.');
        
        // Reset form
        $this->reset(['first_name', 'last_name', 'email', 'phone', 'password', 'password_confirmation', 'role', 'employee_id']);
        
        // Redirect to users list
        return redirect()->route('organization-settings.users');
    }

    private function getDefaultPermissions()
    {
        return match($this->role) {
            'super_admin' => [
                'manage_users', 'manage_clients', 'manage_loans', 'manage_savings',
                'manage_collections', 'view_reports', 'generate_reports', 'manage_settings',
                'manage_products', 'approve_loans', 'manage_groups', 'manage_transactions',
                'manage_branches', 'manage_accounts', 'manage_approvals', 'manage_expenses',
                'system_backup', 'audit_logs',
            ],
            'admin' => [
                'manage_users', 'manage_clients', 'manage_loans', 'manage_savings',
                'manage_collections', 'view_reports', 'generate_reports', 'manage_settings',
                'manage_products', 'approve_loans', 'manage_groups', 'manage_transactions',
                'manage_branches', 'manage_accounts', 'manage_approvals', 'manage_expenses',
            ],
            'manager' => [
                'manage_clients', 'manage_loans', 'manage_collections', 'view_reports',
                'approve_loans', 'manage_groups', 'manage_transactions', 'manage_accounts',
            ],
            'loan_officer' => [
                'manage_clients', 'manage_loans', 'manage_collections', 'view_reports',
                'manage_transactions',
            ],
            'accountant' => [
                'manage_accounts', 'manage_transactions', 'view_reports', 'generate_reports',
                'manage_collections',
            ],
            'cashier' => [
                'manage_transactions', 'manage_collections', 'view_reports',
            ],
            'field_agent' => [
                'manage_clients', 'view_reports',
            ],
            default => ['view_reports'],
        };
    }

    public function render()
    {
        $currentUser = Auth::user();
        $branches = collect();
        
        if ($currentUser && $currentUser->organization_id) {
            $branches = Branch::where('organization_id', $currentUser->organization_id)
                ->where('status', 'active')
                ->orderBy('is_hq', 'desc')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.create-organization-user-settings', [
            'branches' => $branches,
        ]);
    }
}
