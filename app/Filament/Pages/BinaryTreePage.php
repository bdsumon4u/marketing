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

    public $visibleLevels = 3;

    public $nodesPerLevel = [];

    public $baseId = 1001;

    public function mount()
    {
        // No maxLevel calculation needed
    }

    public function loadMoreLevels()
    {
        $this->visibleLevels++;
    }

    public function getNodesForLevel($level)
    {
        if (! isset($this->nodesPerLevel[$level])) {
            $nodes = [];
            $startIndex = pow(2, $level - 1);
            $count = pow(2, $level - 1);

            for ($i = 0; $i < $count; $i++) {
                $nodeId = $this->baseId + $startIndex + $i - 1;
                $user = User::find($nodeId);
                if ($user) {
                    if ($nodeId == $this->baseId) {
                        $parentId = null;
                    } else {
                        $parentId = floor(($nodeId - $this->baseId + 1) / 2) + $this->baseId - 1;
                    }
                    $nodes[] = [
                        'id' => $nodeId,
                        'parent_id' => $parentId,
                        'name' => $user->name,
                        'username' => $user->username,
                        'hasChildren' => $this->hasChildren($nodeId),
                    ];
                }
            }
            $this->nodesPerLevel[$level] = $nodes;
        }

        return $this->nodesPerLevel[$level];
    }

    private function hasChildren($nodeId)
    {
        $leftChild = 2 * ($nodeId - $this->baseId + 1) + $this->baseId - 1;

        // No upper limit, just check if a user exists for the left child
        return User::where('id', $leftChild)->exists();
    }

    protected function getViewData(): array
    {
        return [
            'visibleLevels' => $this->visibleLevels,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Visualization';
    }
}
