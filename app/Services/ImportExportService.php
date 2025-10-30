<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Quote;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ImportExportService
{
    /**
     * Export customers to CSV
     */
    public static function exportCustomers($userId): string
    {
        $customers = User::find($userId)->customers()
            ->select('id', 'name', 'email', 'phone', 'company', 'address', 'city', 'state', 'country', 'postal_code', 'tax_id', 'website', 'is_active', 'created_at', 'updated_at')
            ->get();

        $filename = "customers_export_" . date('Y-m-d_H-i-s') . ".csv";
        $filepath = storage_path("app/exports/{$filename}");

        $handle = fopen($filepath, 'w');

        // CSV headers
        fputcsv($handle, [
            'ID', 'Name', 'Email', 'Phone', 'Company', 'Address', 'City', 'State', 'Country',
            'Postal Code', 'Tax ID', 'Website', 'Is Active', 'Created At', 'Updated At'
        ]);

        foreach ($customers as $customer) {
            fputcsv($handle, [
                $customer->id,
                $customer->name,
                $customer->email,
                $customer->phone,
                $customer->company,
                $customer->address,
                $customer->city,
                $customer->state,
                $customer->country,
                $customer->postal_code,
                $customer->tax_id,
                $customer->website,
                $customer->is_active ? 'Yes' : 'No',
                $customer->created_at->format('Y-m-d H:i:s'),
                $customer->updated_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($handle);
        return $filename;
    }

    /**
     * Export items to CSV
     */
    public static function exportItems($userId): string
    {
        $items = User::find($userId)->items()
            ->select('id', 'name', 'description', 'sku', 'unit_price', 'cost', 'stock_quantity', 'reorder_level', 'is_active', 'created_at', 'updated_at')
            ->get();

        $filename = "items_export_" . date('Y-m-d_H-i-s') . ".csv";
        $filepath = storage_path("app/exports/{$filename}");

        $handle = fopen($filepath, 'w');

        fputcsv($handle, [
            'ID', 'Name', 'Description', 'SKU', 'Unit Price', 'Cost', 'Stock Quantity',
            'Reorder Level', 'Is Active', 'Created At', 'Updated At'
        ]);

        foreach ($items as $item) {
            fputcsv($handle, [
                $item->id,
                $item->name,
                $item->description,
                $item->sku,
                $item->unit_price,
                $item->cost,
                $item->stock_quantity,
                $item->reorder_level,
                $item->is_active ? 'Yes' : 'No',
                $item->created_at->format('Y-m-d H:i:s'),
                $item->updated_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($handle);
        return $filename;
    }

    /**
     * Export quotes to CSV
     */
    public static function exportQuotes($userId, $startDate = null, $endDate = null): string
    {
        $query = User::find($userId)->quotes()
            ->with('customer')
            ->select('id', 'quote_number', 'customer_id', 'title', 'po_number', 'date', 'valid_until', 'currency', 'status', 'total', 'created_at', 'updated_at');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $quotes = $query->get();

        $filename = "quotes_export_" . date('Y-m-d_H-i-s') . ".csv";
        $filepath = storage_path("app/exports/{$filename}");

        $handle = fopen($filepath, 'w');

        fputcsv($handle, [
            'ID', 'Quote Number', 'Customer', 'Title', 'PO Number', 'Date', 'Valid Until',
            'Currency', 'Status', 'Total', 'Created At', 'Updated At'
        ]);

        foreach ($quotes as $quote) {
            fputcsv($handle, [
                $quote->id,
                $quote->quote_number,
                $quote->customer->name,
                $quote->title,
                $quote->po_number,
                $quote->date->format('Y-m-d'),
                $quote->valid_until->format('Y-m-d'),
                $quote->currency,
                $quote->status,
                $quote->total,
                $quote->created_at->format('Y-m-d H:i:s'),
                $quote->updated_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($handle);
        return $filename;
    }

    /**
     * Export invoices to CSV
     */
    public static function exportInvoices($userId, $startDate = null, $endDate = null): string
    {
        $query = User::find($userId)->invoices()
            ->with('customer')
            ->select('id', 'invoice_number', 'customer_id', 'title', 'po_number', 'date', 'due_date', 'currency', 'status', 'total', 'created_at', 'updated_at');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $invoices = $query->get();

        $filename = "invoices_export_" . date('Y-m-d_H-i-s') . ".csv";
        $filepath = storage_path("app/exports/{$filename}");

        $handle = fopen($filepath, 'w');

        fputcsv($handle, [
            'ID', 'Invoice Number', 'Customer', 'Title', 'PO Number', 'Date', 'Due Date',
            'Currency', 'Status', 'Total', 'Created At', 'Updated At'
        ]);

        foreach ($invoices as $invoice) {
            fputcsv($handle, [
                $invoice->id,
                $invoice->invoice_number,
                $invoice->customer->name,
                $invoice->title,
                $invoice->po_number,
                $invoice->date->format('Y-m-d'),
                $invoice->due_date->format('Y-m-d'),
                $invoice->currency,
                $invoice->status,
                $invoice->total,
                $invoice->created_at->format('Y-m-d H:i:s'),
                $invoice->updated_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($handle);
        return $filename;
    }

    /**
     * Import customers from CSV
     */
    public static function importCustomers($userId, $filePath): array
    {
        $results = [
            'success_count' => 0,
            'error_count' => 0,
            'errors' => [],
            'duplicates' => 0,
        ];

        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle); // Skip header row
        $rowNumber = 2;

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $data = [
                    'name' => $row[0] ?? null,
                    'email' => $row[1] ?? null,
                    'phone' => $row[2] ?? null,
                    'company' => $row[3] ?? null,
                    'address' => $row[4] ?? null,
                    'city' => $row[5] ?? null,
                    'state' => $row[6] ?? null,
                    'country' => $row[7] ?? null,
                    'postal_code' => $row[8] ?? null,
                    'tax_id' => $row[9] ?? null,
                    'website' => $row[10] ?? null,
                    'is_active' => strtolower($row[11] ?? 'yes') === 'yes',
                ];

                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'nullable|email|max:255|unique:customers,email,NULL,id,user_id,' . $userId,
                    'phone' => 'nullable|string|max:50',
                    'company' => 'nullable|string|max:255',
                    'address' => 'nullable|string|max:500',
                    'city' => 'nullable|string|max:100',
                    'state' => 'nullable|string|max:100',
                    'country' => 'nullable|string|max:100',
                    'postal_code' => 'nullable|string|max:20',
                    'tax_id' => 'nullable|string|max:50',
                    'website' => 'nullable|url|max:255',
                    'is_active' => 'boolean',
                ]);

                if ($validator->fails()) {
                    $results['errors'][] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    $results['error_count']++;
                    $rowNumber++;
                    continue;
                }

                // Check for duplicate email
                if ($data['email']) {
                    $existing = Customer::where('user_id', $userId)
                        ->where('email', $data['email'])
                        ->first();
                    if ($existing) {
                        $results['duplicates']++;
                        $results['errors'][] = "Row {$rowNumber}: Customer with email '{$data['email']}' already exists";
                        $results['error_count']++;
                        $rowNumber++;
                        continue;
                    }
                }

                Customer::create(array_merge($data, ['user_id' => $userId]));
                $results['success_count']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                $results['error_count']++;
            }

            $rowNumber++;
        }

        fclose($handle);
        return $results;
    }

    /**
     * Import items from CSV
     */
    public static function importItems($userId, $filePath): array
    {
        $results = [
            'success_count' => 0,
            'error_count' => 0,
            'errors' => [],
            'duplicates' => 0,
        ];

        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle); // Skip header row
        $rowNumber = 2;

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $data = [
                    'name' => $row[0] ?? null,
                    'description' => $row[1] ?? null,
                    'sku' => $row[2] ?? null,
                    'unit_price' => is_numeric($row[3]) ? (float) $row[3] : null,
                    'cost' => is_numeric($row[4]) ? (float) $row[4] : null,
                    'stock_quantity' => is_numeric($row[5]) ? (int) $row[5] : null,
                    'reorder_level' => is_numeric($row[6]) ? (int) $row[6] : null,
                    'is_active' => strtolower($row[7] ?? 'yes') === 'yes',
                ];

                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'description' => 'nullable|string|max:1000',
                    'sku' => 'nullable|string|max:100|unique:items,sku,NULL,id,user_id,' . $userId,
                    'unit_price' => 'required|numeric|min:0|max:999999.99',
                    'cost' => 'nullable|numeric|min:0|max:999999.99',
                    'stock_quantity' => 'nullable|integer|min:0|max:999999',
                    'reorder_level' => 'nullable|integer|min:0|max:999999',
                    'is_active' => 'boolean',
                ]);

                if ($validator->fails()) {
                    $results['errors'][] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    $results['error_count']++;
                    $rowNumber++;
                    continue;
                }

                // Check for duplicate SKU
                if ($data['sku']) {
                    $existing = Item::where('user_id', $userId)
                        ->where('sku', $data['sku'])
                        ->first();
                    if ($existing) {
                        $results['duplicates']++;
                        $results['errors'][] = "Row {$rowNumber}: Item with SKU '{$data['sku']}' already exists";
                        $results['error_count']++;
                        $rowNumber++;
                        continue;
                    }
                }

                Item::create(array_merge($data, ['user_id' => $userId]));
                $results['success_count']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                $results['error_count']++;
            }

            $rowNumber++;
        }

        fclose($handle);
        return $results;
    }

    /**
     * Get template for customer import
     */
    public static function getCustomerImportTemplate(): string
    {
        $filename = "customers_import_template.csv";
        $filepath = storage_path("app/exports/{$filename}");

        $handle = fopen($filepath, 'w');

        fputcsv($handle, [
            'Name', 'Email', 'Phone', 'Company', 'Address', 'City', 'State',
            'Country', 'Postal Code', 'Tax ID', 'Website', 'Is Active (Yes/No)'
        ]);

        // Add sample data
        fputcsv($handle, [
            'Sample Customer', 'customer@example.com', '+1234567890', 'Sample Company',
            '123 Main St', 'New York', 'NY', 'USA', '10001', '123456789',
            'https://example.com', 'Yes'
        ]);

        fclose($handle);
        return $filename;
    }

    /**
     * Get template for item import
     */
    public static function getItemImportTemplate(): string
    {
        $filename = "items_import_template.csv";
        $filepath = storage_path("app/exports/{$filename}");

        $handle = fopen($filepath, 'w');

        fputcsv($handle, [
            'Name', 'Description', 'SKU', 'Unit Price', 'Cost', 'Stock Quantity',
            'Reorder Level', 'Is Active (Yes/No)'
        ]);

        // Add sample data
        fputcsv($handle, [
            'Sample Item', 'This is a sample item description', 'SKU001',
            '99.99', '50.00', '100', '10', 'Yes'
        ]);

        fclose($handle);
        return $filename;
    }

    /**
     * Create export directory if it doesn't exist
     */
    public static function ensureExportDirectory(): void
    {
        $exportDir = storage_path('app/exports');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
    }

    /**
     * Clean up old export files
     */
    public static function cleanupOldExports(int $daysOld = 7): int
    {
        $exportDir = storage_path('app/exports');
        $deletedCount = 0;

        if (is_dir($exportDir)) {
            $files = glob($exportDir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < strtotime("-{$daysOld} days")) {
                    unlink($file);
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }
}