import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import ItemForm from './components/item-form';

export default function Create() {
    return (
        <AppLayout breadcrumbs={[
            { title: 'Items', href: '/items' },
            { title: 'Create', href: '/items/create' }
        ]}>
            <Head title="Create Item" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between mb-4">
                    <h1 className="text-2xl font-bold">Create Item</h1>
                </div>

                <div className="rounded-xl bg-card p-6 border text-card-foreground shadow-sm max-w-2xl">
                    <ItemForm />
                </div>
            </div>
        </AppLayout>
    );
}
