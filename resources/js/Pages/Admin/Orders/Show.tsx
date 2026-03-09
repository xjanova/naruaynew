import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, Link } from '@inertiajs/react';

interface Props { order: any; }

const fmt = (v: number) => `₹${Number(v).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

export default function OrderShow({ order }: Props) {
    return (
        <AdminLayout>
            <Head title={`Order ${order.order_number}`} />
            <div className="max-w-4xl mx-auto space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{order.order_number}</h1>
                        <p className="text-white/40 mt-1">{new Date(order.created_at).toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                    </div>
                    <Link href="/admin/orders" className="text-sm text-white/40 hover:text-white">← Back</Link>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <GlassCard padding="sm">
                        <p className="text-xs text-white/30">Status</p>
                        <span className={`mt-1 inline-block ${order.status === 'completed' ? 'badge-success' : order.status === 'pending' ? 'badge-warning' : 'badge-danger'}`}>{order.status}</span>
                    </GlassCard>
                    <GlassCard padding="sm">
                        <p className="text-xs text-white/30">Order Type</p>
                        <p className="text-lg font-bold text-white capitalize mt-1">{order.order_type}</p>
                    </GlassCard>
                    <GlassCard padding="sm">
                        <p className="text-xs text-white/30">Payment Method</p>
                        <p className="text-lg font-bold text-white capitalize mt-1">{order.payment_method?.replace(/_/g, ' ')}</p>
                    </GlassCard>
                </div>

                {order.user && (
                    <GlassCard padding="sm">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-sm font-bold text-white">
                                {order.user.first_name?.[0]}{order.user.last_name?.[0]}
                            </div>
                            <div>
                                <Link href={`/admin/members/${order.user.id}`} className="font-medium text-white hover:text-primary-400 transition">
                                    {order.user.first_name} {order.user.last_name}
                                </Link>
                                <p className="text-xs text-white/30">@{order.user.username} · {order.user.email}</p>
                            </div>
                        </div>
                    </GlassCard>
                )}

                <GlassCard padding="none" hover={false}>
                    <div className="p-4 border-b border-white/5">
                        <h3 className="text-lg font-semibold text-white">Order Items</h3>
                    </div>
                    <table className="table-glass">
                        <thead>
                            <tr><th>Product</th><th>Price</th><th>PV</th><th>Qty</th><th>Total</th><th>Total PV</th></tr>
                        </thead>
                        <tbody>
                            {order.items?.map((item: any) => (
                                <tr key={item.id}>
                                    <td className="text-sm font-medium text-white">{item.product?.name || 'N/A'}</td>
                                    <td className="text-sm text-white/70">{fmt(item.price)}</td>
                                    <td className="text-sm text-primary-400">{item.pv}</td>
                                    <td className="text-sm text-white/50">{item.quantity}</td>
                                    <td className="text-sm text-white font-medium">{fmt(item.total)}</td>
                                    <td className="text-sm text-accent-400">{item.total_pv}</td>
                                </tr>
                            ))}
                        </tbody>
                        <tfoot>
                            <tr className="border-t border-white/10">
                                <td colSpan={4} className="text-right font-semibold text-white px-4 py-3">Total</td>
                                <td className="text-lg font-bold text-white px-4 py-3">{fmt(order.total)}</td>
                                <td className="text-lg font-bold text-accent-400 px-4 py-3">{order.total_pv}</td>
                            </tr>
                        </tfoot>
                    </table>
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
