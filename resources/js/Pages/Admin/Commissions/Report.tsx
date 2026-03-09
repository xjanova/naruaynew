import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, router } from '@inertiajs/react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';

interface Props { data: any[]; period: string; filters: any; }

const fmt = (v: number) => `₹${Number(v).toLocaleString('en-IN')}`;

export default function CommissionReport({ data, period, filters }: Props) {
    const grouped = data.reduce((acc: any, item: any) => {
        if (!acc[item.period]) acc[item.period] = { period: item.period };
        acc[item.period][item.commission_type] = Number(item.total);
        return acc;
    }, {});
    const chartData = Object.values(grouped);
    const types = [...new Set(data.map((d: any) => d.commission_type))];
    const colors = ['#6366f1', '#d946ef', '#22c55e', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6', '#ec4899'];

    return (
        <AdminLayout>
            <Head title="Commission Report" />
            <div className="space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-white">Commission Report</h1>
                    <div className="flex gap-2">
                        {['daily', 'weekly', 'monthly', 'yearly'].map(p => (
                            <button key={p} onClick={() => router.get('/admin/commissions/report', { ...filters, period: p }, { preserveState: true })} className={`px-3 py-1.5 rounded-lg text-sm capitalize transition ${period === p ? 'bg-primary-500/20 text-primary-400 border border-primary-500/30' : 'text-white/40 hover:text-white/70 hover:bg-white/5'}`}>
                                {p}
                            </button>
                        ))}
                    </div>
                </div>
                <GlassCard>
                    <div className="h-96">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart data={chartData as any[]}>
                                <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" />
                                <XAxis dataKey="period" tick={{ fill: 'rgba(255,255,255,0.3)', fontSize: 11 }} />
                                <YAxis tick={{ fill: 'rgba(255,255,255,0.3)', fontSize: 11 }} tickFormatter={fmt} />
                                <Tooltip contentStyle={{ background: 'rgba(15,23,42,0.95)', border: '1px solid rgba(255,255,255,0.1)', borderRadius: '12px', color: 'white' }} />
                                <Legend wrapperStyle={{ color: 'rgba(255,255,255,0.5)' }} />
                                {types.map((t, i) => <Bar key={t} dataKey={t} fill={colors[i % colors.length]} radius={[4, 4, 0, 0]} />)}
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </GlassCard>

                <GlassCard padding="none" hover={false}>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead><tr><th>Period</th><th>Type</th><th>Count</th><th>Total</th><th>TDS</th><th>Service Charge</th></tr></thead>
                            <tbody>
                                {data.map((d: any, i: number) => (
                                    <tr key={i}>
                                        <td className="text-sm text-white/70">{d.period}</td>
                                        <td><span className="badge-info capitalize text-xs">{d.commission_type?.replace(/_/g, ' ')}</span></td>
                                        <td className="text-sm text-white/50">{d.count}</td>
                                        <td className="text-sm text-white font-medium">{fmt(d.total)}</td>
                                        <td className="text-sm text-red-400/60">{fmt(d.total_tds || 0)}</td>
                                        <td className="text-sm text-amber-400/60">{fmt(d.total_sc || 0)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
