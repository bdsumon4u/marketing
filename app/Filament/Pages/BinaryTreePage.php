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

    public $connections = [];

    public function mount()
    {
        $this->expandedNodes = [$this->baseId]; // Start with root node expanded
        $this->buildConnections();
    }

    public function expandNode($nodeId)
    {
        if (! in_array($nodeId, $this->expandedNodes)) {
            $this->expandedNodes[] = $nodeId;
            $this->buildConnections();
        }
    }

    protected function buildConnections()
    {
        $this->connections = collect($this->expandedNodes)
            ->map(function ($nodeId) {
                $node = $this->getNode($nodeId);
                if (! $node) {
                    return null;
                }

                return collect($node['children'])
                    ->filter()
                    ->map(fn ($childId) => [$nodeId, $childId])
                    ->values();
            })
            ->filter()
            ->flatten(1)
            ->values()
            ->toArray();
    }

    public function getNode($nodeId)
    {
        if (! $user = User::find($nodeId)) {
            return null;
        }

        $leftChild = 2 * $nodeId - $this->baseId + 1;
        $rightChild = $leftChild + 1;

        return [
            'id' => $nodeId,
            'name' => $user->name,
            'username' => $user->username,
            'children' => [
                $leftChild,
                $rightChild,
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
            'connections' => $this->connections,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Visualization';
    }
}
