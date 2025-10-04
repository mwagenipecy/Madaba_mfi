<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;

class EditBranch extends Component
{
    public $branch;
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
        'code' => 'required|string|max:50',
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
        'code.required' => 'Branch code is required.',
        'email.email' => 'Please enter a valid email address.',
        'status.required' => 'Status is required.',
    ];

    public function mount($branch = null)
    {
        if ($branch) {
            $this->branch = $branch;
            $this->name = $this->branch->name;
            $this->code = $this->branch->code;
            $this->description = $this->branch->description;
            $this->address = $this->branch->address;
            $this->city = $this->branch->city;
            $this->state = $this->branch->state;
            $this->country = $this->branch->country;
            $this->postal_code = $this->branch->postal_code;
            $this->phone = $this->branch->phone;
            $this->email = $this->branch->email;
            $this->manager_name = $this->branch->manager_name;
            $this->manager_email = $this->branch->manager_email;
            $this->manager_phone = $this->branch->manager_phone;
            $this->status = $this->branch->status;
            $this->established_date = $this->branch->established_date ? $this->branch->established_date->format('Y-m-d') : '';
        }
    }

    public function updateBranch()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $this->branch->id,
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
        ]);

        try {
            $this->branch->update([
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
            ]);

            session()->flash('success', 'Branch updated successfully.');
            return redirect()->route('branches.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update branch. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.edit-branch');
    }
}
