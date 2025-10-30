@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar current-route="reports" />

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
                        <p class="mt-1 text-sm text-gray-600">Get insights into your business performance with comprehensive reports.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Reports & Analytics</h1>
        <p class="text-gray-600">Get insights into your business performance with comprehensive reports.</p>
    </div>

    <!-- Dashboard Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Current Month Revenue</p>
                    <p class="text-2xl font-bold text-gray-900" id="currentMonthRevenue">$0</p>
                    <p class="text-sm text-green-600 mt-1" id="revenueGrowth">+0%</p>
                </div>
                <div class="bg-primary-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Outstanding Amount</p>
                    <p class="text-2xl font-bold text-gray-900" id="outstandingAmount">$0</p>
                    <p class="text-sm text-gray-500 mt-1" id="outstandingInvoices">0 invoices</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Quotes</p>
                    <p class="text-2xl font-bold text-gray-900" id="totalQuotes">0</p>
                    <p class="text-sm text-gray-500 mt-1">All time</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Paid Invoices</p>
                    <p class="text-2xl font-bold text-gray-900" id="paidInvoices">0</p>
                    <p class="text-sm text-gray-500 mt-1" id="totalInvoices">0 total</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Controls -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                <select id="dateRange" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="last30">Last 30 Days</option>
                    <option value="last90">Last 90 Days</option>
                    <option value="thisYear">This Year</option>
                    <option value="lastYear">Last Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            <div id="customDateRange" class="hidden">
                <div class="flex gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" id="startDate" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" id="endDate" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                <select id="reportType" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="revenue">Revenue Report</option>
                    <option value="customers">Customer Performance</option>
                    <option value="items">Item Sales</option>
                    <option value="outstanding">Outstanding Invoices</option>
                    <option value="conversions">Quote Conversions</option>
                </select>
            </div>

            <button onclick="loadReport()" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition-colors">
                Generate Report
            </button>

            <button onclick="exportReport()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                Export CSV
            </button>
        </div>
    </div>

    <!-- Report Content -->
    <div id="reportContent" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="text-center py-12">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6m12-6v6m0 0V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6m0 0v1a1 1 0 001 1h4a1 1 0 001-1v-1"></path>
            </svg>
            <p class="text-gray-500">Select report type and date range to generate insights</p>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Quotes</h3>
            <div id="recentQuotes" class="space-y-3">
                <p class="text-gray-500 text-sm">Loading recent quotes...</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Invoices</h3>
            <div id="recentInvoices" class="space-y-3">
                <p class="text-gray-500 text-sm">Loading recent invoices...</p>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Customers This Month</h3>
        <div id="topCustomers" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <p class="text-gray-500 text-sm">Loading top customers...</p>
        </div>
    </div>
</div>

<script>
// Load dashboard stats on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
});

function loadDashboardStats() {
    fetch('/reports/dashboard-stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('currentMonthRevenue').textContent = formatCurrency(data.stats.current_month_revenue);
            document.getElementById('revenueGrowth').textContent = data.stats.revenue_growth >= 0 ?
                `+${data.stats.revenue_growth.toFixed(1)}%` : `${data.stats.revenue_growth.toFixed(1)}%`;
            document.getElementById('outstandingAmount').textContent = formatCurrency(data.stats.outstanding_amount);
            document.getElementById('outstandingInvoices').textContent = `${data.stats.total_invoices - data.stats.paid_invoices} invoices`;
            document.getElementById('totalQuotes').textContent = data.stats.total_quotes;
            document.getElementById('paidInvoices').textContent = data.stats.paid_invoices;
            document.getElementById('totalInvoices').textContent = data.stats.total_invoices;

            // Load recent activity
            loadRecentActivity(data.recent_activity);

            // Load top customers
            loadTopCustomers(data.top_customers);
        });
}

function loadRecentActivity(activity) {
    const quotesContainer = document.getElementById('recentQuotes');
    const invoicesContainer = document.getElementById('recentInvoices');

    quotesContainer.innerHTML = activity.quotes.map(quote => `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
                <p class="font-medium text-gray-900">${quote.number}</p>
                <p class="text-sm text-gray-500">${quote.customer_name}</p>
            </div>
            <div class="text-right">
                <p class="font-medium text-gray-900">${formatCurrency(quote.total)}</p>
                <p class="text-sm text-gray-500">${quote.created_at}</p>
            </div>
        </div>
    `).join('');

    invoicesContainer.innerHTML = activity.invoices.map(invoice => `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
                <p class="font-medium text-gray-900">${invoice.number}</p>
                <p class="text-sm text-gray-500">${invoice.customer_name}</p>
            </div>
            <div class="text-right">
                <p class="font-medium text-gray-900">${formatCurrency(invoice.total)}</p>
                <p class="text-sm text-gray-500">${invoice.created_at}</p>
            </div>
        </div>
    `).join('');
}

