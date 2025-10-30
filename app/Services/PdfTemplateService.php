<?php

namespace App\Services;

class PdfTemplateService
{
    /**
     * Get template-specific styles for PDF generation
     */
    public static function getTemplateSpecificStyles(
        string $template = "modern",
    ): array {
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
                "headerTextColor" => "#333333",
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
                "headerTextColor" => "#000000",
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
                "headerTextColor" => "#000000",
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
                "headerTextColor" => "#8e44ad",
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
                "headerTextColor" => "#2c3e50",
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
                "headerTextColor" => "#f39c12",
                "customerBox" =>
                    "background: #fff3cd; border-radius: 15px; padding: 25px; border: 2px solid #f39c12;",
                "tableHeader" =>
                    "background: #f39c12; color: white; padding: 18px; border-radius: 10px; font-weight: 600;",
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
                "headerTextColor" => "#c0392b",
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
                "headerTextColor" => "#3498db",
                "customerBox" =>
                    "background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);",
                "tableHeader" =>
                    "background: #3498db; color: white; padding: 16px; font-weight: 600;",
            ],
        ];

        return $styles[$template] ?? $styles["modern"];
    }

    /**
     * Generate complete CSS for a given template
     */
    public static function generateTemplateCSS(
        string $template = "modern",
    ): string {
        $styles = self::getTemplateSpecificStyles($template);

        return "
        body {
            font-family: {$styles["fontFamily"]}, myanmar, sans-serif;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
            margin: 0;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            {$styles["container"]}
        }

        .header {
            {$styles["header"]}
        }

        .company-info h1 {
            font-size: {$styles["companyNameSize"]};
            color: {$styles["headerTextColor"]};
            margin-bottom: 8px;
            font-weight: {$styles["fontWeight"]};
        }

        .company-info p {
            color: {$styles["headerTextColor"]};
            margin-bottom: 4px;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-number {
            font-size: 24px;
            font-weight: bold;
            color: {$styles["headerTextColor"]};
            margin-bottom: 8px;
        }

        .invoice-date {
            color: {$styles["headerTextColor"]};
            font-size: 14px;
        }

        .customer-section {
            margin: 30px 0;
        }

        .customer-section h3 {
            font-size: 16px;
            color: {$styles["primaryColor"]};
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .customer-info {
            {$styles["customerBox"]}
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            font-size: 12px;
        }

        .items-table th {
            {$styles["tableHeader"]}
            font-size: 12px !important;
            padding: 12px !important;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
            font-size: 12px;
        }

        .item-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 4px;
            font-size: 12px;
        }

        .item-description {
            color: #666;
            font-size: 11px;
            font-style: italic;
        }

        .totals-section {
            margin: 30px 0;
        }

        .totals-table {
            width: 100%;
            max-width: 300px;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 14px;
        }

        .totals-table td {
            padding: 4px 0;
            vertical-align: middle;
            white-space: nowrap;
        }

        .totals-label {
            text-align: right;
            padding-right: 20px;
            color: #666;
            font-weight: normal;
            white-space: nowrap;
        }

        .totals-value {
            text-align: right;
            font-weight: 600;
            min-width: 100px;
            white-space: nowrap;
        }

        .totals-table .grand-total td {
            border-top: 2px solid {$styles["primaryColor"]};
            padding-top: 10px;
            margin-top: 10px;
        }

        .totals-table .grand-total .totals-label,
        .totals-table .grand-total .totals-value {
            font-size: 18px;
            font-weight: 700;
            color: {$styles["primaryColor"]};
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        .company-logo {
            max-width: 120px;
            max-height: 60px;
            margin-bottom: 10px;
        }
        ";
    }

    /**
     * Get all available templates
     */
    public static function getAvailableTemplates(): array
    {
        return [
            "modern" => "Modern",
            "classic" => "Classic",
            "minimal" => "Minimal",
            "bold" => "Bold",
            "elegant" => "Elegant",
            "corporate" => "Corporate",
            "creative" => "Creative",
            "technical" => "Technical",
            "luxury" => "Luxury",
            "startup" => "Startup",
        ];
    }
}
