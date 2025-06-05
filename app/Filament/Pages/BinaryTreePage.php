<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;

class BinaryTreePage extends Page
{
    protected static string $view = 'filament.pages.binary-tree-page';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Binary Tree';

    protected static ?string $title = 'Binary Tree Visualization';

    protected static ?int $navigationSort = 1;

    public $baseId = 1001;

    public $expandedNodes = [];

    public function mount()
    {
        $this->expandedNodes = [$this->baseId]; // Start with root node expanded
    }

    public function expandNode($nodeId)
    {
        if (! in_array($nodeId, $this->expandedNodes)) {
            $this->expandedNodes[] = $nodeId;

            $this->dispatch('update-tree');
        }
    }

    public function getNode($nodeId)
    {
        $user = User::find($nodeId);
        if (! $user) {
            return null;
        }

        $leftChild = 2 * ($nodeId - $this->baseId + 1) + $this->baseId - 1;
        $rightChild = $leftChild + 1;

        return [
            'id' => $nodeId,
            'name' => $user->name,
            'username' => $user->username,
            'children' => [
                User::find($leftChild) ? $leftChild : null,
                User::find($rightChild) ? $rightChild : null,
            ],
        ];
    }

    public function isExpanded($nodeId)
    {
        return in_array($nodeId, $this->expandedNodes);
    }

    protected function getViewData(): array
    {
        return [
            'baseId' => $this->baseId,
            'expandedNodes' => $this->expandedNodes,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Visualization';
    }
}
