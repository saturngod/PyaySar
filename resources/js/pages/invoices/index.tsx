// import { Badge } from '@/components/ui/badge'; // Removed unused import
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
    invoice_number?: string;
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
            return 'bg-[#0ecb81]/15 text-[#0ecb81] border-[#0ecb81]/20';
        case 'Sent':
            return 'bg-blue-500/15 text-blue-500 border-blue-500/20';
        case 'Reject':
            return 'bg-[#f6465d]/15 text-[#f6465d] border-[#f6465d]/20';
        default:
            return 'bg-muted text-muted-foreground border-border';
    }
};

export default function Index({ invoices, filters, customers }: IndexProps) {
    const [historyOpen, setHistoryOpen] = useState(false);
    const [selectedInvoiceId, setSelectedInvoiceId] = useState<number | null>(null);
    const [invoiceToDelete, setInvoiceToDelete] = useState<Invoice | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);

    const handleDelete = () => {
        if (invoiceToDelete) {
            setIsDeleting(true);
            router.delete(`/invoices/${invoiceToDelete.id}`, {
                preserveScroll: true,
                onFinish: () => {
                    setIsDeleting(false);
                    setInvoiceToDelete(null);
                },
            });
        }
    };

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

    const openHistory = (invoiceId: number) => {
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
                                            {invoice.invoice_number || `INV-${invoice.id}`}
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
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem
                                                        className="cursor-pointer"
                                                        onClick={() => router.post(`/invoices/${invoice.id}/duplicate`)}
                                                    >
                                                        Duplicate
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        className="text-red-600 focus:text-red-600 cursor-pointer"
                                                        onClick={() => setInvoiceToDelete(invoice)}
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

            <Dialog open={!!invoiceToDelete} onOpenChange={(open) => !open && setInvoiceToDelete(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Are you absolutely sure?</DialogTitle>
                        <DialogDescription>
                            This action cannot be undone. This will permanently delete the invoice "{invoiceToDelete?.invoice_number || `INV-${invoiceToDelete?.id}`}".
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setInvoiceToDelete(null)} disabled={isDeleting}>
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={handleDelete} disabled={isDeleting}>
                            {isDeleting ? 'Deleting...' : 'Delete'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout >
    );
}
