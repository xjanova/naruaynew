import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, Link, useForm } from '@inertiajs/react';

interface Props {
    product: any;
    categories: Array<{ id: number; name: string }>;
}

export default function ProductEdit({ product, categories }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: product.name || '',
        category_id: product.category_id?.toString() || '',
        sku: product.sku || '',
        price: product.price?.toString() || '',
        pv: product.pv?.toString() || '',
        cv: product.cv?.toString() || '',
        sp: product.sp?.toString() || '',
        description: product.description || '',
        stock: product.stock?.toString() || '0',
        is_active: product.is_active ?? true,
        is_registration_package: product.is_registration_package ?? false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/products/${product.id}`);
    };

    return (
        <AdminLayout>
            <Head title={`Edit - ${product.name}`} />
            <div className="max-w-2xl mx-auto space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-white">Edit Product</h1>
                    <Link href="/admin/products" className="text-sm text-white/40 hover:text-white">← Back</Link>
                </div>

                <GlassCard>
                    <form onSubmit={submit} className="space-y-5">
                        <div>
                            <label className="block text-sm text-white/50 mb-1.5">Product Name *</label>
                            <input type="text" value={data.name} onChange={e => setData('name', e.target.value)} className="glass-input w-full" />
                            {errors.name && <p className="text-red-400 text-xs mt-1">{errors.name}</p>}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm text-white/50 mb-1.5">Category *</label>
                                <select value={data.category_id} onChange={e => setData('category_id', e.target.value)} className="glass-input w-full">
                                    {categories.map(c => <option key={c.id} value={c.id} className="bg-surface-900">{c.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm text-white/50 mb-1.5">SKU</label>
                                <input type="text" value={data.sku} onChange={e => setData('sku', e.target.value)} className="glass-input w-full" />
                                {errors.sku && <p className="text-red-400 text-xs mt-1">{errors.sku}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label className="block text-sm text-white/50 mb-1.5">Price (₹) *</label>
                                <input type="number" value={data.price} onChange={e => setData('price', e.target.value)} className="glass-input w-full" step="0.01" min="0" />
                                {errors.price && <p className="text-red-400 text-xs mt-1">{errors.price}</p>}
                            </div>
                            <div>
                                <label className="block text-sm text-white/50 mb-1.5">PV *</label>
                                <input type="number" value={data.pv} onChange={e => setData('pv', e.target.value)} className="glass-input w-full" step="0.01" min="0" />
                            </div>
                            <div>
                                <label className="block text-sm text-white/50 mb-1.5">CV</label>
                                <input type="number" value={data.cv} onChange={e => setData('cv', e.target.value)} className="glass-input w-full" step="0.01" min="0" />
                            </div>
                            <div>
                                <label className="block text-sm text-white/50 mb-1.5">SP</label>
                                <input type="number" value={data.sp} onChange={e => setData('sp', e.target.value)} className="glass-input w-full" step="0.01" min="0" />
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm text-white/50 mb-1.5">Stock</label>
                            <input type="number" value={data.stock} onChange={e => setData('stock', e.target.value)} className="glass-input w-full" min="0" />
                        </div>

                        <div>
                            <label className="block text-sm text-white/50 mb-1.5">Description</label>
                            <textarea value={data.description} onChange={e => setData('description', e.target.value)} className="glass-input w-full h-24 resize-none" />
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" checked={data.is_active} onChange={e => setData('is_active', e.target.checked)} className="rounded bg-white/5 border-white/20 text-primary-500 focus:ring-primary-500" />
                                <span className="text-sm text-white/70">Active</span>
                            </label>
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" checked={data.is_registration_package} onChange={e => setData('is_registration_package', e.target.checked)} className="rounded bg-white/5 border-white/20 text-accent-500 focus:ring-accent-500" />
                                <span className="text-sm text-white/70">Registration Package</span>
                            </label>
                        </div>

                        <div className="flex justify-end gap-3 pt-4 border-t border-white/5">
                            <Link href="/admin/products" className="btn-secondary text-sm">Cancel</Link>
                            <button type="submit" disabled={processing} className="btn-primary text-sm">{processing ? 'Saving...' : 'Save Changes'}</button>
                        </div>
                    </form>
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
