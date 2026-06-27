import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import React from 'react';

interface Item {
    id: number;
    name: string;
    description: string | null;
    price: string;
}

interface ItemFormProps {
    item?: Item;
    className?: string;
}

export default function ItemForm({ item, className }: ItemFormProps) {
    const isEditing = !!item;

    const { data, setData, post, put, processing, errors } = useForm({
        name: item?.name || '',
        description: item?.description || '',
        price: item?.price || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (isEditing) {
            put(`/items/${item.id}`);
        } else {
            post('/items');
        }
    };

    return (
        <form onSubmit={handleSubmit} className={cn("space-y-6", className)}>
            <div className="space-y-4">
                <div className="space-y-2">
                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder="Item Name"
                    />
                    {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="price">Price</Label>
                    <Input
                        id="price"
                        type="number"
                        min="0"
                        step="0.01"
                        value={data.price}
                        onChange={(e) => setData('price', e.target.value)}
                        placeholder="0.00"
                    />
                    {errors.price && <p className="text-sm text-red-500">{errors.price}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="description">Description</Label>
                    <Textarea
                        id="description"
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder="Item Description"
                        rows={4}
                        maxLength={5000}
                    />
                    {errors.description && <p className="text-sm text-red-500">{errors.description}</p>}
                </div>
            </div>

            <Button type="submit" disabled={processing}>
                {processing ? 'Saving...' : (isEditing ? 'Update Item' : 'Create Item')}
            </Button>
        </form>
    );
}
