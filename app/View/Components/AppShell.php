<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppShell extends Component
{
    public string $title;
    public string $header;

    /**
     * Create a new component instance.
     */
    public function __construct(string $title = 'Dashboard', string $header = 'Dashboard')
    {
        $this->title = $title;
        $this->header = $header;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.app-shell');
    }
}
