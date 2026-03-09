import { Link } from '@inertiajs/react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationProps {
    links: PaginationLink[];
    from?: number;
    to?: number;
    total?: number;
}

export default function Pagination({ links, from, to, total }: PaginationProps) {
    if (links.length <= 3) return null;

    return (
        <div className="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
            {from && to && total && (
                <p className="text-sm text-white/40">
                    Showing <span className="text-white/70">{from}</span> to{' '}
                    <span className="text-white/70">{to}</span> of{' '}
                    <span className="text-white/70">{total}</span> results
                </p>
            )}
            <div className="flex items-center gap-1">
                {links.map((link, i) => (
                    <div key={i}>
                        {link.url ? (
                            <Link
                                href={link.url}
                                className={`px-3 py-1.5 rounded-lg text-sm transition-all ${
                                    link.active
                                        ? 'bg-primary-500/20 text-primary-400 border border-primary-500/30'
                                        : 'text-white/40 hover:text-white/70 hover:bg-white/5'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ) : (
                            <span
                                className="px-3 py-1.5 rounded-lg text-sm text-white/20"
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}
