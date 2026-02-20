<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number ?? $invoice->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @php
        $primaryColor = $userPreference->pdf_primary_color ?? '#1e40af';
        $customFontBase64 = '';
        if ($userPreference && $userPreference->pdf_font) {
            $fontPath = storage_path('app/private/fonts/' . $userPreference->pdf_font);
            if (file_exists($fontPath)) {
                $fontData = file_get_contents($fontPath);
                $customFontBase64 = base64_encode($fontData);
            }
        }
    @endphp
    <style>
        @if($customFontBase64)
        @font-face {
            font-family: 'CustomFont';
            src: url(data:font/truetype;charset=utf-8;base64,{{ $customFontBase64 }}) format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @endif

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: @if($customFontBase64) 'CustomFont', @endif -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
        }

        .page-break { page-break-after: always; }

        .text-primary { color: {{ $primaryColor }}; }
        .bg-primary { background-color: {{ $primaryColor }}; }
        .border-primary { border-color: {{ $primaryColor }}; }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* Compact table styling */
        .items-table th {
            background-color: {{ $primaryColor }};
            color: white;
            padding: 6px 10px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        /* Footer stays at bottom */
        .invoice-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 8px 40px;
            font-size: 9px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            background-color: white;
        }
    </style>
</head>
<body class="bg-white text-slate-800 p-10 antialiased">

    <div class="max-w-full">
        <!-- Compact Header -->
        <header class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
                @if($userPreference && $userPreference->company_logo)
                    @php
                        $logoPath = Storage::disk('public')->path($userPreference->company_logo);
                        $logoSrc = '';
                        if (file_exists($logoPath)) {
                            $logoData = file_get_contents($logoPath);
                            $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                            $logoSrc = 'data:image/' . $logoType . ';base64,' . base64_encode($logoData);
                        }
                    @endphp
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Company Logo" class="h-12 w-auto object-contain">
                    @endif
                @endif
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $userPreference->company_name ?? 'Your Company Name' }}</h2>
                    @if($userPreference && $userPreference->company_address)
                        <p class="text-xs text-slate-500">{{ $userPreference->company_address }}</p>
                    @endif
                    @if($userPreference && $userPreference->company_email)
                        <p class="text-xs text-slate-500">{{ $userPreference->company_email }}</p>
                    @endif
                </div>
            </div>

            <div class="text-right">
                <h1 class="text-2xl font-bold tracking-tight" style="color: {{ $primaryColor }}">INVOICE</h1>
                <div class="mt-2 text-xs">
                    <table class="ml-auto">
                        <tbody>
                            <tr>
                                <td class="text-slate-500 pr-3 py-0.5">Invoice #</td>
                                <td class="font-semibold text-slate-900 py-0.5">{{ $invoice->invoice_number ?? $invoice->id }}</td>
                            </tr>
                            <tr>
                                <td class="text-slate-500 pr-3 py-0.5">Date</td>
                                <td class="text-slate-900 py-0.5">{{ \Carbon\Carbon::parse($invoice->open_date)->format('M j, Y') }}</td>
                            </tr>
                            @if($invoice->due_date)
                            <tr>
                                <td class="text-slate-500 pr-3 py-0.5">Due</td>
                                <td class="text-slate-900 py-0.5">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M j, Y') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if($invoice->status === 'Paid')
                <div class="mt-3">
                    <span class="inline-block px-3 py-1 text-xs font-bold tracking-wide uppercase rounded" style="border: 2px solid {{ $primaryColor }}; color: {{ $primaryColor }}; background-color: {{ $primaryColor }}15">
                        PAID
                    </span>
                </div>
                @endif
            </div>
        </header>

        <!-- Bill To - Compact -->
        <div class="mb-5 p-3 bg-slate-50 rounded-lg border border-slate-200">
            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Bill To</h3>
            <div class="flex items-center gap-3">
                @if($invoice->customer->avatar)
                    @php
                        $avatarStr = $invoice->customer->avatar;
                        $customerAvatarSrc = '';
                        if (str_starts_with($avatarStr, 'http')) {
                            $customerAvatarSrc = $avatarStr;
                        } else {
                            $customerAvatarPath = Storage::disk('public')->path($avatarStr);
                            if (file_exists($customerAvatarPath)) {
                                $avatarData = file_get_contents($customerAvatarPath);
                                $avatarType = pathinfo($customerAvatarPath, PATHINFO_EXTENSION);
                                $customerAvatarSrc = 'data:image/' . $avatarType . ';base64,' . base64_encode($avatarData);
                            }
                        }
                    @endphp
                    @if($customerAvatarSrc)
                        <img src="{{ $customerAvatarSrc }}" alt="Customer Avatar" class="h-10 w-10 shrink-0 rounded-full object-cover border border-slate-200">
                    @endif
                @endif
                <div>
                    <p class="font-semibold text-slate-900">{{ $invoice->customer->name }}</p>
                    @if($invoice->customer->email)
                        <p class="text-xs text-slate-500">{{ $invoice->customer->email }}</p>
                    @endif
                    @if($invoice->customer->address)
                        <p class="text-xs text-slate-500">{{ $invoice->customer->address }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table - Compact -->
        <div class="mb-4 border border-slate-200 rounded-lg overflow-hidden">
            <table class="items-table">
                <thead>
                    <tr>
                        <th scope="col" class="text-left">Description</th>
                        <th scope="col" class="text-right" style="width: 60px;">Qty</th>
                        <th scope="col" class="text-right" style="width: 90px;">Price</th>
                        <th scope="col" class="text-right" style="width: 100px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <p class="font-medium text-slate-900 text-xs">{{ $item->item_name }}</p>
                            @if($item->description)
                                <p class="text-[10px] text-slate-500 mt-0.5">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td class="text-right text-slate-700">{{ $item->qty }}</td>
                        <td class="text-right text-slate-700">
                            @if(fmod($item->price, 1) == 0) {{ number_format($item->price, 0) }} @else {{ number_format($item->price, 2) }} @endif
                        </td>
                        <td class="text-right font-semibold text-slate-900">
                            @if(fmod($item->qty * $item->price, 1) == 0) {{ number_format($item->qty * $item->price, 0) }} @else {{ number_format($item->qty * $item->price, 2) }} @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Notes - Compact Side by Side -->
        <div class="flex justify-between items-start gap-6">
            <!-- Notes and Bank Info -->
            <div class="flex-1">
                @if($invoice->notes)
                    <div class="mb-3">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Notes</h4>
                        <p class="text-xs text-slate-600 bg-slate-50 rounded p-2 border border-slate-100">{{ $invoice->notes }}</p>
                    </div>
                @endif

                @if($invoice->bank_account_info)
                    <div>
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Payment Details</h4>
                        <p class="text-xs text-slate-600 bg-slate-50 rounded p-2 border border-slate-100">{{ $invoice->bank_account_info }}</p>
                    </div>
                @endif
            </div>

            <!-- Summary - Compact -->
            <div class="w-52 bg-slate-50 rounded-lg p-3 border border-slate-200">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs text-slate-500">Subtotal</span>
                    <span class="text-xs font-medium text-slate-900">{{ $invoice->currency }} @if(fmod($invoice->sub_total, 1) == 0) {{ number_format($invoice->sub_total, 0) }} @else {{ number_format($invoice->sub_total, 2) }} @endif</span>
                </div>

                <div class="h-px bg-slate-200 my-2"></div>

                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-slate-900">Total</span>
                    <span class="text-lg font-bold" style="color: {{ $primaryColor }}">{{ $invoice->currency }} @if(fmod($invoice->total, 1) == 0) {{ number_format($invoice->total, 0) }} @else {{ number_format($invoice->total, 2) }} @endif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Fixed Footer -->
    @if($userPreference && $userPreference->pdf_footer_message)
    <div class="invoice-footer">
        {{ $userPreference->pdf_footer_message }}
    </div>
    @endif

</body>
</html>
