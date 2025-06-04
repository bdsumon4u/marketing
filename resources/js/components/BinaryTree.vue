<template>
    <div class="binary-tree">
        <div class="tree-container">
            <div v-for="level in visibleLevels" :key="level" class="tree-level">
                <div
                    v-for="node in getNodesForLevel(level)"
                    :key="node.id"
                    class="tree-node"
                >
                    <div
                        class="node-content"
                        :class="{ 'has-children': hasChildren(node.id) }"
                    >
                        {{ node.id }}
                    </div>
                    <div v-if="hasChildren(node.id)" class="node-children">
                        <div class="child-lines"></div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="hasMoreLevels" class="load-more">
            <button @click="loadMoreLevels" class="btn btn-primary">
                Load More Levels
            </button>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            visibleLevels: 3,
            nodesPerLevel: {},
            baseId: 1001,
            maxLevel: 0,
        };
    },
    computed: {
        hasMoreLevels() {
            return this.visibleLevels < this.maxLevel;
        },
    },
    methods: {
        getNodesForLevel(level) {
            if (!this.nodesPerLevel[level]) {
                const nodes = [];
                const startIndex = Math.pow(2, level - 1);
                const count = Math.pow(2, level - 1);

                for (let i = 0; i < count; i++) {
                    const nodeId = this.baseId + startIndex + i - 1;
                    if (nodeId <= 11000) {
                        // Assuming max 10,000 users
                        nodes.push({ id: nodeId });
                    }
                }
                this.nodesPerLevel[level] = nodes;
            }
            return this.nodesPerLevel[level];
        },
        hasChildren(nodeId) {
            const leftChild = 2 * (nodeId - this.baseId + 1) + this.baseId - 1;
            return leftChild <= 11000; // Check if left child exists
        },
        loadMoreLevels() {
            this.visibleLevels++;
        },
        calculateMaxLevel() {
            let level = 1;
            let nodes = 1;
            while (nodes < 10000) {
                level++;
                nodes += Math.pow(2, level - 1);
            }
            this.maxLevel = level;
        },
    },
    mounted() {
        this.calculateMaxLevel();
    },
};
</script>

<style scoped>
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
    background-color: #4a5568;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    position: relative;
    z-index: 2;
}

.node-content.has-children {
    background-color: #2d3748;
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
    background-color: #4a5568;
}

.load-more {
    text-align: center;
    margin-top: 20px;
}

.btn-primary {
    background-color: #4299e1;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-primary:hover {
    background-color: #3182ce;
}
</style>
