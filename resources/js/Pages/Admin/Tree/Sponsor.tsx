import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, router } from '@inertiajs/react';

interface TreeNode {
    id: number;
    username: string;
    first_name: string;
    last_name: string;
    active: boolean;
    personal_pv: number;
    children?: TreeNode[];
}

interface Props {
    treeData: TreeNode | null;
    rootUser: { id: number; username: string; first_name: string; last_name: string } | null;
    depth: number;
}

function SponsorTreeNode({ node, level = 0 }: { node: TreeNode; level?: number }) {
    const [expanded, setExpanded] = useState(level < 2);
    const hasChildren = node.children && node.children.length > 0;

    return (
        <div className="ml-6 relative">
            {/* Connector line */}
            {level > 0 && (
                <div className="absolute -left-6 top-0 h-7 w-6 border-l-2 border-b-2 border-white/10 rounded-bl-xl" />
            )}

            {/* Node */}
            <div className={`
                inline-flex items-center gap-3 px-4 py-2.5 rounded-xl border backdrop-blur-sm mb-2
                ${node.active ? 'border-primary-500/20 bg-primary-500/5 hover:border-primary-500/40' : 'border-gray-500/20 bg-gray-500/5'}
                transition-all duration-200 cursor-pointer
            `}
            onClick={() => hasChildren && setExpanded(!expanded)}
            >
                {hasChildren && (
                    <span className={`text-xs text-white/30 transition-transform ${expanded ? 'rotate-90' : ''}`}>▶</span>
                )}
                <div className="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-xs font-bold text-white">
                    {node.first_name?.[0]}{node.last_name?.[0]}
                </div>
                <div>
                    <p className="text-sm font-medium text-white">{node.username}</p>
                    <p className="text-xs text-white/30">{node.first_name} {node.last_name} · PV: {node.personal_pv}</p>
                </div>
                <span className={`w-2 h-2 rounded-full ml-2 ${node.active ? 'bg-green-400' : 'bg-gray-500'}`} />
            </div>

            {/* Children */}
            {hasChildren && expanded && (
                <div className="relative">
                    {node.children!.map((child, i) => (
                        <SponsorTreeNode key={child.id} node={child} level={level + 1} />
                    ))}
                </div>
            )}
        </div>
    );
}

export default function SponsorTree({ treeData, rootUser, depth }: Props) {
    return (
        <AdminLayout>
            <Head title="Sponsor Tree" />

            <div className="space-y-4 animate-fade-in">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Sponsor Tree</h1>
                        <p className="text-white/40 mt-1">
                            Referral network from: <span className="text-primary-400">{rootUser?.username || 'Root'}</span>
                        </p>
                    </div>
                    <select
                        value={depth}
                        onChange={(e) => router.get('/admin/tree/sponsor', { user_id: rootUser?.id, depth: e.target.value })}
                        className="glass-input text-sm"
                    >
                        {[3, 4, 5, 6, 7, 10].map((d) => (
                            <option key={d} value={d} className="bg-surface-900">{d} levels</option>
                        ))}
                    </select>
                </div>

                <GlassCard hover={false} className="overflow-x-auto">
                    {treeData ? (
                        <SponsorTreeNode node={treeData} />
                    ) : (
                        <div className="text-center py-16 text-white/30">No tree data available</div>
                    )}
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
