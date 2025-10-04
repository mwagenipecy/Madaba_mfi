<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\SystemLog;

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
            
            SystemLog::log(
                'branch_enabled',
                "Branch enabled: {$branch->name} ({$branch->code})",
                'info',
                $branch,
                auth()->id()
            );
            
            session()->flash('success', 'Branch enabled successfully.');
        }
    }

    public function disableBranch($branchId)
    {
        $branch = Branch::find($branchId);
        if ($branch) {
            $branch->update(['status' => 'inactive']);
            
            SystemLog::log(
                'branch_disabled',
                "Branch disabled: {$branch->name} ({$branch->code})",
                'warning',
                $branch,
                auth()->id()
            );
            
            session()->flash('success', 'Branch disabled successfully.');
        }
    }
}
