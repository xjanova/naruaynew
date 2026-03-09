import UserLayout from '@/Layouts/UserLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Pagination from '@/Components/ui/Pagination';
import { Head } from '@inertiajs/react';

interface Props { payouts: { data: any[]; links: any[]; from: number; to: number; total: number } }
const fmt = (v: number) => `₹${Number(v).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

export default function Payouts({ payouts }: Props) {
    return (
        <UserLayout>
            <Head title="My Payouts" />
            <div className="space-y-6 animate-fade-in">
                <h1 className="text-2xl font-bold text-white">My Payouts</h1>
                <GlassCard padding="none" hover={false}>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead><tr><th>Amount</th><th>Method</th><th>Status</th><th>Requested</th><th>Processed</th></tr></thead>
                            <tbody>
                                {payouts.data.length === 0 ? (
                                    <tr><td colSpan={5} className="text-center py-12 text-white/30">No payout requests</td></tr>
                                ) : payouts.data.map((p: any) => (
                                    <tr key={p.id}>
                                        <td className="text-white font-medium text-sm">{fmt(p.amount)}</td>
                                        <td className="text-white/50 text-sm capitalize">{p.method?.replace(/_/g, ' ')}</td>
                                        <td><span className={p.status === 'completed' ? 'badge-success' : p.status === 'pending' ? 'badge-warning' : p.status === 'approved' ? 'badge-info' : 'badge-danger'}>{p.status}</span></td>
                                        <td className="text-white/40 text-xs">{new Date(p.created_at).toLocaleDateString()}</td>
                                        <td className="text-white/40 text-xs">{p.processed_at ? new Date(p.processed_at).toLocaleDateString() : '—'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="px-4 pb-4"><Pagination links={payouts.links} from={payouts.from} to={payouts.to} total={payouts.total} /></div>
                </GlassCard>
            </div>
        </UserLayout>
    );
}
