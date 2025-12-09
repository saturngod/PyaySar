import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { CustomerFormDialog } from './components/customer-form-dialog';

interface Customer {
    id: number;
    name: string;
    email: string;
    address: string;
    created_at: string;
}

interface IndexProps {
    customers: Customer[];
}

export default function Index({ customers }: IndexProps) {
    const [open, setOpen] = useState(false);
    const [editingCustomer, setEditingCustomer] = useState<Customer | undefined>(
        undefined,
    );

    const handleEdit = (customer: Customer) => {
        setEditingCustomer(customer);
        setOpen(true);
    };

    const handleCreate = () => {
        setEditingCustomer(undefined);
        setOpen(true);
    };

    const handleDelete = (customer: Customer) => {
        if (confirm('Are you sure you want to delete this customer?')) {
            router.delete(`/customers/${customer.id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Customers', href: '/customers' }
        ]}>
            <Head title="Customers" />
            
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Customers</h1>
                    <Button onClick={handleCreate}>Add Customer</Button>
                </div>

                <div className="rounded-md border bg-card">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Address</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {customers.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={4} className="h-24 text-center">
                                        No results.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                customers.map((customer) => (
                                    <TableRow key={customer.id}>
                                        <TableCell>{customer.name}</TableCell>
                                        <TableCell>{customer.email}</TableCell>
                                        <TableCell>{customer.address}</TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleEdit(customer)}
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(customer)}
                                                    className="text-destructive hover:text-destructive"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>

            <CustomerFormDialog
                open={open}
                onOpenChange={setOpen}
                customer={editingCustomer}
            />
        </AppLayout>
    );
}
