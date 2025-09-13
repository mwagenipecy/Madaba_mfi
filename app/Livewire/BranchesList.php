<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;

class BranchesList extends Component
{
    public function render()
    {
        $organizationId = auth()->user()->organization_id ?? 1;
        $branches = Branch::where('organization_id', $organizationId)->latest()->paginate(10);
        
        return view('livewire.branches-list', [
            'branches' => $branches
        ]);
    }

    public function enableBranch($branchId)
    {
        $branch = Branch::find($branchId);
        if ($branch) {
            $branch->update(['status' => 'active']);
            session()->flash('message', 'Branch enabled successfully.');
        }
    }

    public function disableBranch($branchId)
    {
        $branch = Branch::find($branchId);
        if ($branch) {
            $branch->update(['status' => 'inactive']);
            session()->flash('message', 'Branch disabled successfully.');
        }
    }
}
