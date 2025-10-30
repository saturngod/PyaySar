<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 20mm;
            @bottom-center {
                content: counter(page);
                font-size: 10px;
                color: #666;
            }
        }

        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            background: #ffffff;
    }

  {!! \App\Services\PdfTemplateService::generateTemplateCSS($template ?? 'modern') !!}
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                @if($pdfSettings['show_logo'] ?? true && $company->logo_url)
                    <img src="{{ $company->logo_url }}" alt="Company Logo" class="company-logo">
                @endif
                <h1>{{ $company->name }}</h1>
                <p>{{ $company->address }}</p>
                <p>{{ $company->email }}</p>
                @if($company->phone)
                    <p>{{ $company->phone }}</p>
                @endif
            </div>
            <div class="invoice-details">
                <div class="invoice-number">Invoice #{{ $invoice->invoice_number }}</div>
                <div class="invoice-date">{{ $invoice->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-section">
            <h3>Bill To:</h3>
            <div class="customer-info">
                <strong>{{ $invoice->customer->name }}</strong><br>
                @if($invoice->customer->address)
                    {{ $invoice->customer->address }}<br>
                @endif
                @if($invoice->customer->city)
                    {{ $invoice->customer->city }},
                @endif
                @if($invoice->customer->country)
                    {{ $invoice->customer->country }}
                @endif
                @if($invoice->customer->postal_code)
                    {{ $invoice->customer->postal_code }}
                @endif
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
                @foreach($items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->item->name }}</div>
                        @if($pdfSettings['show_item_description'] ?? true && $item->item->description)
                            <div class="item-description">{{ $item->item->description }}</div>
                        @endif
                    </td>
                    <td>{{ $item->qty }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td>${{ number_format($item->qty * $item->price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Terms and Notes -->
        @if($pdfSettings['show_terms_notes'] ?? true)
            @if($company->default_terms || $invoice->terms)
                <div style="margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 6px;">
                    <h4 style="margin-bottom: 10px; color: #333;">Terms & Conditions</h4>
                    <p style="margin: 0; line-height: 1.5;">{{ $invoice->terms ?? $company->default_terms }}</p>
                </div>
            @endif

            @if($company->default_notes || $invoice->notes)
                <div style="margin: 20px 0; padding: 20px; background: #f0f0f0; border-radius: 6px;">
                    <h4 style="margin-bottom: 10px; color: #333;">Notes</h4>
                    <p style="margin: 0; line-height: 1.5;">{{ $invoice->notes ?? $company->default_notes }}</p>
                </div>
            @endif
        @endif

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="totals-label">Subtotal:</td>
                    <td class="totals-value">${{ number_format($invoice->sub_total, 2) }}</td>
                </tr>
                <tr>
                    <td class="totals-label">Tax (0%):</td>
                    <td class="totals-value">$0.00</td>
                </tr>
                <tr class="grand-total">
                    <td class="totals-label">Total:</td>
                    <td class="totals-value">${{ number_format($invoice->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This invoice was generated on {{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
