import { ReactNode } from 'react';

interface Column<T> {
    key: string;
    label: string;
    render?: (item: T) => ReactNode;
    className?: string;
}

interface DataTableProps<T> {
    columns: Column<T>[];
    data: T[];
    keyField?: string;
    emptyMessage?: string;
}

export default function DataTable<T extends Record<string, any>>({
    columns,
    data,
    keyField = 'id',
    emptyMessage = 'No data available.',
}: DataTableProps<T>) {
    return (
        <div className="overflow-x-auto">
            <table className="table-glass">
                <thead>
                    <tr>
                        {columns.map((col) => (
                            <th key={col.key} className={col.className}>{col.label}</th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {data.length === 0 ? (
                        <tr>
                            <td colSpan={columns.length} className="text-center py-12 text-white/30">
                                {emptyMessage}
                            </td>
                        </tr>
                    ) : (
                        data.map((item) => (
                            <tr key={item[keyField]}>
                                {columns.map((col) => (
                                    <td key={col.key} className={col.className}>
                                        {col.render ? col.render(item) : item[col.key]}
                                    </td>
                                ))}
                            </tr>
                        ))
                    )}
                </tbody>
            </table>
        </div>
    );
}
