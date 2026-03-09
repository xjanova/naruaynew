import UserLayout from '@/Layouts/UserLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Pagination from '@/Components/ui/Pagination';
import { Head, router } from '@inertiajs/react';

interface Props {
    commissions: { data: any[]; links: any[]; from: number; to: number; total: number };
    filters: any;
}

const fmt = (v: number) => `₹${Number(v).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

export default function Commissions({ commissions, filters }: Props) {
    return (
        <UserLayout>
            <Head title="My Commissions" />
            <div className="space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-white">My Commissions</h1>
                    <select value={filters.type || ''} onChange={e => router.get('/user/wallet/commissions', { type: e.target.value || undefined }, { preserveState: true })} className="glass-input text-sm">
                        <option value="" className="bg-surface-900">All Types</option>
                        <option value="level_commission" className="bg-surface-900">Level Commission</option>
                        <option value="referral" className="bg-surface-900">Referral</option>
                        <option value="binary_commission" className="bg-surface-900">Binary</option>
                        <option value="matching_bonus" className="bg-surface-900">Matching</option>
                        <option value="sales_commission" className="bg-surface-900">Sales</option>
                    </select>
                </div>

                <GlassCard padding="none" hover={false}>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead><tr><th>Type</th><th>From</th><th>Amount</th><th>TDS</th><th>Net</th><th>Level</th><th>Date</th></tr></thead>
                            <tbody>
                                {commissions.data.length === 0 ? (
                                    <tr><td colSpan={7} className="text-center py-12 text-white/30">No commissions earned yet</td></tr>
                                ) : commissions.data.map((c: any) => (
                                    <tr key={c.id}>
                                        <td><span className="badge-info capitalize text-xs">{c.commission_type?.replace(/_/g, ' ')}</span></td>
                                        <td className="text-sm text-white/50">{c.from_user?.username || '—'}</td>
                                        <td className="text-sm text-white font-medium">{fmt(c.amount)}</td>
                                        <td className="text-sm text-red-400/60">{fmt(c.tds_amount || 0)}</td>
                                        <td className="text-sm text-green-400 font-medium">{fmt(c.net_amount || c.amount)}</td>
                                        <td className="text-sm text-white/40">{c.level || '—'}</td>
                                        <td className="text-xs text-white/40">{new Date(c.created_at).toLocaleDateString()}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="px-4 pb-4"><Pagination links={commissions.links} from={commissions.from} to={commissions.to} total={commissions.total} /></div>
                </GlassCard>
            </div>
        </UserLayout>
    );
}
