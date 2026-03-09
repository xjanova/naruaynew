import UserLayout from '@/Layouts/UserLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Pagination from '@/Components/ui/Pagination';
import { Head, router } from '@inertiajs/react';
import { ShoppingCartIcon } from '@heroicons/react/24/outline';

interface Props {
    products: { data: any[]; links: any[]; from: number; to: number; total: number };
    categories: Array<{ id: number; name: string; products_count: number }>;
    filters: any;
}

export default function ShopIndex({ products, categories, filters }: Props) {
    return (
        <UserLayout>
            <Head title="Shop" />
            <div className="space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-white">Shop</h1>
                    <a href="/user/shop/cart" className="btn-secondary text-sm flex items-center gap-2"><ShoppingCartIcon className="w-4 h-4" /> Cart</a>
                </div>

                {/* Categories */}
                <div className="flex gap-2 overflow-x-auto pb-2">
                    <button onClick={() => router.get('/user/shop', {}, { preserveState: true })} className={`px-4 py-1.5 rounded-full text-sm whitespace-nowrap transition ${!filters.category ? 'bg-primary-500/20 text-primary-400 border border-primary-500/30' : 'text-white/40 hover:text-white/70 border border-white/10'}`}>All</button>
                    {categories.map(c => (
                        <button key={c.id} onClick={() => router.get('/user/shop', { category: c.id }, { preserveState: true })} className={`px-4 py-1.5 rounded-full text-sm whitespace-nowrap transition ${Number(filters.category) === c.id ? 'bg-primary-500/20 text-primary-400 border border-primary-500/30' : 'text-white/40 hover:text-white/70 border border-white/10'}`}>
                            {c.name} ({c.products_count})
                        </button>
                    ))}
                </div>

                {/* Products Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    {products.data.map((p: any) => (
                        <GlassCard key={p.id} padding="none">
                            <div className="aspect-square bg-gradient-to-br from-primary-500/10 to-accent-500/5 rounded-t-2xl flex items-center justify-center">
                                <ShoppingCartIcon className="w-16 h-16 text-white/10" />
                            </div>
                            <div className="p-4">
                                <p className="text-xs text-white/30 mb-1">{p.category?.name}</p>
                                <h3 className="font-semibold text-white">{p.name}</h3>
                                <div className="flex items-center justify-between mt-3">
                                    <div>
                                        <p className="text-lg font-bold text-white">₹{Number(p.price).toLocaleString()}</p>
                                        <p className="text-xs text-primary-400">PV: {p.pv}</p>
                                    </div>
                                    <button onClick={() => router.post('/user/shop/cart/add', { product_id: p.id, quantity: 1 })} className="btn-primary text-xs !px-4 !py-2">Add to Cart</button>
                                </div>
                            </div>
                        </GlassCard>
                    ))}
                </div>

                <Pagination links={products.links} from={products.from} to={products.to} total={products.total} />
            </div>
        </UserLayout>
    );
}
