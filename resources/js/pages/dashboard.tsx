import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { parseDateOnly } from '@/lib/date-only';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { FileText, Users, ArrowRight, TrendingUp, CheckCircle2 } from 'lucide-react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DraftInvoice {
    id: number;
    invoice_number?: string;
    total: number;
    currency: string;
    open_date: string;
    customer?: {
        name: string;
    };
}

interface PeriodSummary {
    total_amount: number;
    received_amount: number;
    invoice_count: number;
    received_count: number;
}

interface ReportSummary {
    daily: PeriodSummary;
    monthly: PeriodSummary;
    yearly: PeriodSummary;
}

interface DashboardProps {
    totalCustomers: number;
    totalInvoices: number;
    draftInvoices: DraftInvoice[];
    reportSummary: ReportSummary;
}

function formatAmount(amount: number): string {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

type Period = 'daily' | 'monthly' | 'yearly';

const PERIOD_LABELS: Record<Period, string> = {
    daily: 'Today',
    monthly: 'This Month',
    yearly: 'This Year',
};

export default function Dashboard({ totalCustomers, totalInvoices, draftInvoices, reportSummary }: DashboardProps) {
    const [period, setPeriod] = useState<Period>('monthly');
    const summary = reportSummary[period];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

                {/* ── Period selector ── */}
                <div className="flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Overview</h2>
                    <Tabs value={period} onValueChange={(v: string) => setPeriod(v as Period)}>
                        <TabsList>
                            <TabsTrigger value="daily">Today</TabsTrigger>
                            <TabsTrigger value="monthly">This Month</TabsTrigger>
                            <TabsTrigger value="yearly">This Year</TabsTrigger>
                        </TabsList>
                    </Tabs>
                </div>

                {/* ── Report cards ── */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Total Invoiced */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Invoiced</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatAmount(summary.total_amount)}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {summary.invoice_count} invoice{summary.invoice_count !== 1 ? 's' : ''} · {PERIOD_LABELS[period]}
                            </p>
                        </CardContent>
                    </Card>

                    {/* Received (Paid) */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Received</CardTitle>
                            <CheckCircle2 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-trading-up">
                                {formatAmount(summary.received_amount)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {summary.received_count} invoice{summary.received_count !== 1 ? 's' : ''} · {PERIOD_LABELS[period]}
                            </p>
                        </CardContent>
                    </Card>

                    {/* Outstanding */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Outstanding</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatAmount(summary.total_amount - summary.received_amount)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {summary.invoice_count - summary.received_count} invoice{(summary.invoice_count - summary.received_count) !== 1 ? 's' : ''} · {PERIOD_LABELS[period]}
                            </p>
                        </CardContent>
                    </Card>

                    {/* Total Invoices count */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Customers</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{totalCustomers}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {totalInvoices} total invoices
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* ── Recent Draft Invoices ── */}
                <div className="grid gap-4 md:grid-cols-1">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle>Recent Draft Invoices</CardTitle>
                            <Link href="/invoices?status=Draft">
                                <Button variant="ghost" size="sm" className="gap-1">
                                    View All <ArrowRight className="h-4 w-4" />
                                </Button>
                            </Link>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Invoice #</TableHead>
                                        <TableHead>Customer</TableHead>
                                        <TableHead>Date</TableHead>
                                        <TableHead className="text-right">Amount</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {draftInvoices.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="h-24 text-center">
                                                No draft invoices found.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        draftInvoices.map((invoice) => (
                                            <TableRow key={invoice.id}>
                                                <TableCell className="font-medium">
                                                    <Link href={`/invoices/${invoice.id}`} className="hover:underline">
                                                        {invoice.invoice_number || `INV-${invoice.id}`}
                                                    </Link>
                                                </TableCell>
                                                <TableCell>{invoice.customer?.name || 'N/A'}</TableCell>
                                                <TableCell>{parseDateOnly(invoice.open_date).toLocaleDateString()}</TableCell>
                                                <TableCell className="text-right">
                                                    {invoice.currency} {Number(invoice.total).toFixed(2)}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
