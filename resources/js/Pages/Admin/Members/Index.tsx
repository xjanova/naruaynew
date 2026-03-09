import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Pagination from '@/Components/ui/Pagination';
import { Head, Link, router } from '@inertiajs/react';
import { MagnifyingGlassIcon } from '@heroicons/react/24/outline';

interface Member {
    id: number;
    username: string;
    first_name: string;
    last_name: string;
    email: string;
    active: boolean;
    is_blocked: boolean;
    current_rank_id: number | null;
    created_at: string;
    rank?: { id: number; name: string };
    sponsor?: { id: number; username: string; first_name: string; last_name: string };
}

interface Props {
    members: {
        data: Member[];
        links: any[];
        from: number;
        to: number;
        total: number;
    };
    ranks: Array<{ id: number; name: string }>;
    filters: { search?: string; status?: string; rank?: string };
}

export default function MembersIndex({ members, ranks, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/members', { ...filters, search }, { preserveState: true });
    };

    return (
        <AdminLayout>
            <Head title="Members" />
            <div className="space-y-6 animate-fade-in">
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Members</h1>
                        <p className="text-white/40 mt-1">{members.total} total members</p>
                    </div>
                </div>

                {/* Filters */}
                <GlassCard padding="sm">
                    <div className="flex flex-col sm:flex-row gap-3">
                        <form onSubmit={handleSearch} className="flex-1 relative">
                            <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
                            <input type="text" value={search} onChange={e => setSearch(e.target.value)} placeholder="Search by name, username, email..." className="glass-input w-full pl-10 text-sm" />
                        </form>
                        <select value={filters.status || ''} onChange={e => router.get('/admin/members', { ...filters, status: e.target.value || undefined }, { preserveState: true })} className="glass-input text-sm">
                            <option value="" className="bg-surface-900">All Status</option>
                            <option value="active" className="bg-surface-900">Active</option>
                            <option value="inactive" className="bg-surface-900">Inactive</option>
                            <option value="blocked" className="bg-surface-900">Blocked</option>
                        </select>
                        <select value={filters.rank || ''} onChange={e => router.get('/admin/members', { ...filters, rank: e.target.value || undefined }, { preserveState: true })} className="glass-input text-sm">
                            <option value="" className="bg-surface-900">All Ranks</option>
                            {ranks.map(r => <option key={r.id} value={r.id} className="bg-surface-900">{r.name}</option>)}
                        </select>
                    </div>
                </GlassCard>

                {/* Table */}
                <GlassCard padding="none" hover={false}>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Sponsor</th>
                                    <th>Rank</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {members.data.map(m => (
                                    <tr key={m.id}>
                                        <td>
                                            <div className="flex items-center gap-3">
                                                <div className="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-xs font-bold text-white shrink-0">
                                                    {m.first_name?.[0]}{m.last_name?.[0]}
                                                </div>
                                                <div>
                                                    <Link href={`/admin/members/${m.id}`} className="font-medium text-white hover:text-primary-400 transition">{m.first_name} {m.last_name}</Link>
                                                    <p className="text-xs text-white/30">@{m.username}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="text-white/50 text-sm">{m.sponsor ? `@${m.sponsor.username}` : '—'}</td>
                                        <td><span className="badge-info">{m.rank?.name || 'Unranked'}</span></td>
                                        <td>
                                            {m.is_blocked ? <span className="badge-danger">Blocked</span> : m.active ? <span className="badge-success">Active</span> : <span className="badge-warning">Inactive</span>}
                                        </td>
                                        <td className="text-white/40 text-xs">{new Date(m.created_at).toLocaleDateString()}</td>
                                        <td>
                                            <div className="flex items-center gap-2">
                                                <Link href={`/admin/members/${m.id}`} className="text-xs text-primary-400 hover:text-primary-300">View</Link>
                                                <Link href={`/admin/members/${m.id}/edit`} className="text-xs text-white/40 hover:text-white">Edit</Link>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="px-4 pb-4">
                        <Pagination links={members.links} from={members.from} to={members.to} total={members.total} />
                    </div>
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
