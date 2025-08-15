<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SidebarLink extends Component
{
    public $active;

    // We now pass the 'active' state directly from the view
    public function __construct($active = false)
    {
        $this->active = $active;
    }

    public function render()
    {
        return view('components.sidebar-link');
    }
}
