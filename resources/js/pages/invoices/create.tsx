import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import InvoiceForm, { Customer } from './components/invoice-form';

interface CreateProps {
    customers: Customer[];
    nextInvoiceId: number;
    userPreference: any;
}

export default function Create({ customers, nextInvoiceId, userPreference }: CreateProps) {
    return (
        <AppLayout breadcrumbs={[
            { title: 'Invoices', href: '/invoices' },
            { title: 'Create', href: '/invoices/create' }
        ]}>
            <Head title="Create Invoice" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
               <div className="flex items-center justify-between mb-4">
                    <h1 className="text-2xl font-bold">Create Invoice</h1>
                </div>
                <InvoiceForm customers={customers} nextInvoiceId={nextInvoiceId} userPreference={userPreference} />
            </div>
        </AppLayout>
    );
}
