import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Pagination from '@/Components/ui/Pagination';
import { Head, Link, router } from '@inertiajs/react';

interface Props {
    products: { data: any[]; links: any[]; from: number; to: number; total: number };
    categories: any[];
    filters: any;
}

export default function ProductsIndex({ products, categories, filters }: Props) {
    return (
        <AdminLayout>
            <Head title="Products" />
            <div className="space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-white">Products</h1>
                    <Link href="/admin/products/create" className="btn-primary text-sm">+ Add Product</Link>
                </div>
                <GlassCard padding="none" hover={false}>
                    <div className="overflow-x-auto">
                        <table className="table-glass">
                            <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>PV</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                {products.data.map((p: any) => (
                                    <tr key={p.id}>
                                        <td>
                                            <div>
                                                <p className="text-sm font-medium text-white">{p.name}</p>
                                                <p className="text-xs text-white/30">{p.sku || '—'}</p>
                                            </div>
                                        </td>
                                        <td className="text-sm text-white/50">{p.category?.name || '—'}</td>
                                        <td className="text-sm text-white font-medium">₹{Number(p.price).toLocaleString()}</td>
                                        <td className="text-sm text-primary-400">{p.pv}</td>
                                        <td className="text-sm text-white/50">{p.stock ?? '∞'}</td>
                                        <td>{p.is_active ? <span className="badge-success">Active</span> : <span className="badge-danger">Inactive</span>}</td>
                                        <td>
                                            <Link href={`/admin/products/${p.id}/edit`} className="text-xs text-primary-400 hover:text-primary-300">Edit</Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="px-4 pb-4"><Pagination links={products.links} from={products.from} to={products.to} total={products.total} /></div>
                </GlassCard>
            </div>
        </AdminLayout>
    );
}
