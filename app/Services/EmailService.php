<?php

namespace App\Services;

use App\Mail\QuoteDelivered;
use App\Mail\InvoiceSent;
use App\Models\Quote;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailService
{
    /**
     * Send quote to customer with PDF attachment
     */
    public function sendQuote(Quote $quote, bool $generatePdf = true): bool
    {
        try {
            $company = $this->getCompanyInfo($quote->user);
            $pdfPath = null;

            // Generate PDF if requested
            if ($generatePdf) {
                $pdfService = new PdfService();
                $pdfPath = $pdfService->generateQuote($quote);
            }

            $mail = new QuoteDelivered($quote, $company, $pdfPath);
            $mail->to($quote->customer->email);

            // CC the user if they have a different email
            if ($quote->user->email !== $quote->customer->email) {
                $mail->cc($quote->user->email);
            }

            Mail::send($mail);

            Log::info('Quote email sent successfully', [
                'quote_id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'customer_email' => $quote->customer->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send quote email', [
                'quote_id' => $quote->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send invoice to customer with PDF attachment
     */
    public function sendInvoice(Invoice $invoice, bool $generatePdf = true): bool
    {
        try {
            $company = $this->getCompanyInfo($invoice->user);
            $pdfPath = null;

            // Generate PDF if requested
            if ($generatePdf) {
                $pdfService = new PdfService();
                $pdfPath = $pdfService->generateInvoice($invoice);
            }

            $mail = new InvoiceSent($invoice, $company, $pdfPath);
            $mail->to($invoice->customer->email);

            // CC the user if they have a different email
            if ($invoice->user->email !== $invoice->customer->email) {
                $mail->cc($invoice->user->email);
            }

            Mail::send($mail);

            Log::info('Invoice email sent successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_email' => $invoice->customer->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send bulk quotes to multiple customers
     */
    public function sendBulkQuotes(array $quotes): array
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($quotes as $quote) {
            if ($this->sendQuote($quote)) {
                $results[] = [
                    'quote_id' => $quote->id,
                    'status' => 'success',
                    'message' => 'Quote sent successfully'
                ];
                $successCount++;
            } else {
                $results[] = [
                    'quote_id' => $quote->id,
                    'status' => 'error',
                    'message' => 'Failed to send quote'
                ];
                $failureCount++;
            }
        }

        Log::info('Bulk quote email sending completed', [
            'total_quotes' => count($quotes),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return $results;
    }

    /**
     * Send bulk invoices to multiple customers
     */
    public function sendBulkInvoices(array $invoices): array
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($invoices as $invoice) {
            if ($this->sendInvoice($invoice)) {
                $results[] = [
                    'invoice_id' => $invoice->id,
                    'status' => 'success',
                    'message' => 'Invoice sent successfully'
                ];
                $successCount++;
            } else {
                $results[] = [
                    'invoice_id' => $invoice->id,
                    'status' => 'error',
                    'message' => 'Failed to send invoice'
                ];
                $failureCount++;
            }
        }

        Log::info('Bulk invoice email sending completed', [
            'total_invoices' => count($invoices),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return $results;
    }

    /**
     * Test email configuration
     */
    public function testEmailConfiguration(User $user): bool
    {
        try {
            Mail::raw('This is a test email to verify your email configuration.', function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Email Configuration Test');
            });

            Log::info('Email configuration test sent', ['user_id' => $user->id]);

            return true;
        } catch (\Exception $e) {
            Log::error('Email configuration test failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get company information from user settings
     */
    private function getCompanyInfo(User $user): object
    {
        $settings = $user->settings ?? null;

        return (object) [
            'name' => $settings->company_name ?? config('app.name'),
            'address' => $settings->company_address ?? null,
            'city' => $settings->company_city ?? null,
            'country' => $settings->company_country ?? null,
            'postal_code' => $settings->company_postal_code ?? null,
            'phone' => $settings->company_phone ?? null,
            'email' => $settings->company_email ?? $user->email,
            'website' => $settings->company_website ?? null,
            'tax_id' => $settings->tax_id ?? null,
        ];
    }

    /**
     * Clean up old PDF files
     */
    public function cleanupOldPdfs(int $daysOld = 30): int
    {
        $deletedCount = 0;

        try {
            // Clean up quote PDFs
            $quotePdfPath = storage_path('app/public/quotes');
            if (is_dir($quotePdfPath)) {
                $files = glob($quotePdfPath . '/*.pdf');
                foreach ($files as $file) {
                    if (filemtime($file) < strtotime("-{$daysOld} days")) {
                        unlink($file);
                        $deletedCount++;
                    }
                }
            }

            // Clean up invoice PDFs
            $invoicePdfPath = storage_path('app/public/invoices');
            if (is_dir($invoicePdfPath)) {
                $files = glob($invoicePdfPath . '/*.pdf');
                foreach ($files as $file) {
                    if (filemtime($file) < strtotime("-{$daysOld} days")) {
                        unlink($file);
                        $deletedCount++;
                    }
                }
            }

            Log::info('PDF cleanup completed', [
                'days_old' => $daysOld,
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('PDF cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $deletedCount;
    }
}