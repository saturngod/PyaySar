import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import { useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { CalendarIcon, Check, Plus, Trash2, X } from 'lucide-react';
import { useState, useEffect, useRef } from 'react';
import axios from 'axios';

const formatAmount = (amount: number | string): string => {
    const num = Number(amount);
    if (isNaN(num)) return '0.00';
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num);
};

export interface Customer {
    id: number;
    name: string;
    email: string;
    address: string;
    avatar?: string;
    avatar_url?: string;
}

export interface InvoiceItem {
    id?: number;
    item_name: string;
    description: string;
    qty: number;
    price: number;
    total_price: number;
}

export interface Invoice {
    id: number;
    invoice_number?: string;
    customer_id: number;
    status: string;
    currency: string;
    open_date: string;
    due_date?: string;
    items: InvoiceItem[];
    notes?: string;
    bank_account_info?: string;
    customer?: Customer;
}

export interface UserPreference {
    company_name?: string;
    company_email?: string;
    company_address?: string;
    company_logo?: string;
    company_logo_url?: string;
}

interface InvoiceFormProps {
    customers: Customer[];
    invoice?: Invoice;
    nextInvoiceNumber?: string;
    readonly?: boolean;
    userPreference?: UserPreference | null;
    className?: string;
}

interface AutocompleteItemNameProps {
    value: string;
    onChange: (value: string) => void;
    onSelectItem?: (item: { id?: number; name: string; description?: string; price?: number }) => void;
    className?: string;
    placeholder?: string;
}

