import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import ItemForm from './components/item-form';

interface Item {
    id: number;
    name: string;
    description: string | null;
    price: string;
}

interface EditProps {
    item: Item;
}

export default function Edit({ item }: EditProps) {
    return (
        <AppLayout breadcrumbs={[
            { title: 'Items', href: '/items' },
            { title: 'Edit', href: `/items/${item.id}/edit` }
        ]}>
            <Head title="Edit Item" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between mb-4">
                    <h1 className="text-2xl font-bold">Edit Item</h1>
                </div>

                <div className="rounded-xl bg-card p-6 border text-card-foreground shadow-sm max-w-2xl">
                    <ItemForm item={item} />
                </div>
            </div>
        </AppLayout>
    );
}
