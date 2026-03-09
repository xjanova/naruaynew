import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import GlassCard from '@/Components/ui/GlassCard';
import { Head, router } from '@inertiajs/react';

interface Props {
    settings: Record<string, Array<{ id: number; key: string; value: string; group: string }>>;
    modules: Array<{ id: number; module_name: string; is_active: boolean }>;
}

export default function SettingsIndex({ settings, modules }: Props) {
    const [editedSettings, setEditedSettings] = useState<Record<string, string>>({});

    const handleChange = (key: string, value: string) => {
        setEditedSettings(prev => ({ ...prev, [key]: value }));
    };

    const handleSave = () => {
        const items = Object.entries(editedSettings).map(([key, value]) => ({ key, value }));
        if (items.length === 0) return;
        router.post('/admin/settings', { settings: items }, {
            onSuccess: () => setEditedSettings({}),
        });
    };

    return (
        <AdminLayout>
            <Head title="Settings" />
            <div className="space-y-6 animate-fade-in">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-white">Settings</h1>
                    {Object.keys(editedSettings).length > 0 && (
                        <button onClick={handleSave} className="btn-primary text-sm">
                            Save Changes ({Object.keys(editedSettings).length})
                        </button>
                    )}
                </div>

                {/* Modules */}
                <GlassCard>
                    <h3 className="text-lg font-semibold text-white mb-4">Modules</h3>
                    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        {modules.map(m => (
                            <button
                                key={m.id}
                                onClick={() => router.post(`/admin/settings/modules/${m.id}/toggle`)}
                                className={`flex items-center justify-between px-4 py-3 rounded-xl border transition-all ${
                                    m.is_active
                                        ? 'border-green-500/20 bg-green-500/5 hover:border-green-500/40'
                                        : 'border-white/10 bg-white/[0.02] hover:border-white/20'
                                }`}
                            >
                                <span className="text-sm text-white/70 capitalize">{m.module_name.replace(/_/g, ' ')}</span>
                                <span className={`w-2.5 h-2.5 rounded-full ${m.is_active ? 'bg-green-400' : 'bg-gray-500'}`} />
                            </button>
                        ))}
                    </div>
                </GlassCard>

                {/* Settings by Group */}
                {Object.entries(settings).map(([group, items]) => (
                    <GlassCard key={group}>
                        <h3 className="text-lg font-semibold text-white mb-4 capitalize">{group.replace(/_/g, ' ')}</h3>
                        <div className="space-y-4">
                            {items.map(s => (
                                <div key={s.id} className="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label className="text-sm text-white/50 sm:w-1/3 capitalize">{s.key.replace(/_/g, ' ')}</label>
                                    <input
                                        type="text"
                                        value={editedSettings[s.key] ?? s.value}
                                        onChange={e => handleChange(s.key, e.target.value)}
                                        className="glass-input flex-1 text-sm"
                                    />
                                </div>
                            ))}
                        </div>
                    </GlassCard>
                ))}
            </div>
        </AdminLayout>
    );
}