function loadTopCustomers(customers) {
    const container = document.getElementById('topCustomers');

    container.innerHTML = customers.map((customer, index) => `
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-primary-600 font-semibold">${index + 1}</span>
                </div>
                <div>
                    <p class="font-medium text-gray-900">${customer.name}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-medium text-gray-900">${formatCurrency(customer.revenue)}</p>
            </div>
        </div>
    `).join('');
}

function loadReport() {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;

    let startDate = null;
    let endDate = null;

    if (dateRange === 'last30') {
        startDate = moment().subtract(30, 'days').format('YYYY-MM-DD');
        endDate = moment().format('YYYY-MM-DD');
    } else if (dateRange === 'last90') {
        startDate = moment().subtract(90, 'days').format('YYYY-MM-DD');
        endDate = moment().format('YYYY-MM-DD');
    } else if (dateRange === 'thisYear') {
        startDate = moment().startOf('year').format('YYYY-MM-DD');
        endDate = moment().format('YYYY-MM-DD');
    } else if (dateRange === 'lastYear') {
        startDate = moment().subtract(1, 'year').startOf('year').format('YYYY-MM-DD');
        endDate = moment().subtract(1, 'year').endOf('year').format('YYYY-MM-DD');
    } else if (dateRange === 'custom') {
        startDate = document.getElementById('startDate').value;
        endDate = document.getElementById('endDate').value;
    }

    let url = `/reports/${reportType}`;
    if (startDate && endDate) {
        url += `?start_date=${startDate}&end_date=${endDate}`;
    }

    document.getElementById('reportContent').innerHTML = '<div class="text-center py-12"><p class="text-gray-500">Loading report...</p></div>';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            displayReport(reportType, data);
        });
}

function displayReport(type, data) {
    const container = document.getElementById('reportContent');

    switch(type) {
        case 'revenue':
            displayRevenueReport(container, data);
            break;
        case 'customers':
            displayCustomerReport(container, data);
            break;
        case 'items':
            displayItemsReport(container, data);
            break;
        case 'outstanding':
            displayOutstandingReport(container, data);
            break;
        case 'conversions':
            displayConversionsReport(container, data);
            break;
    }
}

function displayRevenueReport(container, data) {
    const html = `
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-primary-50 p-4 rounded-lg">
                    <p class="text-sm text-primary-600 font-medium">Total Revenue</p>
                    <p class="text-2xl font-bold text-primary-900">${formatCurrency(data.totals.total_revenue)}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-sm text-green-600 font-medium">Total Tax</p>
                    <p class="text-2xl font-bold text-green-900">${formatCurrency(data.totals.total_tax)}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <p class="text-sm text-yellow-600 font-medium">Total Discount</p>
                    <p class="text-2xl font-bold text-yellow-900">${formatCurrency(data.totals.total_discount)}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <p class="text-sm text-purple-600 font-medium">Total Invoices</p>
                    <p class="text-2xl font-bold text-purple-900">${data.totals.total_invoices}</p>
                </div>
            </div>
        </div>

        <div>
            <h4 class="text-md font-semibold text-gray-900 mb-3">Monthly Revenue</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoices</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${data.monthly_data.map(row => `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.month}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.invoice_count}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatCurrency(row.revenue)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatCurrency(row.net_revenue)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    container.innerHTML = html;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
}

function exportReport() {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;

    let startDate = null;
    let endDate = null;

    if (dateRange === 'last30') {
        startDate = moment().subtract(30, 'days').format('YYYY-MM-DD');
        endDate = moment().format('YYYY-MM-DD');
    } else if (dateRange === 'custom') {
        startDate = document.getElementById('startDate').value;
        endDate = document.getElementById('endDate').value;
    }

    const formData = new FormData();
    formData.append('type', reportType);
    if (startDate) formData.append('start_date', startDate);
    if (endDate) formData.append('end_date', endDate);

    fetch('/reports/export', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create a temporary link to download the file
            const link = document.createElement('a');
            link.href = `/storage/temp/${data.filename}`;
            link.download = data.filename;
            link.click();
        }
    });
}

// Show/hide custom date range
document.getElementById('dateRange').addEventListener('change', function() {
    const customRange = document.getElementById('customDateRange');
    if (this.value === 'custom') {
        customRange.classList.remove('hidden');
    } else {
        customRange.classList.add('hidden');
    }
});
</script>
    </main>
</div>
@endsection
