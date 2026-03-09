import { useState } from 'react';
import UserLayout from '@/Layouts/UserLayout';
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
    depth: number;
}

function SponsorNode({ node, level = 0 }: { node: TreeNode; level?: number }) {
    const [expanded, setExpanded] = useState(level < 2);
    const hasChildren = node.children && node.children.length > 0;

    return (
        <div className="ml-5 relative">
            {level > 0 && (
                <div className="absolute -left-5 top-0 h-6 w-5 border-l-2 border-b-2 border-white/10 rounded-bl-lg" />
            )}
            <div
                className={`inline-flex items-center gap-2.5 px-3 py-2 rounded-lg border mb-1.5 transition-all duration-200 cursor-pointer
                    ${node.active ? 'border-primary-500/20 bg-primary-500/5 hover:border-primary-500/40' : 'border-gray-500/20 bg-gray-500/5'}`}
                onClick={() => hasChildren && setExpanded(!expanded)}
            >
                {hasChildren && (
                    <span className={`text-[10px] text-white/30 transition-transform ${expanded ? 'rotate-90' : ''}`}>▶</span>
                )}
                <div className="w-7 h-7 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-[10px] font-bold text-white">
                    {node.first_name?.[0]}{node.last_name?.[0]}
                </div>
                <div>
                    <p className="text-xs font-medium text-white">{node.username}</p>
                    <p className="text-[10px] text-white/30">PV: {node.personal_pv}</p>
                </div>
                <span className={`w-1.5 h-1.5 rounded-full ${node.active ? 'bg-green-400' : 'bg-gray-500'}`} />
            </div>
            {hasChildren && expanded && (
                <div>{node.children!.map(c => <SponsorNode key={c.id} node={c} level={level + 1} />)}</div>
            )}
        </div>
    );
}

export default function SponsorTree({ treeData, depth }: Props) {
    return (
        <UserLayout>
            <Head title="Sponsor Tree" />
            <div className="space-y-4 animate-fade-in">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-white">My Sponsor Tree</h1>
                        <p className="text-white/40 mt-1">Your referral network</p>
                    </div>
                    <select value={depth} onChange={(e) => router.get('/user/tree/sponsor', { depth: e.target.value })} className="glass-input text-sm">
                        {[3, 4, 5, 6, 8, 10].map(d => <option key={d} value={d} className="bg-surface-900">{d} levels</option>)}
                    </select>
                </div>
                <GlassCard hover={false} className="overflow-x-auto min-h-[50vh]">
                    {treeData ? <SponsorNode node={treeData} /> : <div className="text-center py-16 text-white/30">No tree data available</div>}
                </GlassCard>
            </div>
        </UserLayout>
    );
}
