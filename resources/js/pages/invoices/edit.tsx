import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import InvoiceForm, { Customer, Invoice, UserPreference } from './components/invoice-form';

interface EditProps {
    invoice: Invoice;
    customers: Customer[];
    userPreference: UserPreference;
}

export default function Edit({ invoice, customers, userPreference }: EditProps) {
    return (
        <AppLayout breadcrumbs={[
            { title: 'Invoices', href: '/invoices' },
            { title: 'Edit', href: `/invoices/${invoice.id}/edit` }
        ]}>
            <Head title={`Edit Invoice #${invoice.invoice_number || invoice.id}`} />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between mb-4">
                    <h1 className="text-2xl font-bold">Edit Invoice</h1>
                </div>
                <InvoiceForm customers={customers} invoice={invoice} userPreference={userPreference} />
            </div>
        </AppLayout>
    );
}
