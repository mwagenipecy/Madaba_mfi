<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class CreateBranch extends Component
{
    public $name = '';
    public $code = '';
    public $description = '';
    public $address = '';
    public $city = '';
    public $state = '';
    public $country = '';
    public $postal_code = '';
    public $phone = '';
    public $email = '';
    public $manager_name = '';
    public $manager_email = '';
    public $manager_phone = '';
    public $status = 'active';
    public $established_date = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:50|unique:branches,code',
        'description' => 'nullable|string|max:1000',
        'address' => 'nullable|string|max:500',
        'city' => 'nullable|string|max:100',
        'state' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'postal_code' => 'nullable|string|max:20',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'manager_name' => 'nullable|string|max:255',
        'manager_email' => 'nullable|email|max:255',
        'manager_phone' => 'nullable|string|max:20',
        'status' => 'required|in:active,inactive,suspended',
        'established_date' => 'nullable|date',
    ];

    protected $messages = [
        'name.required' => 'Branch name is required.',
        'code.unique' => 'This branch code already exists.',
        'email.email' => 'Please enter a valid email address.',
        'status.required' => 'Status is required.',
    ];

    public function saveBranch()
    {
        // Auto-generate branch code if not provided
        if (empty($this->code)) {
            $this->code = $this->generateBranchCode();
        }

        $this->validate();

        try {
            Branch::create([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
                'phone' => $this->phone,
                'email' => $this->email,
                'manager_name' => $this->manager_name,
                'manager_email' => $this->manager_email,
                'manager_phone' => $this->manager_phone,
                'status' => $this->status,
                'established_date' => $this->established_date ? \Carbon\Carbon::parse($this->established_date) : null,
                'organization_id' => Auth::user()->organization_id ?? 1,
            ]);

            session()->flash('success', 'Branch created successfully.');
            $this->reset();
            return redirect()->route('branches.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create branch. Please try again.');
        }
    }

    private function generateBranchCode()
    {
        $organizationId = Auth::user()->organization_id ?? 1;
        
        // Get the count of existing branches for this organization
        $branchCount = Branch::where('organization_id', $organizationId)->count();
        
        // Generate code like BR001, BR002, etc.
        $nextNumber = $branchCount + 1;
        $code = 'BR' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        // Ensure the code is unique
        while (Branch::where('code', $code)->exists()) {
            $nextNumber++;
            $code = 'BR' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
        
        return $code;
    }

    public function render()
    {
        return view('livewire.create-branch');
    }
}
