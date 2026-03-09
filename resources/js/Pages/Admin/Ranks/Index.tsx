import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, Link } from '@inertiajs/react';

interface Props { ranks: Array<{ id: number; name: string; order: number; users_count: number }> }

export default function RanksIndex({ ranks }: Props) {
    return (
        <AdminLayout>
            <Head title="Ranks" />
            <div className="space-y-6 animate-fade-in">
                <h1 className="text-2xl font-bold text-white">Ranks</h1>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {ranks.map((rank, i) => (
                        <Link key={rank.id} href={`/admin/ranks/${rank.id}`}>
                            <GlassCard className="relative overflow-hidden">
                                <div className="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-primary-500 to-accent-500" />
                                <div className="ml-4">
                                    <div className="flex items-center justify-between">
                                        <h3 className="text-lg font-semibold text-white">{rank.name}</h3>
                                        <span className="text-xs text-white/30">Level {rank.order}</span>
                                    </div>
                                    <p className="text-2xl font-bold text-primary-400 mt-2">{rank.users_count}</p>
                                    <p className="text-xs text-white/30 mt-1">members at this rank</p>
                                </div>
                            </GlassCard>
                        </Link>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}
