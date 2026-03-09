import UserLayout from '@/Layouts/UserLayout';
import GlassCard from '@/Components/ui/GlassCard';
import StatCard from '@/Components/ui/StatCard';
import Pagination from '@/Components/ui/Pagination';
import { Head, router, useForm } from '@inertiajs/react';
import { WalletIcon, ArrowsRightLeftIcon } from '@heroicons/react/24/outline';
import { useState } from 'react';

interface Props {
    balance: { balance: number; purchase_wallet: number; commission_balance: number } | null;
    transactions: { data: any[]; links: any[]; from: number; to: number; total: number };
    filters: any;
}

const fmt = (v: number) => `₹${Number(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

export default function WalletIndex({ balance, transactions, filters }: Props) {
    const [showTransfer, setShowTransfer] = useState(false);
    const [showPayout, setShowPayout] = useState(false);

    const transferForm = useForm({ to_username: '', amount: '', transaction_password: '' });
    const payoutForm = useForm({ amount: '', method: 'bank_transfer' });

    const submitTransfer = (e: React.FormEvent) => {
        e.preventDefault();
        transferForm.post('/user/wallet/transfer', { onSuccess: () => { setShowTransfer(false); transferForm.reset(); } });
    };

    const submitPayout = (e: React.FormEvent) => {
        e.preventDefault();
        payoutForm.post('/user/wallet/payout', { onSuccess: () => { setShowPayout(false); payoutForm.reset(); } });
    };

    return (
        <UserLayout>
            <Head title="Wallet" />
            <div className="space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-white">E-Wallet</h1>
                    <div className="flex gap-3">
                        <button onClick={() => setShowTransfer(true)} className="btn-secondary text-sm">Transfer</button>
                        <button onClick={() => setShowPayout(true)} className="btn-primary text-sm">Request Payout</button>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <StatCard title="Main Balance" value={fmt(balance?.balance || 0)} icon={<WalletIcon className="w-6 h-6" />} color="primary" />
                    <StatCard title="Purchase Wallet" value={fmt(balance?.purchase_wallet || 0)} icon={<WalletIcon className="w-6 h-6" />} color="accent" />
                    <StatCard title="Commission Balance" value={fmt(balance?.commission_balance || 0)} icon={<ArrowsRightLeftIcon className="w-6 h-6" />} color="green" />
                </div>

                <GlassCard padding="none" hover={false}>
                    <div className="p-4 border-b border-white/5 flex items-center justify-between">
                        <h3 className="text-lg font-semibold text-white">Transaction History</h3>
                        <select value={filters.type || ''} onChange={e => router.get('/user/wallet', { type: e.target.value || undefined }, { preserveState: true })} className="glass-input text-sm">
                            <option value="" className="bg-surface-900">All Types</option>
                            <option value="credit" className="bg-surface-900">Credits</option>
                            <option value="debit" className="bg-surface-900">Debits</option>
                        </select>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead><tr><th>Description</th><th>Type</th><th>Amount</th><th>Balance</th><th>Date</th></tr></thead>
                            <tbody>
                                {transactions.data.length === 0 ? (
                                    <tr><td colSpan={5} className="text-center py-12 text-white/30">No transactions yet</td></tr>
                                ) : transactions.data.map((t: any) => (
                                    <tr key={t.id}>
                                        <td className="text-sm text-white/70">{t.description}</td>
                                        <td><span className={t.type === 'credit' ? 'badge-success' : 'badge-danger'}>{t.type}</span></td>
                                        <td className={`text-sm font-medium ${t.type === 'credit' ? 'text-green-400' : 'text-red-400'}`}>{t.type === 'credit' ? '+' : '-'}{fmt(t.amount)}</td>
                                        <td className="text-sm text-white/40">{fmt(t.balance_after)}</td>
                                        <td className="text-xs text-white/40">{new Date(t.created_at).toLocaleDateString()}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="px-4 pb-4"><Pagination links={transactions.links} from={transactions.from} to={transactions.to} total={transactions.total} /></div>
                </GlassCard>

                {/* Transfer Modal */}
                {showTransfer && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" onClick={() => setShowTransfer(false)}>
                        <div className="glass-card p-6 w-full max-w-md" onClick={e => e.stopPropagation()}>
                            <h3 className="text-lg font-semibold text-white mb-4">Fund Transfer</h3>
                            <form onSubmit={submitTransfer} className="space-y-4">
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Recipient Username</label>
                                    <input type="text" value={transferForm.data.to_username} onChange={e => transferForm.setData('to_username', e.target.value)} className="glass-input w-full" placeholder="Enter username" />
                                    {transferForm.errors.to_username && <p className="text-red-400 text-xs mt-1">{transferForm.errors.to_username}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Amount</label>
                                    <input type="number" value={transferForm.data.amount} onChange={e => transferForm.setData('amount', e.target.value)} className="glass-input w-full" placeholder="0.00" min="1" step="0.01" />
                                    {transferForm.errors.amount && <p className="text-red-400 text-xs mt-1">{transferForm.errors.amount}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Transaction Password</label>
                                    <input type="password" value={transferForm.data.transaction_password} onChange={e => transferForm.setData('transaction_password', e.target.value)} className="glass-input w-full" />
                                    {transferForm.errors.transaction_password && <p className="text-red-400 text-xs mt-1">{transferForm.errors.transaction_password}</p>}
                                </div>
                                <div className="flex justify-end gap-3 pt-2">
                                    <button type="button" onClick={() => setShowTransfer(false)} className="btn-secondary text-sm">Cancel</button>
                                    <button type="submit" disabled={transferForm.processing} className="btn-primary text-sm">{transferForm.processing ? 'Transferring...' : 'Transfer'}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

                {/* Payout Modal */}
                {showPayout && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" onClick={() => setShowPayout(false)}>
                        <div className="glass-card p-6 w-full max-w-md" onClick={e => e.stopPropagation()}>
                            <h3 className="text-lg font-semibold text-white mb-4">Request Payout</h3>
                            <form onSubmit={submitPayout} className="space-y-4">
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Amount (min ₹100)</label>
                                    <input type="number" value={payoutForm.data.amount} onChange={e => payoutForm.setData('amount', e.target.value)} className="glass-input w-full" placeholder="0.00" min="100" step="0.01" />
                                    {payoutForm.errors.amount && <p className="text-red-400 text-xs mt-1">{payoutForm.errors.amount}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-white/50 mb-1">Payout Method</label>
                                    <select value={payoutForm.data.method} onChange={e => payoutForm.setData('method', e.target.value)} className="glass-input w-full">
                                        <option value="bank_transfer" className="bg-surface-900">Bank Transfer</option>
                                        <option value="upi" className="bg-surface-900">UPI</option>
                                    </select>
                                </div>
                                <div className="flex justify-end gap-3 pt-2">
                                    <button type="button" onClick={() => setShowPayout(false)} className="btn-secondary text-sm">Cancel</button>
                                    <button type="submit" disabled={payoutForm.processing} className="btn-primary text-sm">{payoutForm.processing ? 'Requesting...' : 'Request Payout'}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </UserLayout>
    );
}
