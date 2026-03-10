import { Head, useForm, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

interface Props {
    status: {
        database: boolean;
        migrations: boolean;
        seeded: boolean;
    };
    flash?: { success?: string; error?: string };
}

export default function SetupIndex({ status, flash }: Props) {
    const [step, setStep] = useState(
        !status.database ? 0 : !status.migrations ? 1 : !status.seeded ? 2 : 3
    );
    const [migrating, setMigrating] = useState(false);
    const [seeding, setSeeding] = useState(false);

    const adminForm = useForm({
        username: 'admin',
        email: '',
        password: '',
        password_confirmation: '',
        first_name: '',
        last_name: '',
        company_name: 'Naruay MLM',
    });

    const runMigrations = () => {
        setMigrating(true);
        router.post('/setup/migrate', {}, {
            onSuccess: () => { setMigrating(false); setStep(2); },
            onError: () => setMigrating(false),
        });
    };

    const runSeeders = () => {
        setSeeding(true);
        router.post('/setup/seed', {}, {
            onSuccess: () => { setSeeding(false); setStep(3); },
            onError: () => setSeeding(false),
        });
    };

    const createAdmin = (e: FormEvent) => {
        e.preventDefault();
        adminForm.post('/setup/admin');
    };

    const steps = [
        { label: 'Database', icon: '1' },
        { label: 'Migrate', icon: '2' },
        { label: 'Seed', icon: '3' },
        { label: 'Admin', icon: '4' },
    ];

    return (
        <>
            <Head title="Setup - Naruay MLM" />
            <div className="min-h-screen bg-gradient-to-br from-[#0a0a1a] via-[#0f0f2e] to-[#1a0a2e] flex items-center justify-center p-4">
                {/* Background effects */}
                <div className="fixed inset-0 overflow-hidden pointer-events-none">
                    <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse" />
                    <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl animate-pulse" style={{ animationDelay: '1s' }} />
                </div>

                <div className="relative w-full max-w-2xl">
                    {/* Logo / Header */}
                    <div className="text-center mb-8">
                        <div className="inline-flex items-center gap-2 mb-3">
                            <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-cyan-400 flex items-center justify-center">
                                <span className="text-white font-bold text-lg">X</span>
                            </div>
                            <span className="text-white/30 text-sm tracking-[0.3em] uppercase">XMAN Studio</span>
                        </div>
                        <h1 className="text-3xl font-bold text-white mb-2">Naruay MLM Platform</h1>
                        <p className="text-white/40">Initial Setup Wizard</p>
                    </div>

                    {/* Step Indicators */}
                    <div className="flex items-center justify-center gap-2 mb-8">
                        {steps.map((s, i) => (
                            <div key={i} className="flex items-center gap-2">
                                <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 ${
                                    i < step ? 'bg-green-500/20 text-green-400 ring-2 ring-green-500/30' :
                                    i === step ? 'bg-purple-500/20 text-purple-400 ring-2 ring-purple-500/50 scale-110' :
                                    'bg-white/5 text-white/30'
                                }`}>
                                    {i < step ? '\u2713' : s.icon}
                                </div>
                                {i < steps.length - 1 && (
                                    <div className={`w-8 h-0.5 ${i < step ? 'bg-green-500/30' : 'bg-white/10'}`} />
                                )}
                            </div>
                        ))}
                    </div>

                    {/* Flash Messages */}
                    {flash?.success && (
                        <div className="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                            {flash.error}
                        </div>
                    )}

                    {/* Card */}
                    <div className="backdrop-blur-xl bg-white/5 border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
                        {/* Step 0: Database Check */}
                        {step === 0 && (
                            <div className="p-8">
                                <h2 className="text-xl font-semibold text-white mb-2">Database Connection</h2>
                                <p className="text-white/40 text-sm mb-6">Check your database connection before continuing.</p>

                                <div className="p-4 rounded-xl bg-red-500/10 border border-red-500/20 mb-6">
                                    <p className="text-red-400 font-medium">Database not connected</p>
                                    <p className="text-red-400/60 text-sm mt-1">
                                        Please configure your <code className="px-1 py-0.5 bg-red-500/10 rounded">.env</code> file with correct database credentials and refresh this page.
                                    </p>
                                </div>

                                <div className="bg-white/5 rounded-lg p-4 font-mono text-sm text-white/50">
                                    <div>DB_CONNECTION=mysql</div>
                                    <div>DB_HOST=127.0.0.1</div>
                                    <div>DB_DATABASE=naruaynew</div>
                                    <div>DB_USERNAME=your_user</div>
                                    <div>DB_PASSWORD=your_pass</div>
                                </div>

                                <button
                                    onClick={() => window.location.reload()}
                                    className="mt-6 w-full py-3 rounded-xl bg-purple-500/20 text-purple-400 hover:bg-purple-500/30 transition font-medium"
                                >
                                    Refresh & Check Again
                                </button>
                            </div>
                        )}

                        {/* Step 1: Run Migrations */}
                        {step === 1 && (
                            <div className="p-8">
                                <h2 className="text-xl font-semibold text-white mb-2">Database Migration</h2>
                                <p className="text-white/40 text-sm mb-6">Create all required database tables (52 migrations).</p>

                                <div className="p-4 rounded-xl bg-green-500/10 border border-green-500/20 mb-6">
                                    <p className="text-green-400 font-medium">Database connected</p>
                                </div>

                                <button
                                    onClick={runMigrations}
                                    disabled={migrating}
                                    className="w-full py-3 rounded-xl bg-gradient-to-r from-purple-600 to-cyan-600 text-white font-medium hover:from-purple-500 hover:to-cyan-500 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {migrating ? (
                                        <span className="flex items-center justify-center gap-2">
                                            <svg className="animate-spin w-5 h-5" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none"/><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            Running Migrations...
                                        </span>
                                    ) : 'Run Migrations'}
                                </button>
                            </div>
                        )}

                        {/* Step 2: Seed Data */}
                        {step === 2 && (
                            <div className="p-8">
                                <h2 className="text-xl font-semibold text-white mb-2">Seed Default Data</h2>
                                <p className="text-white/40 text-sm mb-6">Populate ranks, settings, products, and commission configs.</p>

                                <div className="grid grid-cols-2 gap-3 mb-6 text-sm">
                                    {['12 Ranks', '27 Settings', '9 Products', '12 Commission Types', '10 Level Configs', '4 Payment Gateways', '15 Countries', '3 Currencies'].map((item) => (
                                        <div key={item} className="p-3 rounded-lg bg-white/5 text-white/60 flex items-center gap-2">
                                            <span className="w-2 h-2 rounded-full bg-cyan-400/60" />
                                            {item}
                                        </div>
                                    ))}
                                </div>

                                <div className="flex gap-3">
                                    <button
                                        onClick={() => setStep(3)}
                                        className="flex-1 py-3 rounded-xl bg-white/5 text-white/40 hover:bg-white/10 transition font-medium"
                                    >
                                        Skip
                                    </button>
                                    <button
                                        onClick={runSeeders}
                                        disabled={seeding}
                                        className="flex-1 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-cyan-600 text-white font-medium hover:from-purple-500 hover:to-cyan-500 transition disabled:opacity-50"
                                    >
                                        {seeding ? 'Seeding...' : 'Seed Database'}
                                    </button>
                                </div>
                            </div>
                        )}

                        {/* Step 3: Create Admin */}
                        {step === 3 && (
                            <form onSubmit={createAdmin} className="p-8">
                                <h2 className="text-xl font-semibold text-white mb-2">Create Admin Account</h2>
                                <p className="text-white/40 text-sm mb-6">Set up the platform administrator.</p>

                                <div className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-white/50 text-sm mb-1">First Name</label>
                                            <input
                                                type="text"
                                                value={adminForm.data.first_name}
                                                onChange={e => adminForm.setData('first_name', e.target.value)}
                                                className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 focus:border-purple-500/50 focus:outline-none focus:ring-1 focus:ring-purple-500/25"
                                                placeholder="Admin"
                                                required
                                            />
                                            {adminForm.errors.first_name && <p className="text-red-400 text-xs mt-1">{adminForm.errors.first_name}</p>}
                                        </div>
                                        <div>
                                            <label className="block text-white/50 text-sm mb-1">Last Name</label>
                                            <input
                                                type="text"
                                                value={adminForm.data.last_name}
                                                onChange={e => adminForm.setData('last_name', e.target.value)}
                                                className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 focus:border-purple-500/50 focus:outline-none focus:ring-1 focus:ring-purple-500/25"
                                                placeholder="System"
                                                required
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-white/50 text-sm mb-1">Username</label>
                                        <input
                                            type="text"
                                            value={adminForm.data.username}
                                            onChange={e => adminForm.setData('username', e.target.value)}
                                            className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 focus:border-purple-500/50 focus:outline-none focus:ring-1 focus:ring-purple-500/25"
                                            placeholder="admin"
                                            required
                                        />
                                        {adminForm.errors.username && <p className="text-red-400 text-xs mt-1">{adminForm.errors.username}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-white/50 text-sm mb-1">Email</label>
                                        <input
                                            type="email"
                                            value={adminForm.data.email}
                                            onChange={e => adminForm.setData('email', e.target.value)}
                                            className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 focus:border-purple-500/50 focus:outline-none focus:ring-1 focus:ring-purple-500/25"
                                            placeholder="admin@yourdomain.com"
                                            required
                                        />
                                        {adminForm.errors.email && <p className="text-red-400 text-xs mt-1">{adminForm.errors.email}</p>}
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-white/50 text-sm mb-1">Password</label>
                                            <input
                                                type="password"
                                                value={adminForm.data.password}
                                                onChange={e => adminForm.setData('password', e.target.value)}
                                                className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 focus:border-purple-500/50 focus:outline-none focus:ring-1 focus:ring-purple-500/25"
                                                placeholder="Min 8 characters"
                                                required
                                                minLength={8}
                                            />
                                            {adminForm.errors.password && <p className="text-red-400 text-xs mt-1">{adminForm.errors.password}</p>}
                                        </div>
                                        <div>
                                            <label className="block text-white/50 text-sm mb-1">Confirm Password</label>
                                            <input
                                                type="password"
                                                value={adminForm.data.password_confirmation}
                                                onChange={e => adminForm.setData('password_confirmation', e.target.value)}
                                                className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 focus:border-purple-500/50 focus:outline-none focus:ring-1 focus:ring-purple-500/25"
                                                placeholder="Confirm"
                                                required
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-white/50 text-sm mb-1">Company Name</label>
                                        <input
                                            type="text"
                                            value={adminForm.data.company_name}
                                            onChange={e => adminForm.setData('company_name', e.target.value)}
                                            className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 focus:border-purple-500/50 focus:outline-none focus:ring-1 focus:ring-purple-500/25"
                                            placeholder="Your MLM Company"
                                        />
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    disabled={adminForm.processing}
                                    className="mt-6 w-full py-3 rounded-xl bg-gradient-to-r from-purple-600 to-cyan-600 text-white font-medium hover:from-purple-500 hover:to-cyan-500 transition disabled:opacity-50"
                                >
                                    {adminForm.processing ? 'Creating...' : 'Create Admin & Launch'}
                                </button>
                            </form>
                        )}
                    </div>

                    <p className="text-center text-white/20 text-xs mt-6">
                        Powered by XMAN Studio
                    </p>
                </div>
            </div>
        </>
    );
}
