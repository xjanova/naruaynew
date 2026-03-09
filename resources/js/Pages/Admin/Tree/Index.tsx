import { useState, useCallback, useMemo } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, router } from '@inertiajs/react';
import {
    ReactFlow,
    Controls,
    Background,
    useNodesState,
    useEdgesState,
    Node,
    Edge,
    Handle,
    Position,
    BackgroundVariant,
    MiniMap,
} from '@xyflow/react';
import '@xyflow/react/dist/style.css';

interface TreeNode {
    id: number;
    username: string;
    first_name: string;
    last_name: string;
    active: boolean;
    personal_pv: number;
    group_pv: number;
    rank_name?: string;
    position?: string;
    left?: TreeNode | null;
    right?: TreeNode | null;
}

interface Props {
    treeData: TreeNode | null;
    rootUser: { id: number; username: string; first_name: string; last_name: string } | null;
    depth: number;
}

// Custom Node Component
function MemberNode({ data }: { data: any }) {
    const bgGradient = data.active
        ? 'from-primary-500/20 to-accent-500/10'
        : 'from-gray-500/20 to-gray-600/10';

    return (
        <div className={`
            relative px-4 py-3 rounded-xl border backdrop-blur-xl min-w-[160px]
            ${data.active ? 'border-primary-500/30 bg-gradient-to-br' : 'border-gray-500/20 bg-gradient-to-br'}
            ${bgGradient}
            hover:border-primary-400/50 transition-all duration-200 cursor-pointer
        `}>
            <Handle type="target" position={Position.Top} className="!bg-primary-500 !w-3 !h-3 !border-2 !border-surface-900" />

            <div className="text-center">
                <div className="w-10 h-10 mx-auto rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-sm font-bold text-white mb-2">
                    {data.first_name?.[0]}{data.last_name?.[0]}
                </div>
                <p className="text-sm font-semibold text-white truncate">{data.username}</p>
                <p className="text-xs text-white/40">{data.first_name} {data.last_name}</p>

                <div className="flex items-center justify-center gap-3 mt-2">
                    <div className="text-center">
                        <p className="text-xs text-white/30">PV</p>
                        <p className="text-xs font-bold text-primary-400">{data.personal_pv || 0}</p>
                    </div>
                    <div className="w-px h-6 bg-white/10" />
                    <div className="text-center">
                        <p className="text-xs text-white/30">GPV</p>
                        <p className="text-xs font-bold text-accent-400">{data.group_pv || 0}</p>
                    </div>
                </div>

                {data.rank_name && (
                    <div className="mt-2 inline-block px-2 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20">
                        <span className="text-[10px] text-amber-400 font-medium">{data.rank_name}</span>
                    </div>
                )}

                <span className={`absolute top-2 right-2 w-2 h-2 rounded-full ${data.active ? 'bg-green-400' : 'bg-gray-500'}`} />
            </div>

            <Handle type="source" position={Position.Bottom} className="!bg-primary-500 !w-3 !h-3 !border-2 !border-surface-900" />
        </div>
    );
}

// Empty Position Node
function EmptyNode({ data }: { data: any }) {
    return (
        <div className="px-4 py-3 rounded-xl border border-dashed border-white/10 bg-white/[0.02] min-w-[120px] text-center">
            <Handle type="target" position={Position.Top} className="!bg-gray-600 !w-2 !h-2" />
            <div className="w-8 h-8 mx-auto rounded-full border-2 border-dashed border-white/10 flex items-center justify-center mb-1">
                <span className="text-white/20 text-lg">+</span>
            </div>
            <p className="text-xs text-white/20">{data.label}</p>
        </div>
    );
}

const nodeTypes = {
    member: MemberNode,
    empty: EmptyNode,
};

