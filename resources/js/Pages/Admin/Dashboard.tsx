import AdminLayout from '@/Layouts/AdminLayout';
import StatCard from '@/Components/ui/StatCard';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, Link } from '@inertiajs/react';
import {
    UsersIcon,
    CurrencyDollarIcon,
    BanknotesIcon,
    UserPlusIcon,
    ShoppingCartIcon,
    ArrowTrendingUpIcon,
} from '@heroicons/react/24/outline';
import {
    AreaChart,
    Area,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    BarChart,
    Bar,
} from 'recharts';

interface Props {
    stats: {
        total_members: number;
        active_members: number;
        today_joins: number;
        total_revenue: number;
        today_revenue: number;
        pending_payouts: number;
        total_commissions: number;
        month_commissions: number;
    };
    recentMembers: Array<{
        id: number;
        username: string;
        first_name: string;
        last_name: string;
        email: string;
        active: boolean;
        created_at: string;
    }>;
    joinChart: Array<{ date: string; count: number }>;
    revenueChart: Array<{ date: string; total: number }>;
}

const formatCurrency = (val: number) => {
    if (val >= 1000000) return `₹${(val / 1000000).toFixed(1)}M`;
    if (val >= 1000) return `₹${(val / 1000).toFixed(1)}K`;
    return `₹${val.toFixed(0)}`;
};

const CustomTooltip = ({ active, payload, label }: any) => {
    if (!active || !payload?.length) return null;
    return (
        <div className="glass-card p-3 !rounded-lg text-sm">
            <p className="text-white/50 mb-1">{label}</p>
            {payload.map((p: any, i: number) => (
                <p key={i} className="text-white font-medium">
                    {p.name}: {typeof p.value === 'number' && p.value > 100 ? formatCurrency(p.value) : p.value}
                </p>
            ))}
        </div>
    );
};

export default function Dashboard({ stats, recentMembers, joinChart, revenueChart }: Props) {
    return (
        <AdminLayout>
            <Head title="Admin Dashboard" />

            <div className="space-y-6 animate-fade-in">
                {/* Page Header */}
                <div>
                    <h1 className="text-2xl font-bold text-white">Dashboard</h1>
                    <p className="text-white/40 mt-1">Overview of your MLM network</p>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard
                        title="Total Members"
                        value={stats.total_members}
                        subtitle={`${stats.active_members} active`}
                        icon={<UsersIcon className="w-6 h-6" />}
                        color="primary"
                    />
                    <StatCard
                        title="Today Joins"
                        value={stats.today_joins}
                        icon={<UserPlusIcon className="w-6 h-6" />}
                        color="green"
                    />
                    <StatCard
                        title="Total Revenue"
                        value={formatCurrency(stats.total_revenue)}
                        subtitle={`Today: ${formatCurrency(stats.today_revenue)}`}
                        icon={<CurrencyDollarIcon className="w-6 h-6" />}
                        color="accent"
                    />
                    <StatCard
                        title="Pending Payouts"
                        value={formatCurrency(stats.pending_payouts)}
                        subtitle={`Total Commissions: ${formatCurrency(stats.total_commissions)}`}
                        icon={<BanknotesIcon className="w-6 h-6" />}
                        color="amber"
                    />
                </div>

                {/* Charts */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <GlassCard>
                        <h3 className="text-lg font-semibold text-white mb-4">New Members (30 days)</h3>
                        <div className="h-64">
                            <ResponsiveContainer width="100%" height="100%">
                                <AreaChart data={joinChart}>
                                    <defs>
                                        <linearGradient id="joinGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stopColor="#6366f1" stopOpacity={0.3} />
                                            <stop offset="95%" stopColor="#6366f1" stopOpacity={0} />
                                        </linearGradient>
                                    </defs>
                                    <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" />
                                    <XAxis dataKey="date" tick={{ fill: 'rgba(255,255,255,0.3)', fontSize: 11 }} tickLine={false} axisLine={false} />
                                    <YAxis tick={{ fill: 'rgba(255,255,255,0.3)', fontSize: 11 }} tickLine={false} axisLine={false} />
                                    <Tooltip content={<CustomTooltip />} />
                                    <Area type="monotone" dataKey="count" stroke="#6366f1" fill="url(#joinGrad)" strokeWidth={2} name="Members" />
                                </AreaChart>
                            </ResponsiveContainer>
                        </div>
                    </GlassCard>

                    <GlassCard>
                        <h3 className="text-lg font-semibold text-white mb-4">Revenue (30 days)</h3>
                        <div className="h-64">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={revenueChart}>
                                    <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" />
                                    <XAxis dataKey="date" tick={{ fill: 'rgba(255,255,255,0.3)', fontSize: 11 }} tickLine={false} axisLine={false} />
                                    <YAxis tick={{ fill: 'rgba(255,255,255,0.3)', fontSize: 11 }} tickLine={false} axisLine={false} tickFormatter={formatCurrency} />
                                    <Tooltip content={<CustomTooltip />} />
                                    <Bar dataKey="total" fill="#d946ef" radius={[4, 4, 0, 0]} name="Revenue" />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </GlassCard>
                </div>

                {/* Recent Members */}
                <GlassCard>
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-semibold text-white">Recent Members</h3>
                        <Link href="/admin/members" className="text-sm text-primary-400 hover:text-primary-300 transition">
                            View all →
                        </Link>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                {recentMembers.map((m) => (
                                    <tr key={m.id}>
                                        <td>
                                            <Link href={`/admin/members/${m.id}`} className="hover:text-primary-400 transition">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-xs font-bold">
                                                        {m.first_name?.[0]}{m.last_name?.[0]}
                                                    </div>
                                                    <div>
                                                        <p className="font-medium text-white">{m.first_name} {m.last_name}</p>
                                                        <p className="text-xs text-white/30">@{m.username}</p>
                                                    </div>
                                                </div>
                                            </Link>
                                        </td>
                                        <td className="text-white/50">{m.email}</td>
                                        <td>
                                            <span className={m.active ? 'badge-success' : 'badge-danger'}>
                                                {m.active ? 'Active' : 'Inactive'}
                                            </span>
                                        </td>
                                        <td className="text-white/40 text-xs">
                                            {new Date(m.created_at).toLocaleDateString()}
                                        </td>
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
