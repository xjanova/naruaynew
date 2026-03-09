import UserLayout from '@/Layouts/UserLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, useForm } from '@inertiajs/react';

interface Props { user: any; }

export default function ProfileIndex({ user }: Props) {
    const pwForm = useForm({ current_password: '', password: '', password_confirmation: '' });
    const txPwForm = useForm({ current_transaction_password: '', transaction_password: '', transaction_password_confirmation: '' });

    const updatePassword = (e: React.FormEvent) => { e.preventDefault(); pwForm.post('/user/profile/password', { onSuccess: () => pwForm.reset() }); };
    const updateTxPassword = (e: React.FormEvent) => { e.preventDefault(); txPwForm.post('/user/profile/transaction-password', { onSuccess: () => txPwForm.reset() }); };

    return (
        <UserLayout>
            <Head title="Profile" />
            <div className="space-y-6 animate-fade-in max-w-4xl">
                <h1 className="text-2xl font-bold text-white">My Profile</h1>

                {/* Profile Info */}
                <GlassCard>
                    <div className="flex items-center gap-6 mb-6">
                        <div className="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-3xl font-bold text-white">
                            {user.first_name?.[0]}{user.last_name?.[0]}
                        </div>
                        <div>
                            <h2 className="text-xl font-bold text-white">{user.first_name} {user.last_name}</h2>
                            <p className="text-white/40">@{user.username} · {user.email}</p>
                            <div className="flex gap-2 mt-2">
                                {user.active ? <span className="badge-success">Active</span> : <span className="badge-danger">Inactive</span>}
                                {user.rank && <span className="badge-info">{user.rank.name}</span>}
                            </div>
                        </div>
                    </div>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div><span className="text-white/30">Phone</span><p className="text-white mt-1">{user.phone || '—'}</p></div>
                        <div><span className="text-white/30">Subscription</span><p className="text-white mt-1 capitalize">{user.subscription_status || '—'}</p></div>
                        <div><span className="text-white/30">Personal PV</span><p className="text-primary-400 mt-1 font-medium">{user.personal_pv}</p></div>
                        <div><span className="text-white/30">Group PV</span><p className="text-accent-400 mt-1 font-medium">{user.group_pv}</p></div>
                    </div>
                </GlassCard>

                {/* KYC */}
                <GlassCard>
                    <h3 className="text-lg font-semibold text-white mb-4">KYC Documents</h3>
                    {user.kyc_documents?.length > 0 ? (
                        <div className="space-y-3">
                            {user.kyc_documents.map((doc: any) => (
                                <div key={doc.id} className="flex items-center justify-between py-2 border-b border-white/5">
                                    <div>
                                        <p className="text-sm text-white">{doc.document_type}</p>
                                        <p className="text-xs text-white/30">{doc.document_number}</p>
                                    </div>
                                    <span className={doc.status === 'approved' ? 'badge-success' : doc.status === 'pending' ? 'badge-warning' : 'badge-danger'}>{doc.status}</span>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-white/30 text-sm">No KYC documents submitted</p>
                    )}
                </GlassCard>

                {/* Change Passwords */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <GlassCard>
                        <h3 className="text-lg font-semibold text-white mb-4">Change Password</h3>
                        <form onSubmit={updatePassword} className="space-y-4">
                            <div>
                                <label className="block text-sm text-white/50 mb-1">Current Password</label>
                                <input type="password" value={pwForm.data.current_password} onChange={e => pwForm.setData('current_password', e.target.value)} className="glass-input w-full" />
                                {pwForm.errors.current_password && <p className="text-red-400 text-xs mt-1">{pwForm.errors.current_password}</p>}
                            </div>
                            <div>
                                <label className="block text-sm text-white/50 mb-1">New Password</label>
                                <input type="password" value={pwForm.data.password} onChange={e => pwForm.setData('password', e.target.value)} className="glass-input w-full" />
                                {pwForm.errors.password && <p className="text-red-400 text-xs mt-1">{pwForm.errors.password}</p>}
                            </div>
                            <div>
                                <label className="block text-sm text-white/50 mb-1">Confirm Password</label>
                                <input type="password" value={pwForm.data.password_confirmation} onChange={e => pwForm.setData('password_confirmation', e.target.value)} className="glass-input w-full" />
                            </div>
                            <button type="submit" disabled={pwForm.processing} className="btn-primary text-sm">{pwForm.processing ? 'Updating...' : 'Update Password'}</button>
                        </form>
                    </GlassCard>

                    <GlassCard>
                        <h3 className="text-lg font-semibold text-white mb-4">Transaction Password</h3>
                        <form onSubmit={updateTxPassword} className="space-y-4">
                            <div>
                                <label className="block text-sm text-white/50 mb-1">Current Transaction Password</label>
                                <input type="password" value={txPwForm.data.current_transaction_password} onChange={e => txPwForm.setData('current_transaction_password', e.target.value)} className="glass-input w-full" />
                                {txPwForm.errors.current_transaction_password && <p className="text-red-400 text-xs mt-1">{txPwForm.errors.current_transaction_password}</p>}
                            </div>
                            <div>
                                <label className="block text-sm text-white/50 mb-1">New Transaction Password</label>
                                <input type="password" value={txPwForm.data.transaction_password} onChange={e => txPwForm.setData('transaction_password', e.target.value)} className="glass-input w-full" />
                                {txPwForm.errors.transaction_password && <p className="text-red-400 text-xs mt-1">{txPwForm.errors.transaction_password}</p>}
                            </div>
                            <div>
                                <label className="block text-sm text-white/50 mb-1">Confirm Transaction Password</label>
                                <input type="password" value={txPwForm.data.transaction_password_confirmation} onChange={e => txPwForm.setData('transaction_password_confirmation', e.target.value)} className="glass-input w-full" />
                            </div>
                            <button type="submit" disabled={txPwForm.processing} className="btn-primary text-sm">{txPwForm.processing ? 'Updating...' : 'Update'}</button>
                        </form>
                    </GlassCard>
                </div>
            </div>
        </UserLayout>
    );
}
