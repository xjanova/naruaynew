import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Pagination from '@/Components/ui/Pagination';
import { Head, router, useForm } from '@inertiajs/react';

interface Props {
    epins: { data: any[]; links: any[]; from: number; to: number; total: number };
    filters: any;
    configs: Array<{ id: number; name: string; amount: number; pv: number }>;
}

export default function EpinsIndex({ epins, filters, configs }: Props) {
    const [showGenerate, setShowGenerate] = useState(false);
    const form = useForm({ config_id: configs[0]?.id?.toString() || '', quantity: '10' });

    const generate = (e: React.FormEvent) => {
        e.preventDefault();
        form.post('/admin/epins/generate', { onSuccess: () => { setShowGenerate(false); form.reset(); } });
    };

    return (
        <AdminLayout>
            <Head title="E-PINs" />
            <div className="space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-white">E-PINs</h1>
                    <div className="flex gap-3">
                        <select
                            value={filters.status || ''}
                            onChange={e => router.get('/admin/epins', { status: e.target.value || undefined }, { preserveState: true })}
                            className="glass-input text-sm"
                        >
                            <option value="" className="bg-surface-900">All Status</option>
                            <option value="available" className="bg-surface-900">Available</option>
                            <option value="allocated" className="bg-surface-900">Allocated</option>
                            <option value="used" className="bg-surface-900">Used</option>
                            <option value="expired" className="bg-surface-900">Expired</option>
                        </select>
                        <button onClick={() => setShowGenerate(true)} className="btn-primary text-sm">Generate E-PINs</button>
                    </div>
                </div>

                <GlassCard padding="none" hover={false}>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead>
                                <tr><th>Code</th><th>Amount</th><th>PV</th><th>Allocated To</th><th>Used By</th><th>Status</th><th>Expires</th></tr>
                            </thead>
                            <tbody>
                                {epins.data.length === 0 ? (
                                    <tr><td colSpan={7} className="text-center py-12 text-white/30">No E-PINs found</td></tr>
                                ) : epins.data.map((ep: any) => (
                                    <tr key={ep.id}>
                                        <td className="font-mono text-sm text-primary-400">{ep.code}</td>
                                        <td className="text-sm text-white font-medium">₹{Number(ep.amount).toLocaleString()}</td>
                                        <td className="text-sm text-accent-400">{ep.pv}</td>
                                        <td className="text-sm text-white/50">{ep.allocated_to?.username || '—'}</td>
                                        <td className="text-sm text-white/50">{ep.used_by?.username || '—'}</td>
                                        <td>
                                            <span className={
                                                ep.status === 'active' ? 'badge-success' :
                                                ep.status === 'used' ? 'badge-info' : 'badge-danger'
                                            }>{ep.status}</span>
                                        </td>
                                        <td className="text-xs text-white/40">{ep.expires_at ? new Date(ep.expires_at).toLocaleDateString() : '—'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="px-4 pb-4">
                        <Pagination links={epins.links} from={epins.from} to={epins.to} total={epins.total} />
                    </div>
                </GlassCard>

                {/* Generate Modal */}
                {showGenerate && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" onClick={() => setShowGenerate(false)}>
                        <div className="glass-card p-6 w-full max-w-md" onClick={e => e.stopPropagation()}>
                            <h3 className="text-lg font-semibold text-white mb-4">Generate E-PINs</h3>
                            <form onSubmit={generate} className="space-y-4">
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">E-PIN Configuration</label>
                                    <select value={form.data.config_id} onChange={e => form.setData('config_id', e.target.value)} className="glass-input w-full">
                                        {configs.map(c => (
                                            <option key={c.id} value={c.id} className="bg-surface-900">{c.name} — ₹{c.amount} (PV: {c.pv})</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Quantity</label>
                                    <input type="number" value={form.data.quantity} onChange={e => form.setData('quantity', e.target.value)} className="glass-input w-full" min="1" max="1000" />
                                </div>
                                <div className="flex justify-end gap-3 pt-2">
                                    <button type="button" onClick={() => setShowGenerate(false)} className="btn-secondary text-sm">Cancel</button>
                                    <button type="submit" disabled={form.processing} className="btn-primary text-sm">{form.processing ? 'Generating...' : 'Generate'}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
