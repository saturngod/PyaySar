<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote #{{ $quote->quote_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .header .company {
            margin-top: 5px;
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            margin-bottom: 30px;
        }

        .greeting h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #333;
        }

        .greeting p {
            margin: 0;
            font-size: 16px;
            color: #666;
        }

        .quote-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .quote-details h3 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #333;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            color: #666;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
        }

        .amount {
            font-size: 18px;
            font-weight: 700;
            color: #28a745;
        }

        .cta {
            text-align: center;
            margin: 30px 0;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .footer p {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }

        .company-info {
            margin-bottom: 15px;
        }

        .company-info h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
        }

        .company-info p {
            margin: 3px 0;
            font-size: 13px;
            color: #666;
        }

        .footer-note {
            font-size: 12px;
            color: #999;
            font-style: italic;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px 10px;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 30px 20px;
            }

            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Quote #{{ $quote->quote_number }}</h1>
            <div class="company">{{ $company->name }}</div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <h2>Hello {{ $quote->customer->name }}!</h2>
                <p>Thank you for your interest. Please find your quote details below.</p>
            </div>

            <!-- Quote Details -->
            <div class="quote-details">
                <h3>Quote Summary</h3>

                <div class="detail-row">
                    <span class="detail-label">Quote Number:</span>
                    <span class="detail-value">#{{ $quote->quote_number }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Issue Date:</span>
                    <span class="detail-value">{{ $quote->date->format('F j, Y') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Valid Until:</span>
                    <span class="detail-value">{{ $quote->valid_until->format('F j, Y') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value amount">{{ $quote->currency }} {{ number_format($quote->total, 2) }}</span>
                </div>

                @if($quote->po_number)
                    <div class="detail-row">
                        <span class="detail-label">PO Number:</span>
                        <span class="detail-value">{{ $quote->po_number }}</span>
                    </div>
                @endif
            </div>

            <!-- Call to Action -->
            <div class="cta">
                <a href="#" class="btn">View Full Quote</a>
            </div>

            @if($quote->notes)
                <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <h4 style="margin: 0 0 10px 0; color: #856404;">Notes:</h4>
                    <p style="margin: 0; color: #856404;">{{ $quote->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="company-info">
                <h4>{{ $company->name }}</h4>
                @if($company->address)
                    <p>{{ $company->address }}</p>
                @endif
                @if($company->phone)
                    <p>📞 {{ $company->phone }}</p>
                @endif
                <p>📧 {{ $company->email }}</p>
                @if($company->website)
                    <p>🌐 {{ $company->website }}</p>
                @endif
            </div>

            <p class="footer-note">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>