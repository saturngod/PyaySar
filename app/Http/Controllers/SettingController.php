<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(): View
    {
        $setting =
            auth()->user()->settings ??
            Setting::create(["user_id" => auth()->id()]);

        return view("settings.edit", compact("setting"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $setting =
            auth()->user()->settings ??
            Setting::create(["user_id" => auth()->id()]);

        $validated = $request->validate([
            "company_name" => "nullable|string|max:255",
            "company_email" => "nullable|email|max:255",
            "company_address" => "nullable|string|max:1000",
            "currency" => \App\Helpers\CurrencyHelper::getValidationRule(),
            "default_terms" => "nullable|string|max:2000",
            "default_notes" => "nullable|string|max:2000",
            "company_logo" =>
                "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            "pdf_font_size" => "nullable|integer|min:8|max:20",
            "pdf_font_family" =>
                "nullable|string|in:Arial,Helvetica,Times New Roman",
            "pdf_show_logo" => "nullable|boolean",
            "pdf_show_company_details" => "nullable|boolean",
            "pdf_show_item_description" => "nullable|boolean",
            "pdf_template" =>
                "nullable|string|in:modern,classic,minimal,bold,elegant,corporate,creative,technical,luxury,startup",
        ]);

        // Handle logo upload
        if ($request->hasFile("company_logo")) {
            // Delete old logo if exists
            if ($setting->company_logo) {
                Storage::disk("public")->delete($setting->company_logo);
            }

            $logoPath = $request
                ->file("company_logo")
                ->store("logos", "public");
            $validated["company_logo"] = $logoPath;
        }

        // Prepare PDF settings
        $pdfSettings = [
            "font_size" => $validated["pdf_font_size"] ?? 12,
            "font_family" => $validated["pdf_font_family"] ?? "Arial",
            "show_logo" => $request->boolean("pdf_show_logo", true),
            "show_company_details" => $request->boolean(
                "pdf_show_company_details",
                true,
            ),
            "show_item_description" => $request->boolean(
                "pdf_show_item_description",
                true,
            ),
            "template" => $validated["pdf_template"] ?? "modern",
        ];

        // Remove PDF settings from validated array
        unset($validated["pdf_font_size"], $validated["pdf_font_family"]);
        unset(
            $validated["pdf_show_logo"],
            $validated["pdf_show_company_details"],
        );
        unset($validated["pdf_show_item_description"]);
        unset($validated["pdf_template"]);

        $validated["pdf_settings"] = $pdfSettings;

        $setting->update($validated);

        return redirect()
            ->route("settings.edit")
            ->with("success", "Settings updated successfully.");
    }

    /**
     * Remove the company logo.
     */
    public function removeLogo(): RedirectResponse
    {
        $setting = auth()->user()->settings;

        if ($setting && $setting->company_logo) {
            Storage::disk("public")->delete($setting->company_logo);
            $setting->update(["company_logo" => null]);
        }

        return redirect()
            ->route("settings.edit")
            ->with("success", "Logo removed successfully.");
    }

    /**
     * Generate and download a template preview PDF.
     */
    public function templatePreview(
        Request $request,
        string $template,
    ): \Illuminate\Http\Response {
        $allowedTemplates = [
            "modern",
            "classic",
            "minimal",
            "bold",
            "elegant",
            "corporate",
            "creative",
            "technical",
            "luxury",
            "startup",
        ];

        if (!in_array($template, $allowedTemplates)) {
            abort(404, "Template not found");
        }

        // For now, create a simple HTML preview that can be converted to PDF
        // In a real implementation, you would use a PDF library like mPDF

        $html = $this->generateTemplatePreviewHtml($template);

        // Return HTML for now - this can be converted to PDF later
        return response($html)
            ->header("Content-Type", "text/html")
            ->header(
                "Content-Disposition",
                "inline; filename=\"{$template}-template-preview.html\"",
            );
    }

    /**
     * Generate HTML for template preview.
     */
    private function generateTemplatePreviewHtml(string $template): string
    {
        $sampleData = [
            "company_name" => "Sample Company",
            "company_address" => "123 Business Street, City, State 12345",
            "company_email" => "info@samplecompany.com",
            "company_logo" => true, // Show logo placeholder
            "quote_number" => "Q-2024-001",
            "date" => now()->format("M d, Y"),
            "customer_name" => "John Doe",
            "customer_address" =>
                "456 Client Avenue, Customer City, State 67890",
            "items" => [
                [
                    "name" => "Web Design Services",
                    "description" => "Custom website design and development",
                    "quantity" => 1,
                    "price" => 2500.0,
                ],
                [
                    "name" => "SEO Optimization",
                    "description" => "Search engine optimization setup",
                    "quantity" => 1,
                    "price" => 750.0,
                ],
                [
                    "name" => "Hosting (1 Year)",
                    "description" => "Premium web hosting services",
                    "quantity" => 1,
                    "price" => 300.0,
                ],
            ],
            "subtotal" => 3550.0,
            "tax_rate" => 0.08,
            "tax" => 284.0,
            "total" => 3834.0,
        ];

        return $this->generateInlineTemplateHtml($sampleData, $template);
    }

    /**
     * Generate inline HTML for different template styles.
     */
    private function generateInlineTemplateHtml(
        array $data,
        string $template,
    ): string {
        $templateStyles = \App\Services\PdfTemplateService::getTemplateSpecificStyles(
            $template,
        );

        // Pre-compute header text colors with fallbacks
        $headerTextColor = $templateStyles["headerTextColor"] ?? null;
        $companyNameColor = $headerTextColor ?? $templateStyles["primaryColor"];
        $companyInfoColor = $headerTextColor ?? "#666";
        $invoiceNumberColor =
            $headerTextColor ?? $templateStyles["primaryColor"];
        $invoiceDateColor = $headerTextColor ?? "#666";

        $html =
            "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>" .
            ucfirst($template) .
            " Template Preview</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: {$templateStyles["fontFamily"]}; line-height: 1.6; color: #333; background: #f5f5f5; padding: 20px; }
        .invoice-container { max-width: 800px; margin: 0 auto; background: white; {$templateStyles["container"]}; }
        .template-badge { position: fixed; top: 20px; right: 20px; background: {$templateStyles["primaryColor"]}; color: white; padding: 8px 16px; border-radius: {$templateStyles["borderRadius"]}; font-size: 12px; font-weight: 600; text-transform: uppercase; z-index: 1000; }
        .header { {$templateStyles["header"]}; }
        .company-info h1 { font-size: {$templateStyles["companyNameSize"]}; color: {$companyNameColor}; margin-bottom: 8px; font-weight: {$templateStyles["fontWeight"]}; }
        .company-info p { color: {$companyInfoColor}; margin-bottom: 4px; }
        .invoice-details { text-align: right; }
        .invoice-number { font-size: 24px; font-weight: bold; color: {$invoiceNumberColor}; margin-bottom: 8px; }
        .invoice-date { color: {$invoiceDateColor}; font-size: 14px; }


        .customer-section { margin: 30px 0; }
        .customer-section h3 { font-size: 16px; color: {$templateStyles["primaryColor"]}; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .customer-info { {$templateStyles["customerBox"]}; }
        .items-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
        .items-table th { {$templateStyles["tableHeader"]}; }
        .items-table td { padding: 15px; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        .item-name { font-weight: 600; color: #2c3e50; margin-bottom: 4px; }
        .item-description { color: #666; font-size: 14px; font-style: italic; }
        .totals-section { text-align: right; margin: 30px 0; }
        .totals-row { display: flex; justify-content: flex-end; margin-bottom: 8px; }
        .totals-label { width: 120px; padding-right: 20px; text-align: right; color: #666; font-size: 14px; }
        .totals-value { width: 100px; text-align: right; font-weight: 600; }
        .totals-row.grand-total { border-top: 2px solid {$templateStyles["primaryColor"]}; padding-top: 10px; margin-top: 10px; }
        .totals-row.grand-total .totals-label, .totals-row.grand-total .totals-value { font-size: 18px; font-weight: 700; color: {$templateStyles["primaryColor"]}; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='template-badge'>" .
            ucfirst($template) .
            " Template</div>
    <div class='invoice-container'>
        <div class='header'>
            <div class='company-info'>
                " .
            (!empty($data["company_logo"])
                ? "
                <div class='company-logo'>
                    <div style='width: 60px; height: 60px; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; border-radius: 8px;'>
                        <div style='color: #999; font-size: 24px; font-weight: bold;'>LOGO</div>
                    </div>
                </div>"
                : "") .
            "
                <h1>{$data["company_name"]}</h1>
                <p>{$data["company_address"]}</p>
                <p>{$data["company_email"]}</p>
            </div>
            <div class='invoice-details'>
                <div class='invoice-number'>{$data["quote_number"]}</div>
                <div class='invoice-date'>{$data["date"]}</div>
            </div>
        </div>

        <div class='customer-section'>
            <h3>Bill To:</h3>
            <div class='customer-info'>
                <strong>{$data["customer_name"]}</strong><br>
                {$data["customer_address"]}
            </div>
        </div>

        <table class='items-table'>
            <thead>
                <tr>
                    <th style='width: 50%'>Item</th>
                    <th style='width: 15%'>Quantity</th>
                    <th style='width: 15%'>Price</th>
                    <th style='width: 20%'>Total</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($data["items"] as $item) {
            $html .=
                "
                <tr>
                    <td>
                        <div class='item-name'>{$item["name"]}</div>
                        <div class='item-description'>{$item["description"]}</div>
                    </td>
                    <td>{$item["quantity"]}</td>
                    <td>$" .
                number_format($item["price"], 2) .
                "</td>
                    <td>$" .
                number_format($item["quantity"] * $item["price"], 2) .
                "</td>
                </tr>";
        }

        $html .=
            "
            </tbody>
        </table>

        <div class='totals-section'>
            <div class='totals-row'>
                <div class='totals-label'>Subtotal:</div>
                <div class='totals-value'>$" .
            number_format($data["subtotal"], 2) .
            "</div>
            </div>
            <div class='totals-row'>
                <div class='totals-label'>Tax (" .
            $data["tax_rate"] * 100 .
            "%):</div>
                <div class='totals-value'>$" .
            number_format($data["tax"], 2) .
            "</div>
            </div>
            <div class='totals-row grand-total'>
                <div class='totals-label'>Total:</div>
                <div class='totals-value'>$" .
            number_format($data["total"], 2) .
            "</div>
            </div>
        </div>

        <div class='footer'>
            <p>This is a preview of the " .
            ucfirst($template) .
            " template.</p>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>";

        return $html;
    }

    /**
     * Get template-specific styles.
     */
    private function getTemplateSpecificStyles(string $template): array
    {
        $styles = [
            "modern" => [
                "primaryColor" => "#3498db",
                "borderRadius" => "8px",
                "fontFamily" => "Arial, sans-serif",
                "container" =>
                    "padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);",
                "header" =>
                    "display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 2px solid #e0e0e0;",
                "companyNameSize" => "28px",
                "fontWeight" => "700",
                "customerBox" =>
                    "background: #f8f9fa; padding: 20px; border-radius: 6px; border-left: 4px solid #3498db;",
                "tableHeader" =>
                    "background: #34495e; color: white; padding: 15px; text-align: left; font-weight: 600; font-size: 14px; text-transform: uppercase;",
            ],
            "classic" => [
                "primaryColor" => "#2c3e50",
                "borderRadius" => "0px",
                "fontFamily" => "'Times New Roman', serif",
                "container" => "border: 2px solid #000; padding: 30px;",
                "header" =>
                    "text-align: center; margin-bottom: 40px; border-bottom: 3px double #000; padding-bottom: 20px;",
                "companyNameSize" => "32px",
                "fontWeight" => "bold",
                "customerBox" =>
                    "border: 1px solid #ccc; padding: 15px; background: #f9f9f9;",
                "tableHeader" =>
                    "border: 1px solid #000; padding: 12px; background: #f0f0f0; font-weight: bold;",
            ],
            "minimal" => [
                "primaryColor" => "#000",
                "borderRadius" => "0px",
                "fontFamily" => "'Helvetica Neue', sans-serif",
                "container" => "padding: 60px;",
                "header" =>
                    "margin-bottom: 60px; border-bottom: 1px solid #000; padding-bottom: 20px;",
                "companyNameSize" => "24px",
                "fontWeight" => "300",
                "customerBox" => "padding: 20px 0;",
                "tableHeader" =>
                    "border-bottom: 2px solid #000; padding: 10px 0; font-weight: 500; text-transform: uppercase; font-size: 12px; background: transparent;",
            ],
            "bold" => [
                "primaryColor" => "#e74c3c",
                "borderRadius" => "4px",
                "fontFamily" => "Arial, sans-serif",
                "container" =>
                    "padding: 30px; border-left: 8px solid #e74c3c; box-shadow: 0 4px 20px rgba(231, 76, 60, 0.2);",
                "header" =>
                    "background: #e74c3c; color: white; padding: 30px; margin: -30px -30px 40px -30px;",
                "headerTextColor" => "white",
                "companyNameSize" => "36px",
                "fontWeight" => "900",
                "customerBox" =>
                    "background: #fff5f5; border: 2px solid #e74c3c; padding: 20px;",
                "tableHeader" =>
                    "background: #e74c3c; color: white; padding: 20px; font-size: 16px; font-weight: 800;",
            ],
            "elegant" => [
                "primaryColor" => "#8e44ad",
                "borderRadius" => "12px",
                "fontFamily" => "'Georgia', serif",
                "container" =>
                    "padding: 50px; background: #f8f7f5; border: 1px solid #e8e8e8;",
                "header" =>
                    "text-align: center; margin-bottom: 50px; padding-bottom: 30px;",
                "companyNameSize" => "30px",
                "fontWeight" => "300",
                "customerBox" =>
                    "background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 15px rgba(142, 68, 173, 0.1); border: 1px solid #f0e6f6;",
                "tableHeader" =>
                    "background: #8e44ad; color: white; padding: 18px; font-weight: 500;",
            ],
            "corporate" => [
                "primaryColor" => "#2c3e50",
                "borderRadius" => "4px",
                "fontFamily" => "'Calibri', sans-serif",
                "container" =>
                    "padding: 40px; border: 1px solid #bdc3c7; background: #ffffff;",
                "header" =>
                    "border-bottom: 4px solid #2c3e50; padding-bottom: 20px; margin-bottom: 30px;",
                "companyNameSize" => "26px",
                "fontWeight" => "600",
                "customerBox" =>
                    "background: #ecf0f1; border-left: 4px solid #2c3e50; padding: 20px;",
                "tableHeader" =>
                    "background: #34495e; color: white; padding: 15px; font-weight: 600; text-transform: uppercase;",
            ],
            "creative" => [
                "primaryColor" => "#f39c12",
                "borderRadius" => "20px",
                "fontFamily" => "'Segoe UI', sans-serif",
                "container" =>
                    "padding: 40px; background: #fffdf7; border: 2px dashed #f39c12;",
                "header" =>
                    "text-align: center; margin-bottom: 40px; position: relative;",
                "companyNameSize" => "32px",
                "fontWeight" => "700",
                "customerBox" =>
                    "background: #fff3cd; border-radius: 15px; padding: 25px; border: 2px solid #f39c12;",
                "tableHeader" =>
                    "background: #f39c12; color: white; padding: 18px; font-weight: 600;",
            ],
            "technical" => [
                "primaryColor" => "#16a085",
                "borderRadius" => "0px",
                "fontFamily" => "'Courier New', monospace",
                "container" => "padding: 30px; border: 1px solid #16a085;",
                "header" =>
                    "border: 1px solid #16a085; background: #16a085; color: white; padding: 20px; margin: -30px -30px 30px -30px;",
                "headerTextColor" => "white",
                "companyNameSize" => "24px",
                "fontWeight" => "normal",
                "customerBox" =>
                    "border: 1px solid #bdc3c7; background: #f8f9fa; padding: 15px;",
                "tableHeader" =>
                    "background: #16a085; color: white; padding: 12px; font-family: monospace; text-align: left;",
            ],
            "luxury" => [
                "primaryColor" => "#c0392b",
                "borderRadius" => "0px",
                "fontFamily" => "'Playfair Display', serif",
                "container" =>
                    "padding: 60px; background: #fdfbf7; border: 1px solid #d4af37;",
                "header" =>
                    "text-align: center; margin-bottom: 50px; letter-spacing: 3px; text-transform: uppercase;",
                "companyNameSize" => "28px",
                "fontWeight" => "300",
                "customerBox" =>
                    "background: white; border: 1px solid #d4af37; padding: 30px; text-align: center;",
                "tableHeader" =>
                    "background: #c0392b; color: white; padding: 20px; text-align: center; font-weight: 300; letter-spacing: 2px;",
            ],
            "startup" => [
                "primaryColor" => "#3498db",
                "borderRadius" => "16px",
                "fontFamily" => "'Inter', sans-serif",
                "container" =>
                    "padding: 40px; background: #f8f9fa; border-radius: 16px;",
                "header" =>
                    "display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;",
                "companyNameSize" => "32px",
                "fontWeight" => "700",
                "customerBox" =>
                    "background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);",
                "tableHeader" =>
                    "background: #3498db; color: white; padding: 16px; font-weight: 600;",
            ],
        ];

        return $styles[$template] ?? $styles["modern"];
    }

    /**
     * Update the PDF template for the user.
     */
    public function updateTemplate(
        Request $request,
    ): \Illuminate\Http\JsonResponse {
        $request->validate([
            "template" =>
                "required|string|in:modern,classic,minimal,bold,elegant,corporate,creative,technical,luxury,startup",
        ]);

        $user = auth()->user();
        $setting =
            $user->settings ??
            \App\Models\Setting::create(["user_id" => $user->id]);

        // Update PDF settings with new template
        $pdfSettings = $setting->pdf_settings ?? [];
        $pdfSettings["template"] = $request->template;

        $setting->update(["pdf_settings" => $pdfSettings]);

        return response()->json([
            "success" => true,
            "message" => "Template updated successfully!",
            "template" => $request->template,
        ]);
    }
}
