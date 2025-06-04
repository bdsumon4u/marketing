<div class="binary-tree">
    <div class="tree-container">
        @for ($level = 1; $level <= $visibleLevels; $level++)
            <div class="tree-level">
                @foreach ($this->getNodesForLevel($level) as $node)
                    <div class="tree-node">
                        <div class="node-content {{ $node['hasChildren'] ? 'has-children' : '' }}">
                            {{ $node['id'] }}
                        </div>
                        @if ($node['hasChildren'])
                            <div class="node-children">
                                <div class="child-lines"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endfor
    </div>
    @if ($visibleLevels < $maxLevel)
        <div class="load-more">
            <button wire:click="loadMoreLevels" class="btn btn-primary">
                Load More Levels
            </button>
        </div>
    @endif
</div>

<style>
.binary-tree {
    padding: 20px;
    overflow-x: auto;
}

.tree-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.tree-level {
    display: flex;
    justify-content: center;
    margin: 20px 0;
    position: relative;
}

.tree-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 10px;
}

.node-content {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: rgb(74, 85, 104);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    position: relative;
    z-index: 2;
}

.node-content.has-children {
    background-color: rgb(45, 55, 72);
}

.node-children {
    position: relative;
    height: 40px;
}

.child-lines {
    position: absolute;
    top: 0;
    left: 50%;
    width: 2px;
    height: 100%;
    background-color: rgb(74, 85, 104);
}

.load-more {
    text-align: center;
    margin-top: 20px;
}

.btn-primary {
    background-color: rgb(66, 153, 225);
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-primary:hover {
    background-color: rgb(49, 130, 206);
}
</style>
