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

            // Group connections by parent
            const grouped = {};
            this.connections.forEach(([parentId, childId]) => {
                if (!grouped[parentId]) grouped[parentId] = [];
                grouped[parentId].push(childId);
            });

            Object.entries(grouped).forEach(([parentId, childIds]) => {
                const parentCard = container.querySelector(`.user-card[data-node-id='${parentId}']`);
                if (!parentCard) return;

                const parentRect = parentCard.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const parentX = parentRect.left + parentRect.width / 2 - containerRect.left + container.scrollLeft;
                const parentY = parentRect.bottom - containerRect.top + container.scrollTop;

                if (childIds.length === 2) {
                    // Two children: draw vertical, horizontal, and two verticals
                    const childCards = childIds.map(id => container.querySelector(`.user-card[data-node-id='${id}']`));
                    if (childCards.some(card => !card)) return;

                    const childRects = childCards.map(card => card.getBoundingClientRect());
                    const childXs = childRects.map(rect => rect.left + rect.width / 2 - containerRect.left + container.scrollLeft);
                    const childYs = childRects.map(rect => rect.top - containerRect.top + container.scrollTop);

                    // Junction y: a bit above children
                    const junctionY = Math.min(...childYs) - 24;

                    // 1. Vertical from parent to junction
                    svg.appendChild(line(parentX, parentY, parentX, junctionY, parentId, null));

                    // 2. Horizontal from left child x to right child x at junctionY
                    svg.appendChild(line(childXs[0], junctionY, childXs[1], junctionY, parentId, null));

                    // 3. Verticals from junction to each child
                    svg.appendChild(line(childXs[0], junctionY, childXs[0], childYs[0], parentId, childIds[0]));
                    svg.appendChild(line(childXs[1], junctionY, childXs[1], childYs[1], parentId, childIds[1]));
                } else if (childIds.length === 1) {
                    // Only one child: straight line
                    const childCard = container.querySelector(`.user-card[data-node-id='${childIds[0]}']`);
                    if (!childCard) return;
                    const childRect = childCard.getBoundingClientRect();
                    const childX = childRect.left + childRect.width / 2 - containerRect.left + container.scrollLeft;
                    const childY = childRect.top - containerRect.top + container.scrollTop;
                    svg.appendChild(line(parentX, parentY, childX, childY, parentId, childIds[0]));
                }
            });

            function line(x1, y1, x2, y2, parentId = null, childId = null) {
                const l = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                l.setAttribute('x1', x1);
                l.setAttribute('y1', y1);
                l.setAttribute('x2', x2);
                l.setAttribute('y2', y2);
                l.setAttribute('stroke', '#e5e7eb');
                l.setAttribute('stroke-width', '3');
                l.setAttribute('stroke-linecap', 'round');
                l.classList.add('svg-connection-line');
                if (parentId) l.setAttribute('data-parent-id', parentId);
                if (childId) l.setAttribute('data-child-id', childId);
                return l;
            }
        },
        highlightNode(nodeId, highlight) {
            const nodeCard = document.querySelector(`.user-card[data-node-id='${nodeId}']`);
            if (!nodeCard) return;
            nodeCard.classList.toggle('highlight', highlight);

            // Highlight all lines where this node is either parent or child
            document.querySelectorAll(`.svg-connection-line`).forEach(line => {
                const parentId = line.getAttribute('data-parent-id');
                const childId = line.getAttribute('data-child-id');
                if (parentId == nodeId || childId == nodeId) {
                    line.classList.toggle('svg-line-highlight', highlight);
                }
            });

            // Highlight children
            this.connections.forEach(([parentId, childId]) => {
                if (parentId == nodeId) {
                    const childCard = document.querySelector(`.user-card[data-node-id='${childId}']`);
                    if (childCard) {
                        childCard.classList.toggle('highlight-child', highlight);
                    }
                }
            });
        }
    }
}
</script>

</x-filament-panels::page>
