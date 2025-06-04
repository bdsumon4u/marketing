<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BinaryTreeController extends Controller
{
    public function index()
    {
        return view('binary-tree');
    }

    public function getNodes(Request $request)
    {
        $level = $request->input('level', 1);
        $baseId = 1001;
        $nodes = [];

        $startIndex = pow(2, $level - 1);
        $count = pow(2, $level - 1);

        for ($i = 0; $i < $count; $i++) {
            $nodeId = $baseId + $startIndex + $i - 1;
            if ($nodeId <= 11000) {
                $nodes[] = [
                    'id' => $nodeId,
                    'hasChildren' => $this->hasChildren($nodeId),
                ];
            }
        }

        return response()->json($nodes);
    }

    private function hasChildren($nodeId)
    {
        $baseId = 1001;
        $leftChild = 2 * ($nodeId - $baseId + 1) + $baseId - 1;

        return $leftChild <= 11000;
    }
}
