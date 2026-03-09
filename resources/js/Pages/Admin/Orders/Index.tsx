import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Pagination from '@/Components/ui/Pagination';
import { Head, Link, router } from '@inertiajs/react';

interface Props {
    orders: { data: any[]; links: any[]; from: number; to: number; total: number };
    filters: any;
}

export default function OrdersIndex({ orders, filters }: Props) {
    return (
        <AdminLayout>
            <Head title="Orders" />
            <div className="space-y-6 animate-fade-in">
                <h1 className="text-2xl font-bold text-white">Orders</h1>
                <GlassCard padding="none" hover={false}>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead><tr><th>Order #</th><th>Member</th><th>Total</th><th>PV</th><th>Type</th><th>Status</th><th>Date</th><th></th></tr></thead>
                            <tbody>
                                {orders.data.map((o: any) => (
                                    <tr key={o.id}>
                                        <td className="text-sm font-medium text-primary-400">{o.order_number}</td>
                                        <td className="text-sm text-white/70">{o.user?.username || '—'}</td>
                                        <td className="text-sm text-white font-medium">₹{Number(o.total).toLocaleString()}</td>
                                        <td className="text-sm text-accent-400">{o.total_pv}</td>
                                        <td><span className="badge-info capitalize text-xs">{o.order_type}</span></td>
                                        <td><span className={o.status === 'completed' ? 'badge-success' : o.status === 'pending' ? 'badge-warning' : 'badge-danger'}>{o.status}</span></td>
                                        <td className="text-white/40 text-xs">{new Date(o.created_at).toLocaleDateString()}</td>
                                        <td><Link href={`/admin/orders/${o.id}`} className="text-xs text-primary-400 hover:text-primary-300">View</Link></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="px-4 pb-4"><Pagination links={orders.links} from={orders.from} to={orders.to} total={orders.total} /></div>
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
