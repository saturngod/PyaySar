import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/react';
import { Textarea } from '@/components/ui/textarea';
import { Plus } from 'lucide-react';
import { useEffect, useState } from 'react';

interface Customer {
    id: number;
    name: string;
    email: string;
    address: string;
    avatar?: string;
}

interface CustomerFormDialogProps {
    customer?: Customer;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function CustomerFormDialog({
    customer,
    open,
    onOpenChange,
}: CustomerFormDialogProps) {
    const isEditing = !!customer;
    const { data, setData, post, processing, errors, reset } = useForm({
        name: customer?.name || '',
        email: customer?.email || '',
        address: customer?.address || '',
        avatar: null as File | null,
        _method: isEditing ? 'put' : 'post',
    });

    const [newAvatarPreview, setNewAvatarPreview] = useState<string | null>(null);

    useEffect(() => {
        // Use setTimeout to avoid synchronous state updates during rendering causing cascading renders
        const timer = setTimeout(() => {
            if (customer) {
                setData({
                    name: customer.name,
                    email: customer.email,
                    address: customer.address,
                    avatar: null,
                    _method: 'put',
                });
                setNewAvatarPreview(null);
            } else {
                reset();
                setData('_method', 'post');
                setNewAvatarPreview(null);
            }
        }, 0);
        return () => clearTimeout(timer);
    }, [customer, open, reset, setData]);

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const url = isEditing ? `/customers/${customer.id}` : '/customers';

        post(url, {
            onSuccess: () => {
                reset();
                setNewAvatarPreview(null);
                onOpenChange(false);
            },
        });
    };

    const handleAvatarChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            setData('avatar', file);
            setNewAvatarPreview(URL.createObjectURL(file));
        }
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            {!isEditing && (
                <DialogTrigger asChild>
                    <Button>
                        <Plus className="mr-2 h-4 w-4" /> Add Customer
                    </Button>
                </DialogTrigger>
            )}
            <DialogContent className="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing ? 'Edit Customer' : 'Add Customer'}
                    </DialogTitle>
                    <DialogDescription>
                        {isEditing
                            ? 'Update customer details.'
                            : 'Add a new customer to your list.'}
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={onSubmit} className="grid gap-4 py-4">

                    {/* Avatar Upload */}
                    <div className="flex flex-col items-center gap-4">
                        {(newAvatarPreview || customer?.avatar) ? (
                            <img
                                src={newAvatarPreview || `/storage/${customer?.avatar}`}
                                alt="Avatar Preview"
                                className="h-24 w-24 rounded-full object-cover border border-gray-200"
                            />
                        ) : (
                            <div className="h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                No Image
                            </div>
                        )}
                        <Input
                            id="avatar"
                            type="file"
                            accept="image/*"
                            onChange={handleAvatarChange}
                            className="w-full max-w-xs"
                        />
                        {errors.avatar && (
                            <span className="text-sm text-red-500">
                                {errors.avatar}
                            </span>
                        )}
                    </div>

                    <div className="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="name" className="text-right">
                            Name
                        </Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="col-span-3"
                        />
                        {errors.name && (
                            <span className="col-span-4 text-right text-sm text-red-500">
                                {errors.name}
                            </span>
                        )}
                    </div>
                    <div className="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="email" className="text-right">
                            Email
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            className="col-span-3"
                        />
                        {errors.email && (
                            <span className="col-span-4 text-right text-sm text-red-500">
                                {errors.email}
                            </span>
                        )}
                    </div>
                    <div className="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="address" className="text-right">
                            Address
                        </Label>
                        <Textarea
                            id="address"
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                            className="col-span-3 min-h-[100px]"
                        />
                        {errors.address && (
                            <span className="col-span-4 text-right text-sm text-red-500">
                                {errors.address}
                            </span>
                        )}
                    </div>
                    <DialogFooter>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save changes'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
