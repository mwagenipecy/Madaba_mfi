<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Organization;

class OrganizationsList extends Component
{
    public function render()
    {
        $organizations = Organization::latest()->paginate(10);
        
        return view('livewire.organizations-list', [
            'organizations' => $organizations
        ]);
    }

    public function enableOrganization($organizationId)
    {
        $organization = Organization::find($organizationId);
        if ($organization) {
            $organization->update(['status' => 'active']);
            session()->flash('message', 'Organization enabled successfully.');
        }
    }

    public function disableOrganization($organizationId)
    {
        $organization = Organization::find($organizationId);
        if ($organization) {
            $organization->update(['status' => 'inactive']);
            session()->flash('message', 'Organization disabled successfully.');
        }
    }
}
