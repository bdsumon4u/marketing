<x-filament-panels::page>
    @php
        // Helper to collect all visible parent-child pairs
        function collectVisibleConnections($nodeId, $expandedNodes, &$connections = []) {
            $node = app('livewire')->current()->getNode($nodeId);
            if (!$node) return;
            if (in_array($nodeId, $expandedNodes)) {
                foreach ($node['children'] as $childId) {
                    if ($childId) {
                        $connections[] = [$nodeId, $childId];
                        collectVisibleConnections($childId, $expandedNodes, $connections);
                    }
                }
            }
            return $connections;
        }
        $connections = collectVisibleConnections($baseId, $expandedNodes);
    @endphp
    <div class="binary-tree" x-data="{hovered: null}" x-init="
        $nextTick(() => setTimeout(drawTreeLines, 200));
        window.addEventListener('livewire:update', () => setTimeout(drawTreeLines, 200));
    ">
        <svg class="tree-svg" width="100%" height="100%" style="position:absolute; left:0; top:0; pointer-events:none; z-index:0;"></svg>
        <div class="tree-container">
            @include('filament.pages.partials.binary-tree-node', ['nodeId' => $baseId, 'expandedNodes' => $expandedNodes])
        </div>
        <script>
        window.visibleConnections = @json($connections);

        function drawTreeLines() {
            const svg = document.querySelector('.tree-svg');
            if (!svg || !window.visibleConnections) return;
            svg.innerHTML = '';
            window.visibleConnections.forEach(([parentId, childId]) => {
                const parentCard = document.querySelector(`.user-card[data-node-id='${parentId}']`);
                const childCard = document.querySelector(`.user-card[data-node-id='${childId}']`);
                if (!parentCard || !childCard) return;
                const parentRect = parentCard.getBoundingClientRect();
                const childRect = childCard.getBoundingClientRect();
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
                line.setAttribute('data-parent-id', parentId);
                line.setAttribute('data-child-id', childId);
                line.classList.add('svg-connection-line');
                svg.appendChild(line);
            });
        }

        // Highlight function for node and its children and lines
        window.highlightTreeNode = function(nodeId, highlight) {
            // Highlight the node itself
            const nodeCard = document.querySelector(`.user-card[data-node-id='${nodeId}']`);
            if (nodeCard) {
                nodeCard.classList.toggle('highlight', highlight);
            }
            // Highlight direct children and lines
            window.visibleConnections.forEach(([parentId, childId]) => {
                if (parentId == nodeId) {
                    const childCard = document.querySelector(`.user-card[data-node-id='${childId}']`);
                    if (childCard) {
                        childCard.classList.toggle('highlight-child', highlight);
                    }
                    const line = document.querySelector(`.svg-connection-line[data-parent-id='${parentId}'][data-child-id='${childId}']`);
                    if (line) {
                        line.classList.toggle('svg-line-highlight', highlight);
                    }
                }
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(drawTreeLines, 200);
        });
        window.addEventListener('resize', () => setTimeout(drawTreeLines, 200));
        window.addEventListener('livewire:update', () => setTimeout(drawTreeLines, 200));
        </script>
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
</x-filament-panels::page>
