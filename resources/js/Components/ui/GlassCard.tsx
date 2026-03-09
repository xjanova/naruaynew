import { ReactNode } from 'react';
import { clsx } from 'clsx';

interface GlassCardProps {
    children: ReactNode;
    className?: string;
    hover?: boolean;
    padding?: 'none' | 'sm' | 'md' | 'lg';
}

export default function GlassCard({ children, className, hover = true, padding = 'md' }: GlassCardProps) {
    const paddings = { none: '', sm: 'p-4', md: 'p-6', lg: 'p-8' };

    return (
        <div className={clsx(
            hover ? 'glass-card' : 'glass-card hover:transform-none hover:shadow-glass hover:bg-white/[0.03] hover:border-white/[0.08]',
            paddings[padding],
            className
        )}>
            {children}
        </div>
    );
}