function AutocompleteItemName({ value, onChange, onSelectItem, className, placeholder }: AutocompleteItemNameProps) {
    const [open, setOpen] = useState(false);
    const [results, setResults] = useState<{ id?: number; name: string; description?: string; price?: number }[]>([]);
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (!value || value.length < 2) {
            const timer = setTimeout(() => {
                setResults([]);
                setOpen(false);
            }, 0);
            return () => clearTimeout(timer);
        }

        const fetchResults = async () => {
            try {
                const response = await axios.get(`/invoices/search-items?query=${encodeURIComponent(value)}`);
                if (response.data && response.data.result) {
                    setResults(response.data.result);
                    if (response.data.result.length > 0) {
                        setOpen(true);
                    } else {
                        setOpen(false);
                    }
                }
            } catch (error) {
                console.error("Error fetching items", error);
            }
        };

        const timer = setTimeout(() => {
            fetchResults();
        }, 300);

        return () => clearTimeout(timer);

    }, [value]);

    return (
        <div className="relative group w-full">
            <Input
                ref={inputRef}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                className={className}
                placeholder={placeholder}
                onBlur={() => setTimeout(() => setOpen(false), 200)}
                onFocus={() => {
                    if (value && value.length >= 2 && results.length > 0) {
                        setOpen(true);
                    }
                }}
            />
            {open && results.length > 0 && (
                <div className="absolute top-full left-0 w-full mt-1 bg-popover border border-border rounded-md shadow-lg z-50 overflow-hidden animate-in fade-in-0 zoom-in-95">
                    <Command shouldFilter={false}>
                        <CommandList>
                            <CommandGroup heading="Suggestions">
                                {results.map((item, idx) => (
                                    <CommandItem
                                        key={idx}
                                        value={item.name}
                                        onSelect={() => {
                                            onChange(item.name);
                                            onSelectItem?.(item);
                                            setOpen(false);
                                        }}
                                        className="cursor-pointer"
                                    >
                                        <Check className={cn("mr-2 h-4 w-4", value === item.name ? "opacity-100" : "opacity-0")} />
                                        <span>{item.name}</span>
                                        {typeof item.price === 'number' && (
                                            <span className="ml-auto text-xs text-muted-foreground">
                                                {formatAmount(item.price)}
                                            </span>
                                        )}
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </div>
            )}
        </div>
    );
}

export default function InvoiceForm({
    customers,
    invoice,
    nextInvoiceNumber,
    readonly = false,
    userPreference,
    className,
}: InvoiceFormProps) {
    const isEditing = !!invoice;

    // Initial Items State
    const initialItems = invoice?.items?.map((item: InvoiceItem) => ({
        ...item,
        qty: Number(item.qty),
        price: Number(item.price),
    })) || [
            {
                item_name: '',
                description: '',
                qty: 1,
                price: 0,
                total_price: 0,
            },
        ];

    const { data, setData, post, put, processing, errors } = useForm({
        invoice_number: invoice?.invoice_number || nextInvoiceNumber || '',
        customer_id: invoice?.customer_id?.toString() || '',
        status: invoice?.status || 'Draft',
        currency: invoice?.currency || 'MMK',
        open_date: invoice?.open_date ? new Date(invoice.open_date) : new Date(),
        due_date: invoice?.due_date ? new Date(invoice.due_date) : undefined,
        items: initialItems as InvoiceItem[],
        notes: invoice?.notes || '',
        bank_account_info: invoice?.bank_account_info || '',
    });

    const [customerOpen, setCustomerOpen] = useState(false);

    // Derived state for selected customer
    const selectedCustomer = customers.find(c => c.id.toString() === data.customer_id);

    const addItem = () => {
        setData('items', [
            ...data.items,
            {
                item_name: '',
                description: '',
                qty: 1,
                price: 0,
                total_price: 0,
            },
        ]);
    };

    const removeItem = (index: number) => {
        const newItems = [...data.items];
        newItems.splice(index, 1);
        setData('items', newItems);
    };

    const updateItem = (index: number, field: keyof InvoiceItem, value: string | number) => {
        const newItems = [...data.items];
        newItems[index] = { ...newItems[index], [field]: value };

        // Auto-calculate total
        if (field === 'qty' || field === 'price') {
            const qty = field === 'qty' ? Number(value) : Number(newItems[index].qty);
            const price = field === 'price' ? Number(value) : Number(newItems[index].price);
            newItems[index].total_price = qty * price;
        }

        setData('items', newItems);
    };

    const subTotal = data.items.reduce((acc, item) => acc + (item.qty * item.price), 0);
    const total = subTotal;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Format dates for backend
        const formattedData: Record<string, unknown> = {
            ...data,
            open_date: format(data.open_date, 'yyyy-MM-dd'),
            due_date: data.due_date ? format(data.due_date, 'yyyy-MM-dd') : null,
        };

        if (isEditing) {
            put(`/invoices/${invoice.id}`, formattedData);
        } else {
            post('/invoices', formattedData);
        }
    };

    const containerClass = className && className.includes('max-w-')
        ? className
        : cn("max-w-4xl", className);

    return (
        <form onSubmit={handleSubmit} className={cn("mx-auto rounded-xl bg-card p-8 shadow-sm border border-border", containerClass)}>
            {/* Header: Invoice No, Issued, Due */}
            <div className="mb-8 grid grid-cols-3 gap-8 border-b border-dashed border-border pb-8">
                <div>
                    <label className="mb-2 block text-xs font-semibold uppercase text-muted-foreground">
                        Invoice No
                    </label>
                    {readonly ? (
                        <div className="text-xl font-bold text-foreground">
                            {data.invoice_number || (invoice ? `INV-${invoice.id}` : '...')}
                        </div>
                    ) : (
                        <div>
                            <Input
                                value={data.invoice_number}
                                onChange={(e) => setData('invoice_number', e.target.value)}
                                className="text-xl font-bold text-foreground border-none p-0 shadow-none focus-visible:ring-0 h-auto rounded-none"
                                placeholder="INV-..."
                            />
                            {errors.invoice_number && <p className="text-sm text-red-500">{errors.invoice_number}</p>}
                        </div>
                    )}
                </div>
                <div className="col-span-2"> {/* This div now spans two columns */}
                    <div className="grid grid-cols-2 gap-4">
                        <div className={cn("space-y-1", !data.due_date && readonly && "print:col-start-2 print:text-right")}>
                            <Label className="text-xs text-muted-foreground">Issued</Label>
                            {readonly ? (
                                <div className={cn("flex h-8 w-full items-center px-2 -ml-2 text-sm text-foreground", !data.due_date && readonly && "print:justify-end print:px-0 print:ml-0")}>
                                    <CalendarIcon className="mr-2 h-4 w-4 text-muted-foreground" />
                                    {data.open_date ? format(data.open_date, "PPP") : '-'}
                                </div>
                            ) : (
                                <Popover>
                                    <PopoverTrigger asChild>
                                        <Button
                                            variant="outline"
                                            className={cn(
                                                "w-full justify-start text-left font-normal border-none shadow-none h-8 px-2 -ml-2 hover:bg-muted/50",
                                                !data.open_date && "text-muted-foreground"
                                            )}
                                        >
                                            <CalendarIcon className="mr-2 h-4 w-4" />
                                            {data.open_date ? format(data.open_date, "PPP") : <span>Pick a date</span>}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-auto p-0">
                                        <Calendar
                                            mode="single"
                                            selected={data.open_date}
                                            onSelect={(date) => date && setData('open_date', date)}
                                            initialFocus
                                        />
                                    </PopoverContent>
                                </Popover>
                            )}
                            {errors.open_date && <p className="text-sm text-red-500">{errors.open_date}</p>}
                        </div>
                        {(data.due_date || !readonly) && (
                            <div className="space-y-1">
                                <Label className="text-xs text-muted-foreground">Due</Label>
                                {readonly ? (
                                    <div className="flex h-8 w-full items-center px-2 -ml-2 text-sm text-foreground">
                                        <CalendarIcon className="mr-2 h-4 w-4 text-muted-foreground" />
                                        {data.due_date ? format(data.due_date, "PPP") : '-'}
                                    </div>
                                ) : (
                                    <div className="flex items-center">
                                        <Popover>
                                            <PopoverTrigger asChild>
                                                <Button
                                                    variant="outline"
                                                    className={cn(
                                                        "w-full justify-start text-left font-normal border-none shadow-none h-8 px-2 -ml-2 hover:bg-muted/50",
                                                        !data.due_date && "text-muted-foreground"
                                                    )}
                                                >
                                                    <CalendarIcon className="mr-2 h-4 w-4" />
                                                    {data.due_date ? format(data.due_date, "PPP") : <span>Pick a date</span>}
                                                </Button>
                                            </PopoverTrigger>
                                            <PopoverContent className="w-auto p-0">
                                                <Calendar
                                                    mode="single"
                                                    selected={data.due_date}
                                                    onSelect={(date) => setData('due_date', date)}
                                                    initialFocus
                                                />
                                            </PopoverContent>
                                        </Popover>
                                        {data.due_date && (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="h-8 w-8 shrink-0 text-muted-foreground hover:text-red-500"
                                                onClick={() => setData('due_date', undefined)}
                                                title="Clear due date"
                                            >
                                                <X className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>
                                )}
                                {errors.due_date && <p className="text-sm text-red-500">{errors.due_date}</p>}
                            </div>
                        )}
                        <div className="space-y-1 no-print">
                            <Label className="text-xs text-muted-foreground">Currency</Label>
                            {readonly ? (
                                <div className="flex h-8 w-full items-center px-2 -ml-2 text-sm text-foreground">
                                    {data.currency === 'MMK' ? 'MMK (Myanmar Kyat)' : 'USD (US Dollar)'}
                                </div>
                            ) : (
                                <Select
                                    disabled={isEditing}
                                    value={data.currency}
                                    onValueChange={(value) => setData('currency', value)}
                                >
                                    <SelectTrigger className="w-full border-none shadow-none h-8 px-2 -ml-2 bg-transparent hover:bg-muted/50 focus:ring-0">
                                        <SelectValue placeholder="Select Currency" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="MMK">MMK (Myanmar Kyat)</SelectItem>
                                        <SelectItem value="USD">USD (US Dollar)</SelectItem>
                                    </SelectContent>
                                </Select>
                            )}
                        </div>
                        <div className="space-y-1 no-print">
                            <Label className="text-xs text-muted-foreground">Status</Label>
                            {readonly ? (
                                <div className="flex h-8 w-full items-center px-2 -ml-2 text-sm text-foreground">
                                    {data.status}
                                </div>
                            ) : (
                                <Select
                                    value={data.status}
                                    onValueChange={(value) => setData('status', value)}
                                >
                                    <SelectTrigger className="w-full border-none shadow-none h-8 px-2 -ml-2 bg-transparent hover:bg-muted/50 focus:ring-0">
                                        <SelectValue placeholder="Select Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Draft">Draft</SelectItem>
                                        <SelectItem value="Sent">Sent</SelectItem>
                                        <SelectItem value="Received">Received</SelectItem>
                                        <SelectItem value="Reject">Reject</SelectItem>
                                    </SelectContent>
                                </Select>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* From / To Section */}
            <div className="mb-12 grid grid-cols-2 gap-12 border-b border-dashed border-border pb-12">
                {/* From Section */}
                <div>
                    <label className="mb-6 block text-xs font-semibold uppercase text-muted-foreground">
                        From
                    </label>
                    {/* Placeholder Avatar or Logo */}
                    {!readonly && !userPreference?.company_logo_url && <div className="mb-4 h-12 w-12 rounded-full bg-muted" />}

                    {userPreference?.company_logo_url && (
                        <div className="mb-4">
                            <img
                                src={userPreference.company_logo_url}
                                alt="Company Logo"
                                className="h-16 w-16 object-cover rounded-full border border-border/50"
                            />
                        </div>
                    )}

                    <div className="space-y-1">
                        <div className="text-xl font-bold text-foreground">
                            {userPreference?.company_name}
                        </div>
                        <div className="text-muted-foreground">
                            {userPreference?.company_email}
                        </div>
                        <div className="mt-4 text-sm text-muted-foreground whitespace-pre-wrap">
                            {userPreference?.company_address}
                        </div>
                    </div>
                </div>

                {/* To Section */}
                <div>
                    <label className="mb-6 block text-xs font-semibold uppercase text-muted-foreground">
                        To
                    </label>

                    {/* Customer Selector disguised as part of the design or explicit selector */}
                    {!readonly && (
                        <Popover open={!readonly && customerOpen} onOpenChange={setCustomerOpen}>
                            <PopoverTrigger asChild disabled={readonly}>
                                <Button
                                    variant="ghost"
                                    role="combobox"
                                    aria-expanded={customerOpen}
                                    className={cn(
                                        "mb-4 h-12 w-12 rounded-full bg-muted p-0 hover:bg-muted",
                                        !selectedCustomer && "border border-dashed border-border",
                                        readonly && "cursor-default hover:bg-muted"
                                    )}
                                >
                                    {selectedCustomer ? (
                                        selectedCustomer.avatar_url ? (
                                            <img
                                                src={selectedCustomer.avatar_url}
                                                alt="Customer Avatar"
                                                className="h-full w-full rounded-full object-cover"
                                            />
                                        ) : (
                                            <div className="h-full w-full rounded-full bg-muted" />
                                        )
                                    ) : (
                                        <Plus className="h-5 w-5 text-muted-foreground" />
                                    )}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-[300px] p-0" align="start">
                                <Command>
                                    <CommandInput placeholder="Search customer..." />
                                    <CommandList>
                                        <CommandEmpty>No customer found.</CommandEmpty>
                                        <CommandGroup>
                                            {customers.map((customer) => (
                                                <CommandItem
                                                    key={customer.id}
                                                    value={customer.name}
                                                    onSelect={() => {
                                                        setData('customer_id', customer.id.toString());
                                                        setCustomerOpen(false);
                                                    }}
                                                >
                                                    <Check
                                                        className={cn(
                                                            "mr-2 h-4 w-4",
                                                            data.customer_id === customer.id.toString() ? "opacity-100" : "opacity-0"
                                                        )}
                                                    />
                                                    {customer.name}
                                                </CommandItem>
                                            ))}
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    )}
                    {errors.customer_id && <p className="text-sm text-red-500 mb-2">{errors.customer_id}</p>}

                    {/* Readonly Avatar Display */}
                    {readonly && selectedCustomer && (
                        <div className="mb-4">
                            {selectedCustomer.avatar_url ? (
                                <img
                                    src={selectedCustomer.avatar_url}
                                    alt="Customer Avatar"
                                    className="h-16 w-16 object-cover rounded-full border border-border/50"
                                />
                            ) : (
                                <div className="h-12 w-12 rounded-full bg-muted" />
                            )}
                        </div>
                    )}

                    {selectedCustomer ? (
                        <div className="space-y-1">
                            <div className="text-xl font-bold text-foreground">{selectedCustomer.name}</div>
                            <div className="text-muted-foreground">{selectedCustomer.email}</div>
                            <div className="mt-4 whitespace-pre-wrap text-sm text-muted-foreground">
                                {selectedCustomer.address}
                            </div>
                        </div>
                    ) : (
                        <div className="text-muted-foreground italic">Select a customer...</div>
                    )}
                </div>
            </div>

            {/* Line Items */}
            <div className="mb-8 border-b border-dashed border-border pb-8">
                {/* Table Header */}
                <div className="mb-4 grid grid-cols-12 gap-4 text-xs font-semibold uppercase text-muted-foreground">
                    <div className="col-span-6">Description</div>
                    <div className="col-span-2 text-right">Qty</div>
                    <div className="col-span-2 text-right">Price</div>
                    <div className="col-span-2 text-right">Amount</div>
                </div>

                {data.items.map((item, index) => (
                    <div key={index} className="group mb-4 grid grid-cols-12 gap-4 items-start">
                        <div className="col-span-6 space-y-1">
                            {readonly ? (
                                <>
                                    <div className="flex h-9 items-center text-base font-medium">{item.item_name}</div>
                                    {item.description && <div className="text-sm text-muted-foreground whitespace-pre-wrap">{item.description}</div>}
                                </>
                            ) : (
                                <>
                                    <AutocompleteItemName
                                        value={item.item_name}
                                        onChange={(val) => updateItem(index, 'item_name', val)}
                                        onSelectItem={(selected) => {
                                            const newItems = [...data.items];
                                            const qty = Number(newItems[index].qty) || 1;
                                            const price = typeof selected.price === 'number' ? selected.price : Number(newItems[index].price);
                                            newItems[index] = {
                                                ...newItems[index],
                                                item_name: selected.name,
                                                description: newItems[index].description || selected.description || '',
                                                price,
                                                total_price: qty * price,
                                            };
                                            setData('items', newItems);
                                        }}
                                        className="border-none p-0 text-base font-medium shadow-none focus-visible:ring-0"
                                        placeholder="Item Name"
                                    />
                                    <Textarea
                                        value={item.description || ''}
                                        onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => updateItem(index, 'description', e.target.value)}
                                        className="min-h-[20px] resize-none border-none p-0 text-sm text-muted-foreground shadow-none focus-visible:ring-0"
                                        placeholder="Description (optional)"
                                        rows={1}
                                    />
                                </>
                            )}
                        </div>
                        <div className="col-span-2 text-right">
                            {readonly ? (
                                <div className="flex h-9 items-center justify-end">{item.qty}</div>
                            ) : (
                                <Input
                                    type="number"
                                    min="1"
                                    value={item.qty}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => updateItem(index, 'qty', e.target.value)}
                                    className="border-none p-0 text-right shadow-none focus-visible:ring-0"
                                />
                            )}
                        </div>
                        <div className="col-span-2 text-right">
                            {readonly ? (
                                <div className="flex h-9 items-center justify-end">{formatAmount(item.price)}</div>
                            ) : (
                                <Input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={item.price}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => updateItem(index, 'price', e.target.value)}
                                    className="border-none p-0 text-right shadow-none focus-visible:ring-0"
                                />
                            )}
                        </div>
                        <div className="col-span-2 flex h-9 items-center justify-end gap-2 text-right font-medium">
                            {formatAmount(item.qty * item.price)}
                            {!readonly && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    onClick={() => removeItem(index)}
                                    className="h-6 w-6 text-muted-foreground/60 opacity-0 transition-opacity hover:text-red-500 group-hover:opacity-100"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            )}
                        </div>
                    </div>
                ))}

                {!readonly && (
                    <Button
                        type="button"
                        variant="ghost"
                        onClick={addItem}
                        className="mt-2 h-auto p-0 font-medium text-primary hover:bg-transparent hover:text-primary/80"
                    >
                        <Plus className="mr-2 h-4 w-4" /> Add Item
                    </Button>
                )}
                {errors.items && <p className="text-sm text-red-500 mt-2">{errors.items}</p>}
            </div>

            {/* Footer / Totals */}
            <div className="flex justify-between items-start">
                <div className="w-1/2 space-y-8">
                    {(data.notes || !readonly) && (
                        <div>
                            <label className="mb-2 block text-xs font-semibold uppercase text-muted-foreground">
                                Note
                            </label>
                            {readonly ? (
                                <div className="text-sm text-muted-foreground whitespace-pre-wrap">
                                    {data.notes}
                                </div>
                            ) : (
                                <Textarea
                                    value={data.notes}
                                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('notes', e.target.value)}
                                    className="bg-transparent border border-border rounded-md p-3 text-muted-foreground focus-visible:ring-0"
                                    placeholder="Add a note..."
                                />
                            )}
                        </div>
                    )}

                    {(data.bank_account_info || !readonly) && (
                        <div>
                            <label className="mb-2 block text-xs font-semibold uppercase text-muted-foreground">
                                Bank Details
                            </label>
                            {readonly ? (
                                <div className="text-sm text-muted-foreground whitespace-pre-wrap">
                                    {data.bank_account_info}
                                </div>
                            ) : (
                                <Textarea
                                    value={data.bank_account_info}
                                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('bank_account_info', e.target.value)}
                                    className="bg-transparent border border-border rounded-md p-3 text-muted-foreground focus-visible:ring-0 h-24"
                                    placeholder="Bank Name&#10;Account Number&#10;Swift Code"
                                />
                            )}
                        </div>
                    )}
                </div>

                <div className="w-1/3 space-y-4">
                    <div className="flex justify-between text-sm">
                        <span className="font-medium text-muted-foreground">Subtotal</span>
                        <span className="font-medium text-foreground">
                            {data.currency} {formatAmount(subTotal)}
                        </span>
                    </div>
                    <div className="border-t border-dashed border-border pt-4 flex justify-between items-center">
                        <span className="font-semibold text-foreground">Total Amount</span>
                        <span className="font-bold text-foreground">
                            {data.currency} {formatAmount(total)}
                        </span>
                    </div>

                    {!readonly && (
                        <div className="pt-8">
                            <Button type="submit" className="w-full" disabled={processing}>
                                {processing ? 'Saving...' : (isEditing ? 'Update Invoice' : 'Create Invoice')}
                            </Button>
                        </div>
                    )}
                </div>
            </div>
        </form>
    );
}
