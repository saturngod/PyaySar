import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { MoreHorizontal } from 'lucide-react';

interface Invoice {
    id: number;
    total: string;
    status: 'Draft' | 'Sent' | 'Reject' | 'Received';
    open_date: string;
    customer?: {
        name: string;
    };
}

interface ColumnsProps {
    onEdit: (invoice: Invoice) => void;
    onDelete: (invoice: Invoice) => void;
}

const getStatusColor = (status: string) => {
    switch (status) {
        case 'Received':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'Sent':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'Reject':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
    }
};

export const getColumns = ({ onEdit, onDelete }: ColumnsProps) => [
    {
        accessorKey: 'id',
        header: 'Invoice #',
        cell: ({ row }: { row: { original: Invoice } }) => (
            <span className="font-medium">INV-{row.original.id}</span>
        ),
    },
    {
        accessorKey: 'customer.name',
        header: 'Customer',
        cell: ({ row }: { row: { original: Invoice } }) => (
             <span>{row.original.customer?.name || 'N/A'}</span>
        ),
    },
    {
        accessorKey: 'open_date',
        header: 'Date',
        cell: ({ row }: { row: { original: Invoice } }) => (
             <span>{new Date(row.original.open_date).toLocaleDateString()}</span>
        ),
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }: { row: { original: Invoice } }) => (
            <Badge className={getStatusColor(row.original.status)} variant="outline">
                {row.original.status}
            </Badge>
        ),
    },
    {
        accessorKey: 'total',
        header: 'Amount',
        cell: ({ row }: { row: { original: Invoice } }) => (
             <span>{row.original.total}</span>
        ),
    },
    {
        id: 'actions',
        cell: ({ row }: { row: { original: Invoice } }) => {
            const invoice = row.original;

            return (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="h-8 w-8 p-0">
                            <span className="sr-only">Open menu</span>
                            <MoreHorizontal className="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                        <DropdownMenuItem onClick={() => onEdit(invoice)}>
                            Edit
                        </DropdownMenuItem>
                         {/* View, Download PDF etc. can be added here */}
                        <DropdownMenuItem
                            onClick={() => onDelete(invoice)}
                            className="text-red-600 focus:text-red-600"
                        >
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            );
        },
    },
];
