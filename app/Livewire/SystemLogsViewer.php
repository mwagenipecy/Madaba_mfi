<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SystemLog;

class SystemLogsViewer extends Component
{
    use WithPagination;

    public $search = '';
    public $levelFilter = '';
    public $actionFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'levelFilter' => ['except' => ''],
        'actionFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function render()
    {
        $query = SystemLog::with('user');

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('action', 'like', '%' . $this->search . '%');
            });
        }

        // Apply level filter
        if ($this->levelFilter) {
            $query->where('level', $this->levelFilter);
        }

        // Apply action filter
        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        // Apply date filters
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);
        } elseif ($this->dateFrom) {
            $query->where('created_at', '>=', $this->dateFrom);
        } elseif ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo);
        }

        $logs = $query->latest()->paginate(20);

        return view('livewire.system-logs-viewer', [
            'logs' => $logs
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLevelFilter()
    {
        $this->resetPage();
    }

    public function updatingActionFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }
}
