import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Pagination from '@/Components/ui/Pagination';
import { Head, Link } from '@inertiajs/react';

interface Props {
    rank: any;
    members: { data: any[]; links: any[]; from: number; to: number; total: number };
    recentPromotions: any[];
}

export default function RankShow({ rank, members, recentPromotions }: Props) {
    return (
        <AdminLayout>
            <Head title={`Rank - ${rank.name}`} />
            <div className="space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{rank.name}</h1>
                        <p className="text-white/40 mt-1">Level {rank.order} · {members.total} members</p>
                    </div>
                    <Link href="/admin/ranks" className="text-sm text-white/40 hover:text-white">← Back</Link>
                </div>

                {rank.configs && rank.configs.length > 0 && (
                    <GlassCard>
                        <h3 className="text-lg font-semibold text-white mb-4">Rank Criteria</h3>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            {rank.configs.map((c: any) => (
                                <div key={c.id}>
                                    <span className="text-white/30 capitalize">{c.criteria?.replace(/_/g, ' ')}</span>
                                    <p className="text-white font-medium mt-1">{c.value}</p>
                                </div>
                            ))}
                        </div>
                    </GlassCard>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2">
                        <GlassCard padding="none" hover={false}>
                            <div className="p-4 border-b border-white/5">
                                <h3 className="text-lg font-semibold text-white">Members at this Rank</h3>
                            </div>
                            <table className="table-glass">
                                <thead><tr><th>Member</th><th>Sponsor</th><th>Joined</th></tr></thead>
                                <tbody>
                                    {members.data.map((m: any) => (
                                        <tr key={m.id}>
                                            <td>
                                                <Link href={`/admin/members/${m.id}`} className="text-sm text-white hover:text-primary-400 transition">
                                                    {m.first_name} {m.last_name} <span className="text-white/30">@{m.username}</span>
                                                </Link>
                                            </td>
                                            <td className="text-sm text-white/40">{m.sponsor?.username || '—'}</td>
                                            <td className="text-xs text-white/30">{new Date(m.created_at).toLocaleDateString()}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            <div className="px-4 pb-4">
                                <Pagination links={members.links} from={members.from} to={members.to} total={members.total} />
                            </div>
                        </GlassCard>
                    </div>

                    <GlassCard>
                        <h3 className="text-lg font-semibold text-white mb-4">Recent Promotions</h3>
                        {recentPromotions.length === 0 ? (
                            <p className="text-white/30 text-sm text-center py-8">No recent promotions</p>
                        ) : (
                            <div className="space-y-3">
                                {recentPromotions.map((p: any) => (
                                    <div key={p.id} className="flex items-center justify-between py-2 border-b border-white/5 last:border-0">
                                        <div>
                                            <p className="text-sm text-white">{p.user?.first_name} {p.user?.last_name}</p>
                                            <p className="text-xs text-white/30">@{p.user?.username}</p>
                                        </div>
                                        <span className="text-xs text-white/40">{new Date(p.created_at).toLocaleDateString()}</span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </GlassCard>
                </div>
            </div>
        </AdminLayout>
    );
}
