<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Get all supported currencies with their display names
     */
    public static function getAllCurrencies(): array
    {
        return [
            "" => "Select default currency",
            "USD" => 'USD - US Dollar ($)',
            "EUR" => "EUR - Euro (€)",
            "GBP" => "GBP - British Pound (£)",
            "JPY" => "JPY - Japanese Yen (¥)",
            "CAD" => 'CAD - Canadian Dollar (C$)',
            "AUD" => 'AUD - Australian Dollar (A$)',
            "CHF" => "CHF - Swiss Franc (Fr)",
            "CNY" => "CNY - Chinese Yuan (¥)",
            "INR" => "INR - Indian Rupee (₹)",
            "KRW" => "KRW - South Korean Won (₩)",
            "HKD" => 'HKD - Hong Kong Dollar (HK$)',
            "SGD" => 'SGD - Singapore Dollar (S$)',
            "THB" => "THB - Thai Baht (฿)",
            "MYR" => "MYR - Malaysian Ringgit (RM)",
            "IDR" => "IDR - Indonesian Rupiah (Rp)",
            "PHP" => "PHP - Philippine Peso (₱)",
            "VND" => "VND - Vietnamese Dong (₫)",
            "MMK" => "MMK - Myanmar Kyat (K)",
            "BDT" => "BDT - Bangladeshi Taka (৳)",
            "LKR" => "LKR - Sri Lankan Rupee (Rs)",
            "NPR" => "NPR - Nepalese Rupee (Rs)",
            "PKR" => "PKR - Pakistani Rupee (Rs)",
            "AFN" => "AFN - Afghan Afghani (؋)",
            "AED" => "AED - UAE Dirham (د.إ)",
            "SAR" => "SAR - Saudi Riyal (﷼)",
            "QAR" => "QAR - Qatari Riyal (﷼)",
            "KWD" => "KWD - Kuwaiti Dinar (د.ك)",
            "BHD" => "BHD - Bahraini Dinar (د.ب)",
            "OMR" => "OMR - Omani Rial (ر.ع)",
            "JOD" => "JOD - Jordanian Dinar (د.أ)",
            "ILS" => "ILS - Israeli New Shekel (₪)",
            "TRY" => "TRY - Turkish Lira (₺)",
            "RUB" => "RUB - Russian Ruble (₽)",
            "ZAR" => "ZAR - South African Rand (R)",
            "EGP" => "EGP - Egyptian Pound (ج.م)",
            "NGN" => "NGN - Nigerian Naira (₦)",
            "KES" => "KES - Kenyan Shilling (KSh)",
            "GHS" => "GHS - Ghanaian Cedi (GH₵)",
            "XOF" => "XOF - West African CFA Franc (CFA)",
            "XAF" => "XAF - Central African CFA Franc (FCFA)",
            "TZS" => "TZS - Tanzanian Shilling (TSh)",
            "UGX" => "UGX - Ugandan Shilling (USh)",
            "RWF" => "RWF - Rwandan Franc (RF)",
            "BIF" => "BIF - Burundian Franc (FBu)",
            "MGA" => "MGA - Malagasy Ariary (Ar)",
            "SCR" => "SCR - Seychellois Rupee (SR)",
            "MUR" => "MUR - Mauritian Rupee (Rs)",
            "KZT" => "KZT - Kazakhstani Tenge (₸)",
            "UZS" => 'UZS - Uzbekistani Som (so\'m)',
            "KGS" => "KGS - Kyrgyzstani Som (с)",
            "TJS" => "TJS - Tajikistani Somoni (SM)",
            "NOK" => "NOK - Norwegian Krone (kr)",
            "SEK" => "SEK - Swedish Krona (kr)",
            "DKK" => "DKK - Danish Krone (kr)",
            "PLN" => "PLN - Polish Złoty (zł)",
            "CZK" => "CZK - Czech Koruna (Kč)",
            "HUF" => "HUF - Hungarian Forint (Ft)",
            "RON" => "RON - Romanian Leu (lei)",
            "BGN" => "BGN - Bulgarian Lev (лв)",
            "HRK" => "HRK - Croatian Kuna (kn)",
            "RSD" => "RSD - Serbian Dinar (дин)",
            "ALL" => "ALL - Albanian Lek (L)",
            "MKD" => "MKD - Macedonian Denar (ден)",
        ];
    }

    /**
     * Get only the currency codes for validation
     */
    public static function getCurrencyCodes(): array
    {
        $currencies = self::getAllCurrencies();
        unset($currencies[""]); // Remove the "Select default currency" option
        return array_keys($currencies);
    }

    /**
     * Get validation rule string for currency
     */
    public static function getValidationRule(): string
    {
        $codes = self::getCurrencyCodes();
        return "required|string|size:3|in:" . implode(",", $codes);
    }

    /**
     * Get currency symbol by code
     */
    public static function getCurrencySymbol(string $code): string
    {
        $symbols = [
            "USD" => '$',
            "EUR" => "€",
            "GBP" => "£",
            "JPY" => "¥",
            "CAD" => 'C$',
            "AUD" => 'A$',
            "CHF" => "Fr",
            "CNY" => "¥",
            "INR" => "₹",
            "KRW" => "₩",
            "HKD" => 'HK$',
            "SGD" => 'S$',
            "THB" => "฿",
            "MYR" => "RM",
            "IDR" => "Rp",
            "PHP" => "₱",
            "VND" => "₫",
            "MMK" => "K",
            "BDT" => "৳",
            "LKR" => "Rs",
            "NPR" => "Rs",
            "PKR" => "Rs",
            "AFN" => "؋",
            "AED" => "د.إ",
            "SAR" => "﷼",
            "QAR" => "﷼",
            "KWD" => "د.ك",
            "BHD" => "د.ب",
            "OMR" => "ر.ع",
            "JOD" => "د.أ",
            "ILS" => "₪",
            "TRY" => "₺",
            "RUB" => "₽",
            "ZAR" => "R",
            "EGP" => "ج.م",
            "NGN" => "₦",
            "KES" => "KSh",
            "GHS" => "GH₵",
            "XOF" => "CFA",
            "XAF" => "FCFA",
            "TZS" => "TSh",
            "UGX" => "USh",
            "RWF" => "RF",
            "BIF" => "FBu",
            "MGA" => "Ar",
            "SCR" => "SR",
            "MUR" => "Rs",
            "KZT" => "₸",
            "UZS" => "so'm",
            "KGS" => "с",
            "TJS" => "SM",
            "NOK" => "kr",
            "SEK" => "kr",
            "DKK" => "kr",
            "PLN" => "zł",
            "CZK" => "Kč",
            "HUF" => "Ft",
            "RON" => "lei",
            "BGN" => "лв",
            "HRK" => "kn",
            "RSD" => "дин",
            "ALL" => "L",
            "MKD" => "ден",
        ];

        return $symbols[$code] ?? $code;
    }

    /**
     * Format amount with currency symbol
     */
    public static function formatAmount(
        float $amount,
        string $currencyCode,
    ): string {
        $symbol = self::getCurrencySymbol($currencyCode);
        return $symbol . number_format($amount, 2);
    }
}