function buildFlowNodes(node: TreeNode | null, x: number, y: number, depth: number, maxDepth: number): { nodes: Node[]; edges: Edge[] } {
    if (depth > maxDepth || !node) return { nodes: [], edges: [] };

    const nodes: Node[] = [];
    const edges: Edge[] = [];
    const spacing = Math.max(200, 400 / (depth + 1)) * Math.pow(2, maxDepth - depth - 1);
    const verticalGap = 140;

    const nodeId = `node-${node.id}`;
    nodes.push({
        id: nodeId,
        type: 'member',
        position: { x, y },
        data: { ...node },
    });

    // Left child
    if (node.left) {
        const leftResult = buildFlowNodes(node.left, x - spacing, y + verticalGap, depth + 1, maxDepth);
        nodes.push(...leftResult.nodes);
        edges.push(...leftResult.edges);
        edges.push({
            id: `edge-${node.id}-${node.left.id}`,
            source: nodeId,
            target: `node-${node.left.id}`,
            style: { stroke: 'rgba(99, 102, 241, 0.3)', strokeWidth: 2 },
            animated: true,
        });
    } else if (depth < maxDepth) {
        const emptyId = `empty-left-${node.id}`;
        nodes.push({
            id: emptyId,
            type: 'empty',
            position: { x: x - spacing, y: y + verticalGap },
            data: { label: 'Left' },
        });
        edges.push({
            id: `edge-${node.id}-empty-left`,
            source: nodeId,
            target: emptyId,
            style: { stroke: 'rgba(255,255,255,0.05)', strokeWidth: 1, strokeDasharray: '5 5' },
        });
    }

    // Right child
    if (node.right) {
        const rightResult = buildFlowNodes(node.right, x + spacing, y + verticalGap, depth + 1, maxDepth);
        nodes.push(...rightResult.nodes);
        edges.push(...rightResult.edges);
        edges.push({
            id: `edge-${node.id}-${node.right.id}`,
            source: nodeId,
            target: `node-${node.right.id}`,
            style: { stroke: 'rgba(217, 70, 239, 0.3)', strokeWidth: 2 },
            animated: true,
        });
    } else if (depth < maxDepth) {
        const emptyId = `empty-right-${node.id}`;
        nodes.push({
            id: emptyId,
            type: 'empty',
            position: { x: x + spacing, y: y + verticalGap },
            data: { label: 'Right' },
        });
        edges.push({
            id: `edge-${node.id}-empty-right`,
            source: nodeId,
            target: emptyId,
            style: { stroke: 'rgba(255,255,255,0.05)', strokeWidth: 1, strokeDasharray: '5 5' },
        });
    }

    return { nodes, edges };
}

export default function TreeIndex({ treeData, rootUser, depth }: Props) {
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<any[]>([]);

    const { nodes: initialNodes, edges: initialEdges } = useMemo(() => {
        if (!treeData) return { nodes: [], edges: [] };
        return buildFlowNodes(treeData, 0, 0, 0, depth);
    }, [treeData, depth]);

    const [nodes, setNodes, onNodesChange] = useNodesState(initialNodes);
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges);

    const handleSearch = async () => {
        if (!searchQuery.trim()) return;
        try {
            const res = await fetch(`/admin/tree/search?q=${encodeURIComponent(searchQuery)}`);
            const data = await res.json();
            setSearchResults(data);
        } catch (e) {
            console.error(e);
        }
    };

    const navigateToUser = (userId: number) => {
        router.get('/admin/tree', { user_id: userId, depth });
        setSearchResults([]);
        setSearchQuery('');
    };

    return (
        <AdminLayout>
            <Head title="Binary Tree" />

            <div className="space-y-4 animate-fade-in">
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Binary Tree</h1>
                        <p className="text-white/40 mt-1">
                            Viewing from: <span className="text-primary-400">{rootUser?.username || 'Root'}</span>
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        {/* Search */}
                        <div className="relative">
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                placeholder="Search member..."
                                className="glass-input text-sm w-48"
                            />
                            {searchResults.length > 0 && (
                                <div className="absolute top-full mt-1 left-0 right-0 glass-card p-2 z-50 max-h-48 overflow-y-auto">
                                    {searchResults.map((u: any) => (
                                        <button
                                            key={u.id}
                                            onClick={() => navigateToUser(u.id)}
                                            className="w-full text-left px-3 py-2 rounded-lg hover:bg-white/5 text-sm text-white/70 hover:text-white transition"
                                        >
                                            {u.username} - {u.first_name} {u.last_name}
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Depth selector */}
                        <select
                            value={depth}
                            onChange={(e) => router.get('/admin/tree', { user_id: rootUser?.id, depth: e.target.value })}
                            className="glass-input text-sm"
                        >
                            {[3, 4, 5, 6, 7].map((d) => (
                                <option key={d} value={d} className="bg-surface-900">{d} levels</option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Tree Visualization */}
                <GlassCard padding="none" className="!rounded-2xl overflow-hidden" hover={false}>
                    <div style={{ height: '70vh' }}>
                        {nodes.length > 0 ? (
                            <ReactFlow
                                nodes={nodes}
                                edges={edges}
                                onNodesChange={onNodesChange}
                                onEdgesChange={onEdgesChange}
                                nodeTypes={nodeTypes}
                                fitView
                                fitViewOptions={{ padding: 0.3 }}
                                proOptions={{ hideAttribution: true }}
                                minZoom={0.1}
                                maxZoom={2}
                            >
                                <Background variant={BackgroundVariant.Dots} color="rgba(255,255,255,0.03)" gap={20} />
                                <Controls
                                    className="!bg-white/5 !border-white/10 !rounded-xl [&>button]:!bg-transparent [&>button]:!border-white/10 [&>button]:!text-white/40 [&>button:hover]:!bg-white/10"
                                />
                                <MiniMap
                                    nodeColor={(n) => n.type === 'member' ? '#6366f1' : '#333'}
                                    maskColor="rgba(0,0,0,0.8)"
                                    className="!bg-surface-900/80 !border-white/10 !rounded-xl"
                                />
                            </ReactFlow>
                        ) : (
                            <div className="h-full flex items-center justify-center text-white/30">
                                No tree data available
                            </div>
                        )}
                    </div>
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
