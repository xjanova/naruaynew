import UserLayout from '@/Layouts/UserLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, useForm } from '@inertiajs/react';

interface Props {
    packages: Array<{ id: number; name: string; price: number; pv: number }>;
    sponsor: { id: number; username: string; first_name: string; last_name: string };
    positions: string[];
}

export default function RegisterMember({ packages, sponsor, positions }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        username: '', email: '', first_name: '', last_name: '', phone: '',
        password: '', password_confirmation: '',
        product_id: packages[0]?.id?.toString() || '',
        position: 'left',
        payment_method: 'ewallet',
        epin_code: '',
    });

    const submit = (e: React.FormEvent) => { e.preventDefault(); post('/user/register-member'); };
    const selectedPkg = packages.find(p => p.id === Number(data.product_id));

    return (
        <UserLayout>
            <Head title="Register New Member" />
            <div className="max-w-3xl mx-auto space-y-6 animate-fade-in">
                <h1 className="text-2xl font-bold text-white">Register New Member</h1>
                <p className="text-white/40">Sponsor: <span className="text-primary-400">@{sponsor.username}</span> ({sponsor.first_name} {sponsor.last_name})</p>

                <form onSubmit={submit}>
                    <div className="space-y-6">
                        <GlassCard>
                            <h3 className="text-lg font-semibold text-white mb-4">Personal Information</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Username *</label>
                                    <input type="text" value={data.username} onChange={e => setData('username', e.target.value)} className="glass-input w-full" />
                                    {errors.username && <p className="text-red-400 text-xs mt-1">{errors.username}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Email *</label>
                                    <input type="email" value={data.email} onChange={e => setData('email', e.target.value)} className="glass-input w-full" />
                                    {errors.email && <p className="text-red-400 text-xs mt-1">{errors.email}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">First Name *</label>
                                    <input type="text" value={data.first_name} onChange={e => setData('first_name', e.target.value)} className="glass-input w-full" />
                                    {errors.first_name && <p className="text-red-400 text-xs mt-1">{errors.first_name}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Last Name *</label>
                                    <input type="text" value={data.last_name} onChange={e => setData('last_name', e.target.value)} className="glass-input w-full" />
                                    {errors.last_name && <p className="text-red-400 text-xs mt-1">{errors.last_name}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Phone</label>
                                    <input type="text" value={data.phone} onChange={e => setData('phone', e.target.value)} className="glass-input w-full" />
                                </div>
                            </div>
                        </GlassCard>

                        <GlassCard>
                            <h3 className="text-lg font-semibold text-white mb-4">Account Security</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Password *</label>
                                    <input type="password" value={data.password} onChange={e => setData('password', e.target.value)} className="glass-input w-full" />
                                    {errors.password && <p className="text-red-400 text-xs mt-1">{errors.password}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Confirm Password *</label>
                                    <input type="password" value={data.password_confirmation} onChange={e => setData('password_confirmation', e.target.value)} className="glass-input w-full" />
                                </div>
                            </div>
                        </GlassCard>

                        <GlassCard>
                            <h3 className="text-lg font-semibold text-white mb-4">Package & Placement</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Registration Package *</label>
                                    <select value={data.product_id} onChange={e => setData('product_id', e.target.value)} className="glass-input w-full">
                                        {packages.map(p => <option key={p.id} value={p.id} className="bg-surface-900">{p.name} — ₹{p.price} (PV: {p.pv})</option>)}
                                    </select>
                                    {selectedPkg && <p className="text-xs text-primary-400 mt-1">₹{selectedPkg.price.toLocaleString()} · {selectedPkg.pv} PV</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Position *</label>
                                    <div className="flex gap-4 mt-2">
                                        {positions.map(pos => (
                                            <label key={pos} className="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="position" value={pos} checked={data.position === pos} onChange={e => setData('position', e.target.value)} className="text-primary-500 focus:ring-primary-500 bg-white/5 border-white/20" />
                                                <span className="text-sm text-white/70 capitalize">{pos}</span>
                                            </label>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </GlassCard>

                        <GlassCard>
                            <h3 className="text-lg font-semibold text-white mb-4">Payment</h3>
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Payment Method *</label>
                                    <select value={data.payment_method} onChange={e => setData('payment_method', e.target.value)} className="glass-input w-full">
                                        <option value="ewallet" className="bg-surface-900">E-Wallet</option>
                                        <option value="epin" className="bg-surface-900">E-PIN</option>
                                    </select>
                                </div>
                                {data.payment_method === 'epin' && (
                                    <div>
                                        <label className="block text-sm text-white/50 mb-1">E-PIN Code *</label>
                                        <input type="text" value={data.epin_code} onChange={e => setData('epin_code', e.target.value)} className="glass-input w-full" placeholder="XXXX-XXXX-XXXX" />
                                        {errors.epin_code && <p className="text-red-400 text-xs mt-1">{errors.epin_code}</p>}
                                    </div>
                                )}
                            </div>
                        </GlassCard>

                        <div className="flex justify-end">
                            <button type="submit" disabled={processing} className="btn-primary text-base px-10 py-3">{processing ? 'Registering...' : 'Register Member'}</button>
                        </div>
                    </div>
                </form>
            </div>
        </UserLayout>
    );
}
