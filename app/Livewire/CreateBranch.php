<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class CreateBranch extends Component
{
    public $name = '';
    public $code = '';
    public $address = '';
    public $phone = '';
    public $email = '';
    public $manager_name = '';
    public $status = 'active';

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:branches,code',
        'address' => 'nullable|string|max:500',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'manager_name' => 'nullable|string|max:255',
        'status' => 'required|in:active,inactive',
    ];

    protected $messages = [
        'name.required' => 'Branch name is required.',
        'code.required' => 'Branch code is required.',
        'code.unique' => 'This branch code already exists.',
        'email.email' => 'Please enter a valid email address.',
        'status.required' => 'Status is required.',
    ];

    public function saveBranch()
    {
        $this->validate();

        try {
            Branch::create([
                'name' => $this->name,
                'code' => $this->code,
                'address' => $this->address,
                'phone' => $this->phone,
                'email' => $this->email,
                'manager_name' => $this->manager_name,
                'status' => $this->status,
                'organization_id' => Auth::user()->organization_id ?? 1,
            ]);

            session()->flash('message', 'Branch created successfully.');
            $this->reset();
            $this->dispatch('closeModal');
            $this->dispatch('branchCreated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create branch. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.create-branch');
    }
}
