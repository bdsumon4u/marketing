<x-filament-panels::page>
    <div class="binary-tree"
        x-data="binaryTree()"
        @expanded="$nextTick(() => draw())"
    >
        <div class="tree-container" style="position:relative;">
            <svg class="tree-svg" style="position:absolute; left:0; top:0; pointer-events:none; z-index:0;"></svg>
            <livewire:tree-node :node-id="$baseId" expanded />
        </div>
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
function binaryTree() {
    return {
        connections: [],
        interval: null,
        init() {
            this.$nextTick(() => this.draw());
            // this.interval = setTimeout(() => this.draw(), 200);
            window.addEventListener('resize', () => this.draw());
        },
        draw() {
            const container = document.querySelector('.tree-container');
            const svg = container ? container.querySelector('.tree-svg') : null;
            if (!svg) return;
            svg.setAttribute('width', container.scrollWidth);
            svg.setAttribute('height', container.scrollHeight);
            svg.innerHTML = '';

            // Build connections from DOM
            this.connections = [];
            const cards = Array.from(container.querySelectorAll('.user-card[data-node-id]'));
            cards.forEach(card => {
                const nodeId = card.getAttribute('data-node-id');
                const parentId = card.getAttribute('data-parent-id');
                if (parentId && parentId !== 'null') {
                    this.connections.push([parentId, nodeId]);
                }
            });

            this.connections.forEach(([parentId, childId]) => {
                const parentCard = container.querySelector(`.user-card[data-node-id='${parentId}']`);
                const childCard = container.querySelector(`.user-card[data-node-id='${childId}']`);
                if (!parentCard || !childCard) return;
                const parentRect = parentCard.getBoundingClientRect();
                const childRect = childCard.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const startX = parentRect.left + parentRect.width / 2 - containerRect.left + container.scrollLeft;
                const startY = parentRect.bottom - containerRect.top + container.scrollTop;
                const endX = childRect.left + childRect.width / 2 - containerRect.left + container.scrollLeft;
                const endY = childRect.top - containerRect.top + container.scrollTop;
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
        },
        highlightNode(nodeId, highlight) {
            const nodeCard = document.querySelector(`.user-card[data-node-id='${nodeId}']`);
            if (!nodeCard) return;
            nodeCard.classList.toggle('highlight', highlight);

            this.connections.forEach(([parentId, childId]) => {
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
        }
    }
}
</script>

</x-filament-panels::page>
