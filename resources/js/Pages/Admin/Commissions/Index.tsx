import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Pagination from '@/Components/ui/Pagination';
import { Head, router } from '@inertiajs/react';

interface Props {
    commissions: { data: any[]; links: any[]; from: number; to: number; total: number };
    summary: Array<{ commission_type: string; count: number; total: number }>;
    filters: any;
    commissionTypes: string[];
}

const fmt = (v: number) => `₹${Number(v).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

export default function CommissionsIndex({ commissions, summary, filters, commissionTypes }: Props) {
    return (
        <AdminLayout>
            <Head title="Commissions" />
            <div className="space-y-6 animate-fade-in">
                <h1 className="text-2xl font-bold text-white">Commissions</h1>

                {/* Summary */}
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    {summary.map(s => (
                        <GlassCard key={s.commission_type} padding="sm">
                            <p className="text-xs text-white/30 capitalize">{s.commission_type.replace(/_/g, ' ')}</p>
                            <p className="text-lg font-bold text-white mt-1">{fmt(s.total)}</p>
                            <p className="text-xs text-white/20">{s.count} entries</p>
                        </GlassCard>
                    ))}
                </div>

                {/* Filters */}
                <GlassCard padding="sm">
                    <div className="flex flex-wrap gap-3">
                        <select value={filters.type || ''} onChange={e => router.get('/admin/commissions', { ...filters, type: e.target.value || undefined }, { preserveState: true })} className="glass-input text-sm">
                            <option value="" className="bg-surface-900">All Types</option>
                            {commissionTypes.map(t => <option key={t} value={t} className="bg-surface-900 capitalize">{t.replace(/_/g, ' ')}</option>)}
                        </select>
                        <input type="date" value={filters.date_from || ''} onChange={e => router.get('/admin/commissions', { ...filters, date_from: e.target.value || undefined }, { preserveState: true })} className="glass-input text-sm" />
                        <input type="date" value={filters.date_to || ''} onChange={e => router.get('/admin/commissions', { ...filters, date_to: e.target.value || undefined }, { preserveState: true })} className="glass-input text-sm" />
                    </div>
                </GlassCard>

                {/* Table */}
                <GlassCard padding="none" hover={false}>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead><tr><th>Member</th><th>Type</th><th>From</th><th>Amount</th><th>TDS</th><th>Net</th><th>Date</th></tr></thead>
                            <tbody>
                                {commissions.data.map((c: any) => (
                                    <tr key={c.id}>
                                        <td className="text-sm">{c.user?.username || '—'}</td>
                                        <td><span className="badge-info capitalize text-xs">{c.commission_type?.replace(/_/g, ' ')}</span></td>
                                        <td className="text-white/40 text-sm">{c.from_user?.username || '—'}</td>
                                        <td className="text-white font-medium text-sm">{fmt(c.amount)}</td>
                                        <td className="text-red-400/60 text-sm">{fmt(c.tds_amount || 0)}</td>
                                        <td className="text-green-400 font-medium text-sm">{fmt(c.net_amount || c.amount)}</td>
                                        <td className="text-white/40 text-xs">{new Date(c.created_at).toLocaleDateString()}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="px-4 pb-4">
                        <Pagination links={commissions.links} from={commissions.from} to={commissions.to} total={commissions.total} />
                    </div>
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
