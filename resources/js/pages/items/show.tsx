import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Item } from '@/types';

interface ShowProps {
    item: Item;
}

export default function Show({ item }: ShowProps) {
    return (
        <AppLayout breadcrumbs={[
            { title: 'Items', href: '/items' },
            { title: item.name, href: `/items/${item.id}` }
        ]}>
            <Head title={item.name} />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between mb-4">
                    <h1 className="text-2xl font-bold">{item.name}</h1>
                    <Button asChild>
                        <Link href={route('items.edit', item.id)}>Edit Item</Link>
                    </Button>
                </div>

                <div className="rounded-xl bg-card p-6 border text-card-foreground shadow-sm max-w-2xl space-y-4">
                    <div>
                        <h3 className="font-medium text-gray-500">Name</h3>
                        <p>{item.name}</p>
                    </div>
                    <div>
                        <h3 className="font-medium text-gray-500">Price</h3>
                        <p>${Number(item.price).toFixed(2)}</p>
                    </div>
                    {item.description && (
                        <div>
                            <h3 className="font-medium text-gray-500">Description</h3>
                            <p className="whitespace-pre-wrap">{item.description}</p>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
