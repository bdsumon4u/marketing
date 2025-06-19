<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Component;

class ReferralTreeNode extends Component
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
        return view('livewire.referral-tree-node', [
            'panelId' => Filament::getCurrentOrDefaultPanel()->getId(),
        ]);
    }
}
