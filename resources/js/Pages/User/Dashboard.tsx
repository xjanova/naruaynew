import UserLayout from '@/Layouts/UserLayout';
import StatCard from '@/Components/ui/StatCard';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, Link } from '@inertiajs/react';
import {
    WalletIcon,
    CurrencyDollarIcon,
    UsersIcon,
    ArrowTrendingUpIcon,
    TrophyIcon,
    ChartBarIcon,
} from '@heroicons/react/24/outline';
import {
    AreaChart,
    Area,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
} from 'recharts';

interface Props {
    stats: {
        balance: number;
        purchase_wallet: number;
        total_earned: number;
        month_earned: number;
        personal_pv: number;
        group_pv: number;
        rank: string;
        direct_referrals: number;
        total_downline: number;
    };
    recentCommissions: Array<{
        id: number;
        commission_type: string;
        amount: number;
        created_at: string;
    }>;
    recentTransactions: Array<{
        id: number;
        type: string;
        amount: number;
        balance_after: number;
        description: string;
        created_at: string;
    }>;
    earningChart: Array<{ date: string; total: number }>;
}

const formatCurrency = (val: number) => `₹${val.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

const CustomTooltip = ({ active, payload, label }: any) => {
    if (!active || !payload?.length) return null;
    return (
        <div className="glass-card p-3 !rounded-lg text-sm">
            <p className="text-white/50 mb-1">{label}</p>
            {payload.map((p: any, i: number) => (
                <p key={i} className="text-white font-medium">{formatCurrency(p.value)}</p>
            ))}
        </div>
    );
};

export default function Dashboard({ stats, recentCommissions, recentTransactions, earningChart }: Props) {
    return (
        <UserLayout>
            <Head title="Dashboard" />

            <div className="space-y-6 animate-fade-in">
                <div>
                    <h1 className="text-2xl font-bold text-white">Dashboard</h1>
                    <p className="text-white/40 mt-1">Welcome back! Here's your network overview.</p>
                </div>

                {/* Balance Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard
                        title="E-Wallet Balance"
                        value={formatCurrency(stats.balance)}
                        icon={<WalletIcon className="w-6 h-6" />}
                        color="primary"
                    />
                    <StatCard
                        title="This Month Earned"
                        value={formatCurrency(stats.month_earned)}
                        subtitle={`Total: ${formatCurrency(stats.total_earned)}`}
                        icon={<CurrencyDollarIcon className="w-6 h-6" />}
                        color="green"
                    />
                    <StatCard
                        title="Personal PV"
                        value={stats.personal_pv}
                        subtitle={`Group PV: ${stats.group_pv.toLocaleString()}`}
                        icon={<ChartBarIcon className="w-6 h-6" />}
                        color="accent"
                    />
                    <StatCard
                        title="Rank"
                        value={stats.rank}
                        subtitle={`${stats.direct_referrals} direct · ${stats.total_downline} total`}
                        icon={<TrophyIcon className="w-6 h-6" />}
                        color="amber"
                    />
                </div>

                {/* Quick Actions + Chart */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Quick Actions */}
                    <GlassCard className="lg:col-span-1">
                        <h3 className="text-lg font-semibold text-white mb-4">Quick Actions</h3>
                        <div className="space-y-3">
                            <Link href="/user/register-member" className="btn-primary w-full flex items-center justify-center gap-2 text-sm">
                                <UsersIcon className="w-4 h-4" /> Register New Member
                            </Link>
                            <Link href="/user/shop" className="btn-secondary w-full flex items-center justify-center gap-2 text-sm">
                                Repurchase Products
                            </Link>
                            <Link href="/user/tree/binary" className="btn-secondary w-full flex items-center justify-center gap-2 text-sm">
                                View Binary Tree
                            </Link>
                            <Link href="/user/wallet" className="btn-secondary w-full flex items-center justify-center gap-2 text-sm">
                                Wallet & Transactions
                            </Link>
                        </div>
                    </GlassCard>

                    {/* Earnings Chart */}
                    <GlassCard className="lg:col-span-2">
                        <h3 className="text-lg font-semibold text-white mb-4">Earnings (30 days)</h3>
                        <div className="h-56">
                            <ResponsiveContainer width="100%" height="100%">
                                <AreaChart data={earningChart}>
                                    <defs>
                                        <linearGradient id="earnGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stopColor="#22c55e" stopOpacity={0.3} />
                                            <stop offset="95%" stopColor="#22c55e" stopOpacity={0} />
                                        </linearGradient>
                                    </defs>
                                    <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" />
                                    <XAxis dataKey="date" tick={{ fill: 'rgba(255,255,255,0.3)', fontSize: 11 }} tickLine={false} axisLine={false} />
                                    <YAxis tick={{ fill: 'rgba(255,255,255,0.3)', fontSize: 11 }} tickLine={false} axisLine={false} />
                                    <Tooltip content={<CustomTooltip />} />
                                    <Area type="monotone" dataKey="total" stroke="#22c55e" fill="url(#earnGrad)" strokeWidth={2} />
                                </AreaChart>
                            </ResponsiveContainer>
                        </div>
                    </GlassCard>
                </div>

                {/* Recent Activity */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <GlassCard>
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-lg font-semibold text-white">Recent Commissions</h3>
                            <Link href="/user/wallet/commissions" className="text-sm text-primary-400 hover:text-primary-300">View all →</Link>
                        </div>
                        <div className="space-y-3">
                            {recentCommissions.length === 0 ? (
                                <p className="text-center text-white/30 py-8">No commissions yet</p>
                            ) : (
                                recentCommissions.map((c) => (
                                    <div key={c.id} className="flex items-center justify-between py-2 border-b border-white/5 last:border-0">
                                        <div>
                                            <p className="text-sm text-white/70 capitalize">{c.commission_type.replace(/_/g, ' ')}</p>
                                            <p className="text-xs text-white/30">{new Date(c.created_at).toLocaleDateString()}</p>
                                        </div>
                                        <span className="text-green-400 font-semibold text-sm">+{formatCurrency(c.amount)}</span>
                                    </div>
                                ))
                            )}
                        </div>
                    </GlassCard>

                    <GlassCard>
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-lg font-semibold text-white">Recent Transactions</h3>
                            <Link href="/user/wallet" className="text-sm text-primary-400 hover:text-primary-300">View all →</Link>
                        </div>
                        <div className="space-y-3">
                            {recentTransactions.length === 0 ? (
                                <p className="text-center text-white/30 py-8">No transactions yet</p>
                            ) : (
                                recentTransactions.map((t) => (
                                    <div key={t.id} className="flex items-center justify-between py-2 border-b border-white/5 last:border-0">
                                        <div>
                                            <p className="text-sm text-white/70">{t.description}</p>
                                            <p className="text-xs text-white/30">{new Date(t.created_at).toLocaleDateString()}</p>
                                        </div>
                                        <span className={`font-semibold text-sm ${t.type === 'credit' ? 'text-green-400' : 'text-red-400'}`}>
                                            {t.type === 'credit' ? '+' : '-'}{formatCurrency(t.amount)}
                                        </span>
                                    </div>
                                ))
                            )}
                        </div>
                    </GlassCard>
                </div>
            </div>
        </UserLayout>
    );
}
