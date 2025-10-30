<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_city' => ['nullable', 'string', 'max:100'],
            'company_country' => ['nullable', 'string', 'max:100'],
            'company_postal_code' => ['nullable', 'string', 'max:20'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'default_currency' => ['nullable', 'string', 'in:USD,EUR,GBP,JPY,CAD,AUD,CHF'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.max' => 'Company name cannot exceed 255 characters.',
            'company_address.max' => 'Company address cannot exceed 500 characters.',
            'company_city.max' => 'City cannot exceed 100 characters.',
            'company_country.max' => 'Country cannot exceed 100 characters.',
            'company_postal_code.max' => 'Postal code cannot exceed 20 characters.',
            'company_phone.max' => 'Phone number cannot exceed 50 characters.',
            'company_email.email' => 'Please provide a valid email address.',
            'company_email.max' => 'Email address cannot exceed 255 characters.',
            'company_website.url' => 'Please provide a valid website URL.',
            'company_website.max' => 'Website URL cannot exceed 255 characters.',
            'tax_id.max' => 'Tax ID cannot exceed 50 characters.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be a JPG, JPEG, PNG, or GIF file.',
            'logo.max' => 'Logo cannot exceed 2MB in size.',
            'default_currency.in' => 'Default currency must be one of: USD, EUR, GBP, JPY, CAD, AUD, CHF.',
            'tax_rate.numeric' => 'Tax rate must be a valid number.',
            'tax_rate.min' => 'Tax rate cannot be negative.',
            'tax_rate.max' => 'Tax rate cannot exceed 100%.',
            'payment_terms.max' => 'Payment terms cannot exceed 500 characters.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
