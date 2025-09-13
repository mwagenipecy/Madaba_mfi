<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;

class EditBranch extends Component
{
    public $branch;
    public $name = '';
    public $code = '';
    public $address = '';
    public $phone = '';
    public $email = '';
    public $manager_name = '';
    public $status = 'active';

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50',
        'address' => 'nullable|string|max:500',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'manager_name' => 'nullable|string|max:255',
        'status' => 'required|in:active,inactive',
    ];

    protected $messages = [
        'name.required' => 'Branch name is required.',
        'code.required' => 'Branch code is required.',
        'email.email' => 'Please enter a valid email address.',
        'status.required' => 'Status is required.',
    ];

    public function mount($branchId = null)
    {
        if ($branchId) {
            $this->branch = Branch::findOrFail($branchId);
            $this->name = $this->branch->name;
            $this->code = $this->branch->code;
            $this->address = $this->branch->address;
            $this->phone = $this->branch->phone;
            $this->email = $this->branch->email;
            $this->manager_name = $this->branch->manager_name;
            $this->status = $this->branch->status;
        }
    }

    public function updateBranch()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $this->branch->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $this->branch->update([
                'name' => $this->name,
                'code' => $this->code,
                'address' => $this->address,
                'phone' => $this->phone,
                'email' => $this->email,
                'manager_name' => $this->manager_name,
                'status' => $this->status,
            ]);

            session()->flash('message', 'Branch updated successfully.');
            $this->dispatch('closeModal');
            $this->dispatch('branchUpdated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update branch. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.edit-branch');
    }
}
