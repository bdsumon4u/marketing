<x-filament-panels::page>
    <div class="binary-tree">
        <div class="tree-container">
            @for ($level = 1; $level <= $visibleLevels; $level++)
                <div class="tree-level">
                    @foreach ($this->getNodesForLevel($level) as $node)
                        <div class="tree-node">
                            <div class="user-card">
                                <div class="avatar">
                                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="8" r="4" />
                                        <ellipse cx="12" cy="17" rx="7" ry="5" />
                                    </svg>
                                </div>
                                <div class="user-info">
                                    <div class="user-name">{{ $node['name'] }}</div>
                                    <div class="user-username">{{ '@' . $node['username'] }}</div>
                                </div>
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
                <x-filament::button
                    wire:click="loadMoreLevels"
                    color="primary"
                >
                    Load More Levels
                </x-filament::button>
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

    .user-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        padding: 20px 16px 16px 16px;
        min-width: 180px;
        display: flex;
        flex-direction: column;
        align-items: center;
        border: 1px solid #e5e7eb;
        margin-bottom: 8px;
    }

    .avatar {
        background: #f3f4f6;
        border-radius: 50%;
        padding: 8px;
        margin-bottom: 10px;
        color: #4b5563;
    }

    .user-info {
        text-align: center;
        margin-bottom: 10px;
    }

    .user-name {
        font-weight: 600;
        font-size: 1rem;
        color: #111827;
    }

    .user-username {
        font-size: 0.95rem;
        color: #2563eb;
        font-weight: 500;
        margin-bottom: 4px;
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
        background-color: #e5e7eb;
    }

    .load-more {
        text-align: center;
        margin-top: 20px;
    }
    </style>
</x-filament-panels::page>
