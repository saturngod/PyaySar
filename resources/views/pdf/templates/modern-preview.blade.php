<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst($template ?? 'Modern') }} Template Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .company-info h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .company-info p {
            color: #666;
            margin-bottom: 4px;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 8px;
        }

        .invoice-date {
            color: #666;
            font-size: 14px;
        }

        .customer-section {
            margin-bottom: 30px;
        }

        .customer-section h3 {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .customer-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid #3498db;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background: #34495e;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }

        .items-table tbody tr:hover {
            background: #f8f9fa;
        }

        .item-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .item-description {
            color: #666;
            font-size: 14px;
            font-style: italic;
        }

        .totals-section {
            text-align: right;
            margin-top: 30px;
        }

        .totals-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8px;
        }

        .totals-label {
            width: 120px;
            padding-right: 20px;
            text-align: right;
            color: #666;
            font-size: 14px;
        }

        .totals-value {
            width: 100px;
            text-align: right;
            font-weight: 600;
            color: #2c3e50;
        }

        .totals-row.grand-total {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #34495e;
        }

        .totals-row.grand-total .totals-label,
        .totals-row.grand-total .totals-value {
            font-size: 18px;
            color: #2c3e50;
            font-weight: 700;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        .template-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #3498db;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="template-badge">{{ ucfirst($template ?? 'Modern') }} Template</div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>{{ $company_name ?? 'Sample Company' }}</h1>
                <p>{{ $company_address ?? '123 Business Street, City, State 12345' }}</p>
                <p>{{ $company_email ?? 'info@samplecompany.com' }}</p>
            </div>
            <div class="invoice-details">
                <div class="invoice-number">{{ $quote_number ?? 'Q-2024-001' }}</div>
                <div class="invoice-date">{{ $date ?? now()->format('M d, Y') }}</div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-section">
            <h3>Bill To:</h3>
            <div class="customer-info">
                <strong>{{ $customer_name ?? 'John Doe' }}</strong><br>
                {{ $customer_address ?? '456 Client Avenue, Customer City, State 67890' }}
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%">Item</th>
                    <th style="width: 15%">Quantity</th>
                    <th style="width: 15%">Price</th>
                    <th style="width: 20%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items ?? [] as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item['name'] }}</div>
                        @if(!empty($item['description']))
                        <div class="item-description">{{ $item['description'] }}</div>
                        @endif
                    </td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>${{ number_format($item['price'], 2) }}</td>
                    <td>${{ number_format($item['quantity'] * $item['price'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-row">
                <div class="totals-label">Subtotal:</div>
                <div class="totals-value">${{ number_format($subtotal ?? 0, 2) }}</div>
            </div>
            <div class="totals-row">
                <div class="totals-label">Tax ({{ ($tax_rate ?? 0) * 100 }}%):</div>
                <div class="totals-value">${{ number_format($tax ?? 0, 2) }}</div>
            </div>
            <div class="totals-row grand-total">
                <div class="totals-label">Total:</div>
                <div class="totals-value">${{ number_format($total ?? 0, 2) }}</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is a preview of the {{ ucfirst($template ?? 'Modern') }} template.</p>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>
