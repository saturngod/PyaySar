<?php

namespace App\Services;

use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;
use App\Models\Quote;
use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    private Mpdf $mpdf;

    public function __construct()
    {
        $this->mpdf = new Mpdf([
            "mode" => "utf-8",
            "format" => "A4",
            "default_font_size" => 12,
            "default_font" => "Helvetica",
            "margin_left" => 20,
            "margin_right" => 20,
            "margin_top" => 20,
            "margin_bottom" => 20,
            "margin_header" => 10,
            "margin_footer" => 10,
            "shrink_tables_to_fit" => 1,
            "use_kwt" => true,
            "debug" => false,
            "fontDir" => [storage_path("app/private")],
            "fontdata" => [
                "myanmar" => [
                    "R" => "MyanmarSanpya.ttf",
                    "useOTL" => 0xff,
                    "useKashida" => 75,
                ],
            ],
            "fallbackFonts" => ["myanmar"],
        ]);
    }

    /**
     * Generate PDF for a Quote
     */
    public function generateQuote(Quote $quote): string
    {
        $companyInfo = $this->getCompanyInfo($quote->user);

        // Get user's selected template
        $settings = $quote->user->settings;
        $selectedTemplate = $settings->pdf_settings["template"] ?? "modern";

        $html = view("pdf.quote", [
            "quote" => $quote,
            "company" => $companyInfo,
            "items" => $quote->quoteItems()->with("item")->get(),
            "template" => $selectedTemplate,
            "pdfSettings" => $settings->pdf_settings ?? [],
        ])->render();

        $this->mpdf->WriteHTML($html);

        $filename = "quote-{$quote->quote_number}.pdf";
        $filePath = "quotes/{$filename}";

        Storage::disk("public")->put($filePath, $this->mpdf->Output("", "S"));

        return $filePath;
    }

    /**
     * Generate PDF for an Invoice
     */
    public function generateInvoice(Invoice $invoice): string
    {
        $companyInfo = $this->getCompanyInfo($invoice->user);

        // Get user's selected template
        $settings = $invoice->user->settings;
        $selectedTemplate = $settings->pdf_settings["template"] ?? "modern";

        $html = view("pdf.invoice", [
            "invoice" => $invoice,
            "company" => $companyInfo,
            "items" => $invoice->invoiceItems()->with("item")->get(),
            "template" => $selectedTemplate,
            "pdfSettings" => $settings->pdf_settings ?? [],
        ])->render();

        $this->mpdf->WriteHTML($html);

        $filename = "invoice-{$invoice->invoice_number}.pdf";
        $filePath = "invoices/{$filename}";

        Storage::disk("public")->put($filePath, $this->mpdf->Output("", "S"));

        return $filePath;
    }

    /**
     * Get company information from user settings
     */
    private function getCompanyInfo($user): object
    {
        $settings = $user->settings ?? null;

        return (object) [
            "name" => $settings->company_name ?? config("app.name"),
            "address" => $settings->company_address ?? null,
            "city" => $settings->company_city ?? null,
            "country" => $settings->company_country ?? null,
            "postal_code" => $settings->company_postal_code ?? null,
            "phone" => $settings->company_phone ?? null,
            "email" => $settings->company_email ?? $user->email,
            "website" => $settings->company_website ?? null,
            "tax_id" => $settings->tax_id ?? null,
            "default_terms" =>
                $settings->default_terms ??
                "Payment is due within 30 days of receipt of invoice.",
            "default_notes" => $settings->default_notes ?? "",
            "logo_url" => $this->getLogoAsBase64(
                $settings->company_logo ?? null,
            ),
        ];
    }

    /**
     * Convert company logo to base64 for PDF embedding
     */
    private function getLogoAsBase64($logoPath): ?string
    {
        if (empty($logoPath)) {
            return null;
        }

        try {
            $fullPath = storage_path("app/public/" . $logoPath);

            if (!file_exists($fullPath)) {
                return null;
            }

            // Check file size - limit to 500KB for PDF use
            $fileSize = filesize($fullPath);

            if ($fileSize === false || $fileSize > 500 * 1024) {
                // 500KB limit
                error_log(
                    "Logo file too large for PDF: " .
                        $fileSize .
                        " bytes (limit: 500KB)",
                );
                return null;
            }

            $imageData = file_get_contents($fullPath);

            if ($imageData === false) {
                return null;
            }

            // Get image type from file extension
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $mimeType = match ($extension) {
                "jpg", "jpeg" => "image/jpeg",
                "png" => "image/png",
                "gif" => "image/gif",
                "webp" => "image/webp",
                default => null,
            };

            if ($mimeType === null) {
                return null;
            }

            return "data:" . $mimeType . ";base64," . base64_encode($imageData);
        } catch (\Exception $e) {
            // Log error but don't break PDF generation
            error_log("Error converting logo to base64: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Stream PDF to browser
     */
    public function streamPdf(string $filePath, string $filename)
    {
        $content = Storage::disk("public")->get($filePath);

        return response($content)
            ->header("Content-Type", "application/pdf")
            ->header(
                "Content-Disposition",
                'inline; filename="' . $filename . '"',
            )
            ->header("Cache-Control", "private, max-age=0, must-revalidate")
            ->header("Pragma", "public")
            ->header("Expires", "0");
    }

    /**
     * Download PDF
     */
    public function downloadPdf(string $filePath, string $filename)
    {
        $content = Storage::disk("public")->get($filePath);

        return response($content)
            ->header("Content-Type", "application/pdf")
            ->header(
                "Content-Disposition",
                'attachment; filename="' . $filename . '"',
            )
            ->header("Cache-Control", "private, max-age=0, must-revalidate")
            ->header("Pragma", "public")
            ->header("Expires", "0");
    }

    /**
     * Delete PDF file
     */
    public function deletePdf(string $filePath): bool
    {
        return Storage::disk("public")->delete($filePath);
    }
}
