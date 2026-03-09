import { ReactNode } from 'react';
import { clsx } from 'clsx';

interface StatCardProps {
    title: string;
    value: string | number;
    subtitle?: string;
    icon: ReactNode;
    color?: 'primary' | 'green' | 'accent' | 'amber';
}

const colorMap = {
    primary: {
        iconBg: 'bg-primary-500/15',
        iconText: 'text-primary-400',
    },
    green: {
        iconBg: 'bg-green-500/15',
        iconText: 'text-green-400',
    },
    accent: {
        iconBg: 'bg-accent-500/15',
        iconText: 'text-accent-400',
    },
    amber: {
        iconBg: 'bg-amber-500/15',
        iconText: 'text-amber-400',
    },
};

export default function StatCard({ title, value, subtitle, icon, color = 'primary' }: StatCardProps) {
    const colors = colorMap[color];

    return (
        <div className="stat-card">
            <div className="flex items-start justify-between">
                <div className="flex-1 min-w-0">
                    <p className="text-sm text-white/50 font-medium">{title}</p>
                    <p className="text-2xl font-bold text-white mt-1 truncate">{value}</p>
                    {subtitle && (
                        <p className="text-xs text-white/30 mt-1">{subtitle}</p>
                    )}
                </div>
                <div className={clsx('w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0', colors.iconBg, colors.iconText)}>
                    {icon}
                </div>
            </div>
        </div>
    );
}
