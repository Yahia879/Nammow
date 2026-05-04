<?php

namespace App\Livewire\Sections\Menu;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VerticalMenu extends Component
{
    public function render()
    {
        return view('livewire.sections.menu.vertical-menu');
    }
}

