<x-filament-panels::page>
    <div class="binary-tree"
        x-data="{
            hovered: null,
            visibleLevels: $wire.entangle('visibleLevels'),
            setHovered(id) { this.hovered = id },
            clearHovered() { this.hovered = null },
        }"
        x-init="
            $nextTick(() => setTimeout(drawTreeLines, 200));
            $watch('visibleLevels', () => setTimeout(drawTreeLines, 200));
        "
    >
        <div class="tree-container">
            @php
                $allNodes = collect();
                for ($level = 1; $level <= $visibleLevels; $level++) {
                    $allNodes = $allNodes->merge($this->getNodesForLevel($level));
                }
            @endphp
            <svg class="tree-svg" width="100%" height="100%" style="position:absolute; left:0; top:0; pointer-events:none; z-index:0;"></svg>
            @for ($level = 1; $level <= $visibleLevels; $level++)
                <div class="tree-level">
                    @foreach ($this->getNodesForLevel($level) as $node)
                        <div class="tree-node"
                            x-data="{ id: {{ $node['id'] }}, parentId: {{ $node['parent_id'] ?? 'null' }} }"
                            @mouseenter="setHovered(id); highlightLines(id, parentId, true);"
                            @mouseleave="clearHovered(); highlightLines(id, parentId, false);"
                        >
                            <div class="user-card"
                                :class="{
                                    'highlight': hovered === {{ $node['id'] }} || hovered === {{ $node['parent_id'] ?? 'null' }},
                                    'highlight-child': hovered && hovered === {{ $node['parent_id'] ?? 'null' }},
                                    'highlight-parent': hovered && hovered === {{ $node['id'] }}
                                }"
                                data-node-id="{{ $node['id'] }}"
                                data-parent-id="{{ $node['parent_id'] ?? '' }}"
                            >
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
        padding: 20px 40px;
        overflow-x: auto;
        position: relative;
        min-height: 600px;
        box-sizing: border-box;
        min-width: 0;
    }
    .tree-svg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        pointer-events: none;
    }
    .svg-line-highlight {
        stroke: #2563eb !important;
        stroke-width: 2 !important;
    }
    .tree-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        min-width: max-content;
    }
    .tree-level {
        display: flex;
        justify-content: center;
        margin: 40px 0 0 0;
        position: relative;
    }
    .tree-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 0 20px;
        position: relative;
        z-index: 2;
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
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .user-card.highlight, .user-card.highlight-parent {
        border-color: #2563eb;
        box-shadow: 0 4px 16px rgba(37,99,235,0.15);
    }
    .user-card.highlight-child {
        border-color: #38bdf8;
        box-shadow: 0 4px 16px rgba(56,189,248,0.15);
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
    .load-more {
        text-align: center;
        margin-top: 20px;
    }
    </style>
    <script>
    function drawTreeLines() {
        const container = document.querySelector('.tree-container');
        const svg = document.querySelector('.tree-svg');
        if (!container || !svg) return;
        svg.innerHTML = '';
        // Get all user cards and their positions
        const nodeMap = {};
        container.querySelectorAll('.user-card').forEach(card => {
            const id = card.getAttribute('data-node-id');
            const parentId = card.getAttribute('data-parent-id');
            const rect = card.getBoundingClientRect();
            nodeMap[id] = {
                card,
                parentId,
                rect
            };
        });
        // Draw lines from parent to child
        Object.values(nodeMap).forEach(node => {
            if (node.parentId && nodeMap[node.parentId]) {
                const parentRect = nodeMap[node.parentId].card.getBoundingClientRect();
                const childRect = node.card.getBoundingClientRect();
                // Calculate start and end points relative to SVG
                const svgRect = svg.getBoundingClientRect();
                const startX = parentRect.left + parentRect.width / 2 - svgRect.left;
                const startY = parentRect.bottom - svgRect.top;
                const endX = childRect.left + childRect.width / 2 - svgRect.left;
                const endY = childRect.top - svgRect.top;
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', startX);
                line.setAttribute('y1', startY);
                line.setAttribute('x2', endX);
                line.setAttribute('y2', endY);
                line.setAttribute('stroke', '#e5e7eb');
                line.setAttribute('stroke-width', '2');
                line.setAttribute('data-parent-id', node.parentId);
                line.setAttribute('data-child-id', node.card.getAttribute('data-node-id'));
                line.classList.add('svg-connection-line');
                svg.appendChild(line);
            }
        });
    }
    function highlightLines(id, parentId, highlight) {
        // Highlight lines where this node is parent or child
        const svg = document.querySelector('.tree-svg');
        if (!svg) return;
        // Highlight lines to children
        svg.querySelectorAll(`.svg-connection-line[data-parent-id='${id}']`).forEach(line => {
            if (highlight) {
                line.classList.add('svg-line-highlight');
            } else {
                line.classList.remove('svg-line-highlight');
            }
        });
        // Highlight line to parent
        if (parentId && parentId !== 'null') {
            svg.querySelectorAll(`.svg-connection-line[data-child-id='${id}']`).forEach(line => {
                if (highlight) {
                    line.classList.add('svg-line-highlight');
                } else {
                    line.classList.remove('svg-line-highlight');
                }
            });
        }
    }
    </script>
</x-filament-panels::page>
