import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import StatCard from '@/Components/ui/StatCard';
import { Head, Link, router } from '@inertiajs/react';
import { UsersIcon, CurrencyDollarIcon, ChartBarIcon, ShieldCheckIcon } from '@heroicons/react/24/outline';

interface Props {
    member: any;
    commissions: any[];
    transactions: any[];
    downlineCount: number;
}

const fmt = (v: number) => `₹${v.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

export default function MemberShow({ member, commissions, transactions, downlineCount }: Props) {
    return (
        <AdminLayout>
            <Head title={`Member - ${member.username}`} />
            <div className="space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-2xl font-bold text-white">
                            {member.first_name?.[0]}{member.last_name?.[0]}
                        </div>
                        <div>
                            <h1 className="text-2xl font-bold text-white">{member.first_name} {member.last_name}</h1>
                            <p className="text-white/40">@{member.username} · {member.email}</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link href={`/admin/members/${member.id}/edit`} className="btn-secondary text-sm">Edit</Link>
                        <Link href={`/admin/tree?user_id=${member.id}`} className="btn-primary text-sm">View in Tree</Link>
                        <button onClick={() => router.post(`/admin/members/${member.id}/toggle-block`)} className={member.is_blocked ? 'btn-secondary text-sm' : 'btn-danger text-sm'}>
                            {member.is_blocked ? 'Unblock' : 'Block'}
                        </button>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <StatCard title="Balance" value={fmt(member.balance?.balance || 0)} icon={<CurrencyDollarIcon className="w-6 h-6" />} color="primary" />
                    <StatCard title="Purchase Wallet" value={fmt(member.balance?.purchase_wallet || 0)} icon={<CurrencyDollarIcon className="w-6 h-6" />} color="accent" />
                    <StatCard title="Downline" value={downlineCount} icon={<UsersIcon className="w-6 h-6" />} color="green" />
                    <StatCard title="Rank" value={member.rank?.name || 'Unranked'} subtitle={`PV: ${member.personal_pv} · GPV: ${member.group_pv}`} icon={<ChartBarIcon className="w-6 h-6" />} color="amber" />
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <GlassCard>
                        <h3 className="text-lg font-semibold text-white mb-4">Recent Commissions</h3>
                        <table className="table-glass">
                            <thead><tr><th>Type</th><th>Amount</th><th>Date</th></tr></thead>
                            <tbody>
                                {commissions.length === 0 ? (
                                    <tr><td colSpan={3} className="text-center py-8 text-white/30">No commissions</td></tr>
                                ) : commissions.map((c: any) => (
                                    <tr key={c.id}>
                                        <td className="capitalize text-sm">{c.commission_type?.replace(/_/g, ' ')}</td>
                                        <td className="text-green-400 font-medium text-sm">{fmt(c.amount)}</td>
                                        <td className="text-white/40 text-xs">{new Date(c.created_at).toLocaleDateString()}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </GlassCard>

                    <GlassCard>
                        <h3 className="text-lg font-semibold text-white mb-4">Recent Transactions</h3>
                        <table className="table-glass">
                            <thead><tr><th>Description</th><th>Amount</th><th>Date</th></tr></thead>
                            <tbody>
                                {transactions.length === 0 ? (
                                    <tr><td colSpan={3} className="text-center py-8 text-white/30">No transactions</td></tr>
                                ) : transactions.map((t: any) => (
                                    <tr key={t.id}>
                                        <td className="text-sm">{t.description}</td>
                                        <td className={`font-medium text-sm ${t.type === 'credit' ? 'text-green-400' : 'text-red-400'}`}>{t.type === 'credit' ? '+' : '-'}{fmt(t.amount)}</td>
                                        <td className="text-white/40 text-xs">{new Date(t.created_at).toLocaleDateString()}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </GlassCard>
                </div>

                <GlassCard>
                    <h3 className="text-lg font-semibold text-white mb-4">Member Details</h3>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div><span className="text-white/30">Phone</span><p className="text-white mt-1">{member.phone || '—'}</p></div>
                        <div><span className="text-white/30">Sponsor</span><p className="text-white mt-1">{member.sponsor ? `@${member.sponsor.username}` : '—'}</p></div>
                        <div><span className="text-white/30">Position</span><p className="text-white mt-1 capitalize">{member.position || '—'}</p></div>
                        <div><span className="text-white/30">Joined</span><p className="text-white mt-1">{new Date(member.created_at).toLocaleDateString()}</p></div>
                        <div><span className="text-white/30">Status</span><p className="mt-1">{member.active ? <span className="badge-success">Active</span> : <span className="badge-danger">Inactive</span>}</p></div>
                        <div><span className="text-white/30">Blocked</span><p className="mt-1">{member.is_blocked ? <span className="badge-danger">Yes</span> : <span className="badge-success">No</span>}</p></div>
                        <div><span className="text-white/30">Subscription</span><p className="text-white mt-1 capitalize">{member.subscription_status || '—'}</p></div>
                        <div><span className="text-white/30">2FA</span><p className="mt-1">{member.google2fa_enabled ? <span className="badge-success">Enabled</span> : <span className="badge-warning">Disabled</span>}</p></div>
                    </div>
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
