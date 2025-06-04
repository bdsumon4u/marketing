<?php

namespace App\Livewire;

use Livewire\Component;

class BinaryTree extends Component
{
    public $visibleLevels = 3;

    public $nodesPerLevel = [];

    public $baseId = 1001;

    public $maxLevel = 0;

    public function mount()
    {
        $this->calculateMaxLevel();
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
                if ($nodeId <= 11000) {
                    $nodes[] = [
                        'id' => $nodeId,
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

        return $leftChild <= 11000;
    }

    private function calculateMaxLevel()
    {
        $level = 1;
        $nodes = 1;
        while ($nodes < 10000) {
            $level++;
            $nodes += pow(2, $level - 1);
        }
        $this->maxLevel = $level;
    }

    public function render()
    {
        return view('livewire.binary-tree');
    }
}
