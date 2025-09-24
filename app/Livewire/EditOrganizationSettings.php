<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;

class EditOrganizationSettings extends Component
{
    use WithFileUploads;

    public ?Organization $organization = null;

    public $name;
    public $registration_number;
    public $email;
    public $phone;
    public $address;
    public $city;
    public $state;
    public $country;
    public $postal_code;
    public $website;
    public $logo; // uploaded file

    public function mount()
    {
        $orgId = Auth::user()?->organization_id;
        $this->organization = Organization::find($orgId);

        if ($this->organization) {
            $this->name = $this->organization->name;
            $this->registration_number = $this->organization->registration_number;
            $this->email = $this->organization->email;
            $this->phone = $this->organization->phone;
            $this->address = $this->organization->address;
            $this->city = $this->organization->city;
            $this->state = $this->organization->state;
            $this->country = $this->organization->country;
            $this->postal_code = $this->organization->postal_code;
            $this->website = $this->organization->website;
        }
    }

    public function updateOrganization()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $this->name,
            'registration_number' => $this->registration_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'website' => $this->website,
        ];

        if ($this->logo) {
            $path = $this->logo->store('organization-logos', 'public');
            $data['logo_path'] = $path;
        }

        $this->organization->update($data);

        session()->flash('message', 'Organization updated successfully.');
    }

    public function render()
    {
        return view('livewire.edit-organization-settings');
    }
}
