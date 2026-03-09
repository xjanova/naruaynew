import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, Link, useForm } from '@inertiajs/react';

interface Props {
    member: any;
    ranks: Array<{ id: number; name: string; order: number }>;
}

export default function MemberEdit({ member, ranks }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        first_name: member.first_name || '',
        last_name: member.last_name || '',
        email: member.email || '',
        phone: member.phone || '',
        active: member.active,
        is_blocked: member.is_blocked,
        current_rank_id: member.current_rank_id || '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/members/${member.id}`);
    };

    return (
        <AdminLayout>
            <Head title={`Edit - ${member.username}`} />
            <div className="max-w-2xl mx-auto space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-white">Edit Member</h1>
                    <Link href={`/admin/members/${member.id}`} className="text-sm text-white/40 hover:text-white">← Back</Link>
                </div>
                <GlassCard>
                    <form onSubmit={submit} className="space-y-5">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm text-white/50 mb-1.5">First Name</label>
                                <input type="text" value={data.first_name} onChange={e => setData('first_name', e.target.value)} className="glass-input w-full" />
                                {errors.first_name && <p className="text-red-400 text-xs mt-1">{errors.first_name}</p>}
                            </div>
                            <div>
                                <label className="block text-sm text-white/50 mb-1.5">Last Name</label>
                                <input type="text" value={data.last_name} onChange={e => setData('last_name', e.target.value)} className="glass-input w-full" />
                                {errors.last_name && <p className="text-red-400 text-xs mt-1">{errors.last_name}</p>}
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm text-white/50 mb-1.5">Email</label>
                            <input type="email" value={data.email} onChange={e => setData('email', e.target.value)} className="glass-input w-full" />
                            {errors.email && <p className="text-red-400 text-xs mt-1">{errors.email}</p>}
                        </div>
                        <div>
                            <label className="block text-sm text-white/50 mb-1.5">Phone</label>
                            <input type="text" value={data.phone} onChange={e => setData('phone', e.target.value)} className="glass-input w-full" />
                        </div>
                        <div>
                            <label className="block text-sm text-white/50 mb-1.5">Rank</label>
                            <select value={data.current_rank_id} onChange={e => setData('current_rank_id', e.target.value)} className="glass-input w-full">
                                <option value="" className="bg-surface-900">Unranked</option>
                                {ranks.map(r => <option key={r.id} value={r.id} className="bg-surface-900">{r.name}</option>)}
                            </select>
                        </div>
                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" checked={data.active} onChange={e => setData('active', e.target.checked)} className="rounded bg-white/5 border-white/20 text-primary-500 focus:ring-primary-500" />
                                <span className="text-sm text-white/70">Active</span>
                            </label>
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" checked={data.is_blocked} onChange={e => setData('is_blocked', e.target.checked)} className="rounded bg-white/5 border-white/20 text-red-500 focus:ring-red-500" />
                                <span className="text-sm text-white/70">Blocked</span>
                            </label>
                        </div>
                        <div className="flex justify-end gap-3 pt-4 border-t border-white/5">
                            <Link href={`/admin/members/${member.id}`} className="btn-secondary text-sm">Cancel</Link>
                            <button type="submit" disabled={processing} className="btn-primary text-sm">{processing ? 'Saving...' : 'Save Changes'}</button>
                        </div>
                    </form>
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
