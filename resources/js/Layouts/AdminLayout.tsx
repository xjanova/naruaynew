import { PropsWithChildren, useState } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import {
    HomeIcon,
    UsersIcon,
    ShareIcon,
    CurrencyDollarIcon,
    ShoppingBagIcon,
    TicketIcon,
    Cog6ToothIcon,
    ChartBarIcon,
    BanknotesIcon,
    TrophyIcon,
    ClipboardDocumentListIcon,
    Bars3Icon,
    XMarkIcon,
    ArrowRightOnRectangleIcon,
    ChevronDownIcon,
} from '@heroicons/react/24/outline';

interface NavItem {
    name: string;
    href: string;
    icon: React.ComponentType<any>;
    active?: string;
}

const navigation: NavItem[] = [
    { name: 'Dashboard', href: '/admin/dashboard', icon: HomeIcon, active: 'admin.dashboard' },
    { name: 'Members', href: '/admin/members', icon: UsersIcon, active: 'admin.members.*' },
    { name: 'Network Tree', href: '/admin/tree', icon: ShareIcon, active: 'admin.tree.*' },
    { name: 'Commissions', href: '/admin/commissions', icon: CurrencyDollarIcon, active: 'admin.commissions.*' },
    { name: 'Payouts', href: '/admin/payouts', icon: BanknotesIcon, active: 'admin.payouts.*' },
    { name: 'Products', href: '/admin/products', icon: ShoppingBagIcon, active: 'admin.products.*' },
    { name: 'Orders', href: '/admin/orders', icon: ClipboardDocumentListIcon, active: 'admin.orders.*' },
    { name: 'E-PINs', href: '/admin/epins', icon: TicketIcon, active: 'admin.epins.*' },
    { name: 'Ranks', href: '/admin/ranks', icon: TrophyIcon, active: 'admin.ranks.*' },
    { name: 'Reports', href: '/admin/commissions/report', icon: ChartBarIcon, active: 'admin.commissions.report' },
    { name: 'Settings', href: '/admin/settings', icon: Cog6ToothIcon, active: 'admin.settings.*' },
];

export default function AdminLayout({ children }: PropsWithChildren) {
    const { auth, flash } = usePage<any>().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const currentUrl = usePage().url;

    const isActive = (href: string) => currentUrl.startsWith(href);

    return (
        <div className="min-h-screen flex">
            {/* Mobile overlay */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* Sidebar */}
            <aside className={`
                fixed inset-y-0 left-0 z-50 w-72 glass border-r border-white/5
                transform transition-transform duration-300 ease-in-out
                lg:translate-x-0 lg:static lg:z-auto
                ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
            `}>
                {/* Logo */}
                <div className="flex items-center justify-between h-16 px-6 border-b border-white/5">
                    <Link href="/admin/dashboard" className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                            <span className="text-lg font-bold text-white">N</span>
                        </div>
                        <div>
                            <h1 className="text-lg font-bold text-white">Naruay</h1>
                            <p className="text-[10px] text-white/40 uppercase tracking-widest">Admin Panel</p>
                        </div>
                    </Link>
                    <button onClick={() => setSidebarOpen(false)} className="lg:hidden text-white/40 hover:text-white">
                        <XMarkIcon className="w-6 h-6" />
                    </button>
                </div>

                {/* Nav */}
                <nav className="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                    {navigation.map((item) => (
                        <Link
                            key={item.name}
                            href={item.href}
                            className={`sidebar-link ${isActive(item.href) ? 'active' : ''}`}
                        >
                            <item.icon className="w-5 h-5 flex-shrink-0" />
                            <span>{item.name}</span>
                        </Link>
                    ))}
                </nav>

                {/* User */}
                <div className="p-4 border-t border-white/5">
                    <div className="flex items-center gap-3 px-4 py-3">
                        <div className="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-xs font-bold">
                            {auth?.user?.first_name?.[0]}{auth?.user?.last_name?.[0]}
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-white truncate">{auth?.user?.first_name} {auth?.user?.last_name}</p>
                            <p className="text-xs text-white/40 truncate">{auth?.user?.email}</p>
                        </div>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="text-white/30 hover:text-red-400 transition"
                        >
                            <ArrowRightOnRectangleIcon className="w-5 h-5" />
                        </Link>
                    </div>
                </div>
            </aside>

            {/* Main content */}
            <div className="flex-1 flex flex-col min-w-0">
                {/* Top bar */}
                <header className="h-16 glass border-b border-white/5 flex items-center justify-between px-6 sticky top-0 z-30">
                    <button
                        onClick={() => setSidebarOpen(true)}
                        className="lg:hidden text-white/60 hover:text-white"
                    >
                        <Bars3Icon className="w-6 h-6" />
                    </button>
                    <div className="flex-1" />
                    <div className="flex items-center gap-4">
                        <span className="text-sm text-white/40">
                            {new Date().toLocaleDateString('th-TH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                        </span>
                    </div>
                </header>

                {/* Flash messages */}
                {flash?.success && (
                    <div className="mx-6 mt-4 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm animate-fade-in">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mx-6 mt-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm animate-fade-in">
                        {flash.error}
                    </div>
                )}

                {/* Page content */}
                <main className="flex-1 p-6">
                    {children}
                </main>
            </div>
        </div>
    );
}
