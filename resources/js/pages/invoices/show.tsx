import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, Download, Pencil } from 'lucide-react';
import InvoiceForm, { Customer, Invoice } from './components/invoice-form';
import { generateInvoicePdf } from './components/invoice-pdf';

interface ShowProps {
    invoice: Invoice;
    customers: Customer[];
    userPreference: any;
}

export default function Show({ invoice, customers, userPreference }: ShowProps) {
    const handleDownloadPdf = () => {
        generateInvoicePdf(invoice, userPreference);
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Invoices', href: '/invoices' }, { title: invoice.invoice_number || `INV-${invoice.id}`, href: `/invoices/${invoice.id}` }]}>
            <Head title={`Invoice #${invoice.invoice_number || invoice.id}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between mb-4">
                    <Link href="/invoices" className="flex items-center text-gray-500 hover:text-gray-700">
                        <ChevronLeft className="mr-1 h-4 w-4" />
                        Back to Invoices
                    </Link>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={handleDownloadPdf}>
                            <Download className="mr-2 h-4 w-4" /> Download PDF
                        </Button>
                        <Link href={`/invoices/${invoice.id}/edit`}>
                            <Button variant="outline">
                                <Pencil className="mr-2 h-4 w-4" /> Edit Invoice
                            </Button>
                        </Link>
                    </div>
                </div>

                <InvoiceForm
                    customers={customers}
                    invoice={invoice}
                    readonly={true}
                    userPreference={userPreference}
                    className="max-w-4xl w-full"
                />
            </div>
        </AppLayout>
    );
}
