import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Pagination from '@/Components/ui/Pagination';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    payouts: { data: any[]; links: any[]; from: number; to: number; total: number };
    filters: any;
}

const fmt = (v: number) => `₹${Number(v).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

export default function PayoutsIndex({ payouts, filters }: Props) {
    const [rejectId, setRejectId] = useState<number | null>(null);
    const [rejectReason, setRejectReason] = useState('');

    return (
        <AdminLayout>
            <Head title="Payouts" />
            <div className="space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-white">Payout Requests</h1>
                    <select value={filters.status || ''} onChange={e => router.get('/admin/payouts', { status: e.target.value || undefined }, { preserveState: true })} className="glass-input text-sm">
                        <option value="" className="bg-surface-900">All Status</option>
                        <option value="pending" className="bg-surface-900">Pending</option>
                        <option value="approved" className="bg-surface-900">Approved</option>
                        <option value="completed" className="bg-surface-900">Completed</option>
                        <option value="rejected" className="bg-surface-900">Rejected</option>
                    </select>
                </div>
                <GlassCard padding="none" hover={false}>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead><tr><th>Member</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                            <tbody>
                                {payouts.data.map((p: any) => (
                                    <tr key={p.id}>
                                        <td className="text-sm">{p.user?.username || '—'}</td>
                                        <td className="text-white font-medium text-sm">{fmt(p.amount)}</td>
                                        <td className="text-white/50 text-sm capitalize">{p.method?.replace(/_/g, ' ')}</td>
                                        <td>
                                            <span className={
                                                p.status === 'completed' ? 'badge-success' :
                                                p.status === 'pending' ? 'badge-warning' :
                                                p.status === 'approved' ? 'badge-info' : 'badge-danger'
                                            }>{p.status}</span>
                                        </td>
                                        <td className="text-white/40 text-xs">{new Date(p.created_at).toLocaleDateString()}</td>
                                        <td>
                                            {p.status === 'pending' && (
                                                <div className="flex items-center gap-2">
                                                    <button onClick={() => router.post(`/admin/payouts/${p.id}/approve`)} className="text-xs text-green-400 hover:text-green-300">Approve</button>
                                                    <button onClick={() => setRejectId(p.id)} className="text-xs text-red-400 hover:text-red-300">Reject</button>
                                                </div>
                                            )}
                                            {p.status === 'approved' && (
                                                <button onClick={() => router.post(`/admin/payouts/${p.id}/complete`)} className="text-xs text-primary-400 hover:text-primary-300">Complete</button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="px-4 pb-4"><Pagination links={payouts.links} from={payouts.from} to={payouts.to} total={payouts.total} /></div>
                </GlassCard>

                {/* Reject Modal */}
                {rejectId && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" onClick={() => setRejectId(null)}>
                        <div className="glass-card p-6 w-full max-w-md" onClick={e => e.stopPropagation()}>
                            <h3 className="text-lg font-semibold text-white mb-4">Reject Payout</h3>
                            <textarea value={rejectReason} onChange={e => setRejectReason(e.target.value)} placeholder="Reason for rejection..." className="glass-input w-full h-24 resize-none" />
                            <div className="flex justify-end gap-3 mt-4">
                                <button onClick={() => setRejectId(null)} className="btn-secondary text-sm">Cancel</button>
                                <button onClick={() => { router.post(`/admin/payouts/${rejectId}/reject`, { reason: rejectReason }); setRejectId(null); setRejectReason(''); }} className="btn-danger text-sm">Reject</button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
