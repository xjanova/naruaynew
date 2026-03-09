import UserLayout from '@/Layouts/UserLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, useForm } from '@inertiajs/react';

interface Props {
    cartItems: Array<{ id: number; quantity: number; product: { id: number; name: string; price: number; pv: number } }>;
    balance: number;
    purchaseWallet: number;
}

const fmt = (v: number) => `₹${Number(v).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

export default function Cart({ cartItems, balance, purchaseWallet }: Props) {
    const total = cartItems.reduce((s, i) => s + i.product.price * i.quantity, 0);
    const totalPV = cartItems.reduce((s, i) => s + i.product.pv * i.quantity, 0);

    const { data, setData, post, processing } = useForm({ payment_method: 'ewallet' });

    const checkout = (e: React.FormEvent) => {
        e.preventDefault();
        post('/user/shop/checkout');
    };

    return (
        <UserLayout>
            <Head title="Cart" />
            <div className="space-y-6 animate-fade-in">
                <h1 className="text-2xl font-bold text-white">Shopping Cart</h1>

                {cartItems.length === 0 ? (
                    <GlassCard hover={false}>
                        <div className="text-center py-16">
                            <p className="text-white/30 text-lg">Your cart is empty</p>
                            <a href="/user/shop" className="btn-primary text-sm mt-4 inline-block">Browse Products</a>
                        </div>
                    </GlassCard>
                ) : (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div className="lg:col-span-2">
                            <GlassCard padding="none" hover={false}>
                                <table className="table-glass">
                                    <thead><tr><th>Product</th><th>Price</th><th>PV</th><th>Qty</th><th>Total</th></tr></thead>
                                    <tbody>
                                        {cartItems.map(item => (
                                            <tr key={item.id}>
                                                <td className="text-sm font-medium text-white">{item.product.name}</td>
                                                <td className="text-sm text-white/70">{fmt(item.product.price)}</td>
                                                <td className="text-sm text-primary-400">{item.product.pv}</td>
                                                <td className="text-sm text-white/50">{item.quantity}</td>
                                                <td className="text-sm text-white font-medium">{fmt(item.product.price * item.quantity)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </GlassCard>
                        </div>

                        <div>
                            <GlassCard hover={false}>
                                <h3 className="text-lg font-semibold text-white mb-4">Order Summary</h3>
                                <div className="space-y-3 text-sm">
                                    <div className="flex justify-between"><span className="text-white/40">Total Items</span><span className="text-white">{cartItems.length}</span></div>
                                    <div className="flex justify-between"><span className="text-white/40">Total PV</span><span className="text-primary-400 font-medium">{totalPV}</span></div>
                                    <div className="border-t border-white/5 pt-3 flex justify-between"><span className="text-white font-semibold">Total</span><span className="text-white text-lg font-bold">{fmt(total)}</span></div>
                                </div>

                                <form onSubmit={checkout} className="mt-6 space-y-4">
                                    <div>
                                        <label className="block text-sm text-white/50 mb-1.5">Payment Method</label>
                                        <select value={data.payment_method} onChange={e => setData('payment_method', e.target.value)} className="glass-input w-full text-sm">
                                            <option value="ewallet" className="bg-surface-900">E-Wallet ({fmt(balance)})</option>
                                            <option value="purchase_wallet" className="bg-surface-900">Purchase Wallet ({fmt(purchaseWallet)})</option>
                                        </select>
                                    </div>
                                    <button type="submit" disabled={processing} className="btn-primary w-full">{processing ? 'Processing...' : 'Place Order'}</button>
                                </form>
                            </GlassCard>
                        </div>
                    </div>
                )}
            </div>
        </UserLayout>
    );
}
