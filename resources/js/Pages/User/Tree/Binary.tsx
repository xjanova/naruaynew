import { useState, useMemo } from 'react';
import UserLayout from '@/Layouts/UserLayout';
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
    left?: TreeNode | null;
    right?: TreeNode | null;
}

interface Props {
    treeData: TreeNode | null;
    depth: number;
}

function MemberNode({ data }: { data: any }) {
    return (
        <div className={`
            relative px-4 py-3 rounded-xl border backdrop-blur-xl min-w-[150px]
            ${data.active ? 'border-primary-500/30 bg-gradient-to-br from-primary-500/15 to-accent-500/5' : 'border-gray-500/20 bg-gray-500/10'}
            transition-all duration-200
        `}>
            <Handle type="target" position={Position.Top} className="!bg-primary-500 !w-2.5 !h-2.5 !border-2 !border-surface-900" />
            <div className="text-center">
                <div className="w-9 h-9 mx-auto rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-xs font-bold text-white mb-1.5">
                    {data.first_name?.[0]}{data.last_name?.[0]}
                </div>
                <p className="text-sm font-semibold text-white">{data.username}</p>
                <div className="flex justify-center gap-2 mt-1.5 text-xs">
                    <span className="text-primary-400">PV:{data.personal_pv || 0}</span>
                    <span className="text-accent-400">GPV:{data.group_pv || 0}</span>
                </div>
                <span className={`absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full ${data.active ? 'bg-green-400' : 'bg-gray-500'}`} />
            </div>
            <Handle type="source" position={Position.Bottom} className="!bg-primary-500 !w-2.5 !h-2.5 !border-2 !border-surface-900" />
        </div>
    );
}

function EmptyNode({ data }: { data: any }) {
    return (
        <div className="px-3 py-2 rounded-xl border border-dashed border-white/10 bg-white/[0.02] min-w-[100px] text-center">
            <Handle type="target" position={Position.Top} className="!bg-gray-600 !w-2 !h-2" />
            <p className="text-xs text-white/20">{data.label}</p>
        </div>
    );
}

const nodeTypes = { member: MemberNode, empty: EmptyNode };

function buildNodes(node: TreeNode | null, x: number, y: number, depth: number, maxDepth: number): { nodes: Node[]; edges: Edge[] } {
    if (depth > maxDepth || !node) return { nodes: [], edges: [] };
    const nodes: Node[] = [];
    const edges: Edge[] = [];
    const spacing = Math.max(180, 350 / (depth + 1)) * Math.pow(2, maxDepth - depth - 1);
    const vGap = 130;
    const nId = `n-${node.id}`;

    nodes.push({ id: nId, type: 'member', position: { x, y }, data: { ...node } });

    const addChild = (child: TreeNode | null, side: 'left' | 'right', dx: number) => {
        if (child) {
            const r = buildNodes(child, x + dx, y + vGap, depth + 1, maxDepth);
            nodes.push(...r.nodes);
            edges.push(...r.edges);
            edges.push({ id: `e-${node.id}-${child.id}`, source: nId, target: `n-${child.id}`, style: { stroke: side === 'left' ? 'rgba(99,102,241,0.3)' : 'rgba(217,70,239,0.3)', strokeWidth: 2 }, animated: true });
        } else if (depth < maxDepth) {
            const eId = `empty-${side}-${node.id}`;
            nodes.push({ id: eId, type: 'empty', position: { x: x + dx, y: y + vGap }, data: { label: side === 'left' ? 'L' : 'R' } });
            edges.push({ id: `e-${node.id}-${eId}`, source: nId, target: eId, style: { stroke: 'rgba(255,255,255,0.05)', strokeWidth: 1, strokeDasharray: '5 5' } });
        }
    };

    addChild(node.left ?? null, 'left', -spacing);
    addChild(node.right ?? null, 'right', spacing);

    return { nodes, edges };
}

export default function BinaryTree({ treeData, depth }: Props) {
    const { nodes: initNodes, edges: initEdges } = useMemo(() => {
        if (!treeData) return { nodes: [], edges: [] };
        return buildNodes(treeData, 0, 0, 0, depth);
    }, [treeData, depth]);

    const [nodes,, onNodesChange] = useNodesState(initNodes);
    const [edges,, onEdgesChange] = useEdgesState(initEdges);

    return (
        <UserLayout>
            <Head title="Binary Tree" />
            <div className="space-y-4 animate-fade-in">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-white">My Binary Tree</h1>
                        <p className="text-white/40 mt-1">Your placement network</p>
                    </div>
                    <select value={depth} onChange={(e) => router.get('/user/tree/binary', { depth: e.target.value })} className="glass-input text-sm">
                        {[3, 4, 5, 6].map(d => <option key={d} value={d} className="bg-surface-900">{d} levels</option>)}
                    </select>
                </div>
                <GlassCard padding="none" hover={false} className="!rounded-2xl overflow-hidden">
                    <div style={{ height: '65vh' }}>
                        {nodes.length > 0 ? (
                            <ReactFlow nodes={nodes} edges={edges} onNodesChange={onNodesChange} onEdgesChange={onEdgesChange} nodeTypes={nodeTypes} fitView fitViewOptions={{ padding: 0.3 }} proOptions={{ hideAttribution: true }} minZoom={0.1} maxZoom={2}>
                                <Background variant={BackgroundVariant.Dots} color="rgba(255,255,255,0.03)" gap={20} />
                                <Controls className="!bg-white/5 !border-white/10 !rounded-xl [&>button]:!bg-transparent [&>button]:!border-white/10 [&>button]:!text-white/40 [&>button:hover]:!bg-white/10" />
                            </ReactFlow>
                        ) : (
                            <div className="h-full flex items-center justify-center text-white/30">No tree data available</div>
                        )}
                    </div>
                </GlassCard>
            </div>
        </UserLayout>
    );
}
