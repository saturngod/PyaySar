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
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { format } from 'date-fns';
import { CalendarIcon, Check, ChevronsUpDown, X } from 'lucide-react';
import { useState } from 'react';

interface FilterProps {
    filters: {
        status?: string;
        date_from?: string;
        date_to?: string;
        customer_id?: string;
    };
    customers: { id: number; name: string }[];
}

export function InvoiceFilters({ filters, customers }: FilterProps) {
    const [status, setStatus] = useState(filters.status || 'all');
    const [customerId, setCustomerId] = useState(filters.customer_id || 'all');
    const [dateFrom, setDateFrom] = useState<Date | undefined>(
        filters.date_from ? new Date(filters.date_from) : undefined,
    );
    const [dateTo, setDateTo] = useState<Date | undefined>(
        filters.date_to ? new Date(filters.date_to) : undefined,
    );
    const [customerOpen, setCustomerOpen] = useState(false);

    const applyFilters = (
        newStatus = status,
        newCustomerId = customerId,
        newDateFrom = dateFrom,
        newDateTo = dateTo,
    ) => {
        router.get(
            '/invoices',
            {
                status: newStatus === 'all' ? undefined : newStatus,
                customer_id: newCustomerId === 'all' ? undefined : newCustomerId,
                date_from: newDateFrom ? format(newDateFrom, 'yyyy-MM-dd') : undefined,
                date_to: newDateTo ? format(newDateTo, 'yyyy-MM-dd') : undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    const handleStatusChange = (value: string) => {
        setStatus(value);
        applyFilters(value);
    };

    const handleCustomerChange = (value: string) => {
        setCustomerId(value);
        applyFilters(status, value);
    };

    const handleDateSelect = (
        type: 'from' | 'to',
        date: Date | undefined,
    ) => {
        if (type === 'from') {
            setDateFrom(date);
            applyFilters(status, customerId, date, dateTo);
        } else {
            setDateTo(date);
            applyFilters(status, customerId, dateFrom, date);
        }
    };

    const clearFilters = () => {
        setStatus('all');
        setCustomerId('all');
        setDateFrom(undefined);
        setDateTo(undefined);
        router.get('/invoices');
    };

    return (
        <div className="mb-4 flex flex-wrap items-center gap-2">
            <Select value={status} onValueChange={handleStatusChange}>
                <SelectTrigger className="w-[180px]">
                    <SelectValue placeholder="Filter by Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Statuses</SelectItem>
                    <SelectItem value="Draft">Draft</SelectItem>
                    <SelectItem value="Sent">Sent</SelectItem>
                    <SelectItem value="Received">Received</SelectItem>
                    <SelectItem value="Reject">Reject</SelectItem>
                </SelectContent>
            </Select>

            <Popover open={customerOpen} onOpenChange={setCustomerOpen}>
                <PopoverTrigger asChild>
                    <Button
                        variant="outline"
                        role="combobox"
                        aria-expanded={customerOpen}
                        className="w-[200px] justify-between"
                    >
                        {customerId !== 'all'
                            ? customers.find(
                                  (customer) => customer.id.toString() === customerId,
                              )?.name
                            : 'Select Customer...'}
                        <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-[200px] p-0">
                    <Command>
                        <CommandInput placeholder="Search customer..." />
                         <CommandList>
                            <CommandEmpty>No customer found.</CommandEmpty>
                            <CommandGroup>
                                <CommandItem
                                    value="all"
                                    onSelect={() => {
                                        handleCustomerChange('all');
                                        setCustomerOpen(false);
                                    }}
                                >
                                    <Check
                                        className={cn(
                                            'mr-2 h-4 w-4',
                                            customerId === 'all'
                                                ? 'opacity-100'
                                                : 'opacity-0',
                                        )}
                                    />
                                    All Customers
                                </CommandItem>
                                {customers.map((customer) => (
                                    <CommandItem
                                        key={customer.id}
                                        value={customer.name}
                                        onSelect={() => {
                                            handleCustomerChange(customer.id.toString());
                                            setCustomerOpen(false);
                                        }}
                                    >
                                        <Check
                                            className={cn(
                                                'mr-2 h-4 w-4',
                                                customerId === customer.id.toString()
                                                    ? 'opacity-100'
                                                    : 'opacity-0',
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

            <div className="flex items-center gap-2">
                <Popover>
                    <PopoverTrigger asChild>
                        <Button
                            variant="outline"
                            className={cn(
                                'w-[180px] justify-start text-left font-normal',
                                !dateFrom && 'text-muted-foreground',
                            )}
                        >
                            <CalendarIcon className="mr-2 h-4 w-4" />
                            {dateFrom ? format(dateFrom, 'PPP') : <span>From date</span>}
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0">
                        <Calendar
                            mode="single"
                            selected={dateFrom}
                            onSelect={(date) => handleDateSelect('from', date)}
                            initialFocus
                        />
                    </PopoverContent>
                </Popover>
                <span>-</span>
                <Popover>
                    <PopoverTrigger asChild>
                        <Button
                            variant="outline"
                            className={cn(
                                'w-[180px] justify-start text-left font-normal',
                                !dateTo && 'text-muted-foreground',
                            )}
                        >
                            <CalendarIcon className="mr-2 h-4 w-4" />
                            {dateTo ? format(dateTo, 'PPP') : <span>To date</span>}
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0">
                        <Calendar
                            mode="single"
                            selected={dateTo}
                            onSelect={(date) => handleDateSelect('to', date)}
                            initialFocus
                        />
                    </PopoverContent>
                </Popover>
            </div>

            <Button variant="ghost" onClick={clearFilters} size="icon">
                <X className="h-4 w-4" />
            </Button>
        </div>
    );
}
