import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { MoreHorizontal, History } from 'lucide-react';
import { InvoiceFilters } from './components/invoice-filters';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useState } from 'react';
import { StatusHistoryDialog } from './components/status-history-dialog';

interface Invoice {
    id: number;
    total: string;
    status: 'Draft' | 'Sent' | 'Reject' | 'Received';
    open_date: string;
    customer?: {
        name: string;
    };
}

interface IndexProps {
    invoices: Invoice[];
    filters: {
        status?: string;
        date_from?: string;
        date_to?: string;
        customer_id?: string;
    };
    customers: { id: number; name: string }[];
}


const getStatusColor = (status: string) => {
    switch (status) {
        case 'Received':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border-green-200';
        case 'Sent':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200';
        case 'Reject':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border-red-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400 border-gray-200';
    }
};

export default function Index({ invoices, filters, customers }: IndexProps) {
    const [historyOpen, setHistoryOpen] = useState(false);
    const [selectedInvoiceId, setSelectedInvoiceId] = useState<number | null>(null);

    const handleStatusChange = (invoiceId: number, newStatus: string) => {
        router.put(`/invoices/${invoiceId}/status`, {
            status: newStatus,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: Toast notification handled by backend redirect with 'success'
            }
        });
    };

    const  openHistory = (invoiceId: number) => {
        setSelectedInvoiceId(invoiceId);
        setHistoryOpen(true);
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Invoices', href: '/invoices' }]}>
            <Head title="Invoices" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Invoices</h1>
                    <Link href="/invoices/create">
                        <Button>Create Invoice</Button>
                    </Link>
                </div>

                <InvoiceFilters filters={filters} customers={customers} />

                <div className="rounded-md border bg-card">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Invoice #</TableHead>
                                <TableHead>Customer</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">Amount</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {invoices.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={6} className="h-24 text-center">
                                        No invoices found.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                invoices.map((invoice) => (
                                    <TableRow 
                                        key={invoice.id} 
                                        className="cursor-pointer hover:bg-muted/50"
                                        onClick={() => router.visit(`/invoices/${invoice.id}`)}
                                    >
                                        <TableCell className="font-medium">
                                            INV-{invoice.id}
                                        </TableCell>
                                        <TableCell>
                                            {invoice.customer?.name || 'N/A'}
                                        </TableCell>
                                        <TableCell>
                                            {new Date(invoice.open_date).toLocaleDateString()}
                                        </TableCell>
                                        <TableCell onClick={(e) => e.stopPropagation()}>
                                            <Select
                                                value={invoice.status}
                                                onValueChange={(val) => handleStatusChange(invoice.id, val)}
                                            >
                                                <SelectTrigger className={`w-[110px] h-8 border text-xs font-medium ${getStatusColor(invoice.status)}`}>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="Draft">Draft</SelectItem>
                                                    <SelectItem value="Sent">Sent</SelectItem>
                                                    <SelectItem value="Received">Received</SelectItem>
                                                    <SelectItem value="Reject">Reject</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </TableCell>
                                        <TableCell className="text-right">{invoice.total}</TableCell>
                                        <TableCell className="text-right" onClick={(e) => e.stopPropagation()}>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button
                                                        variant="ghost"
                                                        className="h-8 w-8 p-0"
                                                    >
                                                        <span className="sr-only">
                                                            Open menu
                                                        </span>
                                                        <MoreHorizontal className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                    <DropdownMenuItem onClick={() => openHistory(invoice.id)}>
                                                        <History className="mr-2 h-4 w-4" />
                                                        History
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/invoices/${invoice.id}`}>View</Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/invoices/${invoice.id}/edit`}>Edit</Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem 
                                                        className="text-red-600 focus:text-red-600 cursor-pointer"
                                                        onClick={() => {
                                                            if (confirm('Are you sure?')) {
                                                                router.delete(`/invoices/${invoice.id}`);
                                                            }
                                                        }}
                                                    >
                                                        Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>

            <StatusHistoryDialog 
                open={historyOpen} 
                onOpenChange={setHistoryOpen} 
                invoiceId={selectedInvoiceId} 
            />
        </AppLayout>
    );
}
