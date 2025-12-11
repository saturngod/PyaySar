import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import InvoiceForm, { Customer, UserPreference } from './components/invoice-form';

interface CreateProps {
    customers: Customer[];
    nextInvoiceNumber: string;
    userPreference: UserPreference;
}

export default function Create({ customers, nextInvoiceNumber, userPreference }: CreateProps) {
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
                <InvoiceForm customers={customers} nextInvoiceNumber={nextInvoiceNumber} userPreference={userPreference} />
            </div>
        </AppLayout>
    );
}
