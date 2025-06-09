<x-filament-panels::page>
    <div class="referral-tree"
        x-data="referralTree()"
        @expanded="$nextTick(() => draw())"
    >
        <div class="tree-container" style="position:relative;">
            <svg class="tree-svg" style="position:absolute; left:0; top:0; pointer-events:none; z-index:0;"></svg>
            <livewire:referral-tree-node :node-id="$baseId" expanded />
        </div>
    </div>

    <style>
    .referral-tree {
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
        flex-wrap: wrap;
        gap: 20px;
    }
    .tree-node {
        display: flex;
        flex-direction: column;
        align-items: center;
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
function referralTree() {
    return {
        connections: [],
        interval: null,
        init() {
            this.$nextTick(() => this.draw());
            window.addEventListener('resize', () => this.draw());
            // Add a small delay to ensure DOM is ready
            setTimeout(() => this.draw(), 100);
        },
        draw() {
            const container = document.querySelector('.tree-container');
            const svg = container ? container.querySelector('.tree-svg') : null;
            if (!svg) return;

            // Clear previous connections
            svg.innerHTML = '';
            this.connections = [];

            // Set SVG dimensions
            svg.setAttribute('width', container.scrollWidth);
            svg.setAttribute('height', container.scrollHeight);

            // Build connections from DOM
            const cards = Array.from(container.querySelectorAll('.user-card[data-node-id]'));
            cards.forEach(card => {
                const nodeId = card.getAttribute('data-node-id');
                const parentId = card.getAttribute('data-parent-id');
                if (parentId && parentId !== 'null') {
                    this.connections.push([parentId, nodeId]);
                }
            });

            // Group connections by parent (referrer)
            const grouped = {};
            this.connections.forEach(([parentId, childId]) => {
                if (!grouped[parentId]) grouped[parentId] = [];
                grouped[parentId].push(childId);
            });

            // Draw lines for each referrer-referral group
            Object.entries(grouped).forEach(([referrerId, referralIds]) => {
                const referrerCard = container.querySelector(`.user-card[data-node-id='${referrerId}']`);
                if (!referrerCard) return;

                const referrerRect = referrerCard.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const referrerX = referrerRect.left + referrerRect.width / 2 - containerRect.left + container.scrollLeft;
                const referrerY = referrerRect.bottom - containerRect.top + container.scrollTop;

                // Get all referral cards
                const referralCards = referralIds.map(id => container.querySelector(`.user-card[data-node-id='${id}']`));
                if (referralCards.some(card => !card)) return;

                const referralRects = referralCards.map(card => card.getBoundingClientRect());
                const referralXs = referralRects.map(rect => rect.left + rect.width / 2 - containerRect.left + container.scrollLeft);
                const referralYs = referralRects.map(rect => rect.top - containerRect.top + container.scrollTop);

                // Junction y: a bit above referrals
                const junctionY = Math.min(...referralYs) - 24;

                // Draw vertical line from referrer to junction
                svg.appendChild(this.createLine(referrerX, referrerY, referrerX, junctionY, referrerId, null));

                // Draw horizontal line connecting all referrals
                const minX = Math.min(...referralXs);
                const maxX = Math.max(...referralXs);
                svg.appendChild(this.createLine(minX, junctionY, maxX, junctionY, referrerId, null));

                // Draw vertical lines from junction to each referral
                referralIds.forEach((referralId, index) => {
                    svg.appendChild(this.createLine(referralXs[index], junctionY, referralXs[index], referralYs[index], referrerId, referralId));
                });
            });
        },
        createLine(x1, y1, x2, y2, referrerId = null, referralId = null) {
            const l = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            l.setAttribute('x1', x1);
            l.setAttribute('y1', y1);
            l.setAttribute('x2', x2);
            l.setAttribute('y2', y2);
            l.setAttribute('stroke', '#e5e7eb');
            l.setAttribute('stroke-width', '3');
            l.setAttribute('stroke-linecap', 'round');
            l.classList.add('svg-connection-line');
            if (referrerId) l.setAttribute('data-parent-id', referrerId);
            if (referralId) l.setAttribute('data-child-id', referralId);
            return l;
        },
        highlightNode(nodeId, highlight) {
            const nodeCard = document.querySelector(`.user-card[data-node-id='${nodeId}']`);
            if (!nodeCard) return;
            nodeCard.classList.toggle('highlight', highlight);

            // Highlight all lines where this node is either referrer or referral
            document.querySelectorAll(`.svg-connection-line`).forEach(line => {
                const referrerId = line.getAttribute('data-parent-id');
                const referralId = line.getAttribute('data-child-id');
                if (referrerId == nodeId || referralId == nodeId) {
                    line.classList.toggle('svg-line-highlight', highlight);
                }
            });

            // Highlight referrals
            this.connections.forEach(([referrerId, referralId]) => {
                if (referrerId == nodeId) {
                    const referralCard = document.querySelector(`.user-card[data-node-id='${referralId}']`);
                    if (referralCard) {
                        referralCard.classList.toggle('highlight-child', highlight);
                    }
                }
            });
        }
    }
}
</script>

</x-filament-panels::page>
