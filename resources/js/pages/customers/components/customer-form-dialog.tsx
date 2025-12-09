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
import { Plus } from 'lucide-react';
import { useEffect } from 'react';

interface Customer {
    id: number;
    name: string;
    email: string;
    address: string;
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
    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: customer?.name || '',
        email: customer?.email || '',
        address: customer?.address || '',
    });

    useEffect(() => {
        if (customer) {
            setData({
                name: customer.name,
                email: customer.email,
                address: customer.address,
            });
        } else {
            reset();
        }
    }, [customer, open, reset, setData]);

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (isEditing) {
            put(`/customers/${customer.id}`, {
                onSuccess: () => {
                    reset();
                    onOpenChange(false);
                },
            });
        } else {
            post('/customers', {
                onSuccess: () => {
                    reset();
                    onOpenChange(false);
                },
            });
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
                        <Input
                            id="address"
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                            className="col-span-3"
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
