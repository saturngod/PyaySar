<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst($template ?? 'Classic') }} Template Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.8;
            color: #000;
            background: white;
            padding: 40px;
        }

        .invoice-container {
            max-width: 750px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px double #000;
            padding-bottom: 20px;
        }

        .company-info h1 {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .company-info p {
            font-size: 14px;
            margin-bottom: 5px;
            font-style: italic;
        }

        .invoice-details {
            text-align: center;
            margin-top: 15px;
        }

        .invoice-number {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .invoice-date {
            font-size: 14px;
            color: #666;
        }

        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 40px;
        }

        .section-box {
            flex: 1;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .section-content {
            font-size: 14px;
            line-height: 1.6;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .items-table th {
            border: 1px solid #000;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            background: #f0f0f0;
            text-transform: uppercase;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 12px;
            vertical-align: top;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .item-description {
            font-style: italic;
            color: #666;
            font-size: 12px;
        }

        .totals-section {
            width: 300px;
            margin-left: auto;
            margin-bottom: 30px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ccc;
        }

        .totals-label {
            font-weight: bold;
        }

        .totals-value {
            text-align: right;
        }

        .totals-row.grand-total {
            border-top: 2px solid #000;
            border-bottom: none;
            font-size: 16px;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 12px;
            font-style: italic;
            color: #666;
        }

        .template-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #000;
            color: white;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #000;
        }
    </style>
</head>
<body>
    <div class="template-badge">{{ ucfirst($template ?? 'Classic') }} Template</div>

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

        <!-- Billing Information -->
        <div class="billing-section">
            <div class="section-box">
                <div class="section-title">Bill To:</div>
                <div class="section-content">
                    <strong>{{ $customer_name ?? 'John Doe' }}</strong><br>
                    {{ $customer_address ?? '456 Client Avenue, Customer City, State 67890' }}
                </div>
            </div>
            <div class="section-box">
                <div class="section-title">Payment Terms:</div>
                <div class="section-content">
                    Net 30 Days<br>
                    Due within 30 days of invoice date
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 45%">Description</th>
                    <th style="width: 15%">Qty</th>
                    <th style="width: 20%">Unit Price</th>
                    <th style="width: 20%">Amount</th>
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
            <p>Thank you for your business. Payment is due within 30 days.</p>
            <p>This is a preview of the {{ ucfirst($template ?? 'Classic') }} template.</p>
        </div>
    </div>
</body>
</html>
