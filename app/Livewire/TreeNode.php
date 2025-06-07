<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class TreeNode extends Component
{
    public ?User $user = null;

    public bool $expanded = false;

    public function mount($nodeId)
    {
        $this->user = User::find($nodeId);
    }

    public function expand()
    {
        $this->expanded = true;
        $this->dispatch('expanded');
    }

    public function render()
    {
        return view('livewire.tree-node');
    }
}
