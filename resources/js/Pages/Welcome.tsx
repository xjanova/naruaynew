import { Head, Link } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Welcome to Naruay" />
            <div className="min-h-screen flex flex-col">
                {/* Hero */}
                <div className="flex-1 flex items-center justify-center relative overflow-hidden">
                    {/* Animated background orbs */}
                    <div className="absolute top-20 left-20 w-72 h-72 bg-primary-500/10 rounded-full blur-3xl animate-pulse-slow" />
                    <div className="absolute bottom-20 right-20 w-96 h-96 bg-accent-500/8 rounded-full blur-3xl animate-pulse-slow" style={{ animationDelay: '2s' }} />
                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary-600/5 rounded-full blur-3xl" />

                    <div className="relative z-10 text-center px-6 max-w-4xl mx-auto animate-slide-up">
                        {/* Logo */}
                        <div className="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 mb-8 shadow-neon">
                            <span className="text-3xl font-bold text-white">N</span>
                        </div>

                        <h1 className="text-5xl md:text-7xl font-bold text-white mb-6 tracking-tight">
                            Naruay <span className="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-accent-400">MLM</span>
                        </h1>

                        <p className="text-xl md:text-2xl text-white/40 mb-12 max-w-2xl mx-auto leading-relaxed">
                            Next-generation network marketing platform. Build your team, track commissions, and grow your business.
                        </p>

                        <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <Link
                                href="/login"
                                className="btn-primary text-lg px-10 py-3.5 w-full sm:w-auto text-center"
                            >
                                Sign In
                            </Link>
                            <Link
                                href="/register"
                                className="btn-secondary text-lg px-10 py-3.5 w-full sm:w-auto text-center"
                            >
                                Get Started
                            </Link>
                        </div>

                        {/* Feature highlights */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-20">
                            {[
                                { title: 'Binary Tree', desc: 'Advanced binary placement with real-time visualization' },
                                { title: '10+ Commission Types', desc: 'Level, matching, binary, sales, performance & more' },
                                { title: 'Instant Payouts', desc: 'Real-time commission calculations with secure transfers' },
                            ].map((f, i) => (
                                <div key={i} className="glass-card p-6 text-center">
                                    <h3 className="text-lg font-semibold text-white mb-2">{f.title}</h3>
                                    <p className="text-sm text-white/40">{f.desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <footer className="py-6 text-center text-white/20 text-sm border-t border-white/5">
                    © {new Date().getFullYear()} Naruay MLM Platform. All rights reserved.
                </footer>
            </div>
        </>
    );
}
