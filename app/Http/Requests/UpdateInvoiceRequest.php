<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'due_date' => ['required', 'date', 'after_or_equal:date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'discount_type' => ['nullable', 'in:percentage,amount'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:draft,sent,paid,overdue,cancelled'],
            'payment_method' => ['nullable', 'in:bank_transfer,cash,check,credit_card,other'],
            'payment_date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'Selected customer is invalid.',
            'title.required' => 'Invoice title is required.',
            'title.max' => 'Invoice title cannot exceed 255 characters.',
            'date.required' => 'Invoice date is required.',
            'date.date' => 'Please provide a valid date.',
            'date.before_or_equal' => 'Invoice date cannot be in the future.',
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Please provide a valid date.',
            'due_date.after_or_equal' => 'Due date must be on or after the invoice date.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'items.required' => 'At least one item is required.',
            'items.array' => 'Items must be provided as an array.',
            'items.min' => 'At least one item is required.',
            'items.*.item_id.required' => 'Item selection is required.',
            'items.*.item_id.exists' => 'Selected item is invalid.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.numeric' => 'Quantity must be a valid number.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.quantity.max' => 'Quantity cannot exceed 999,999.',
            'items.*.unit_price.required' => 'Unit price is required.',
            'items.*.unit_price.numeric' => 'Unit price must be a valid number.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
            'items.*.unit_price.max' => 'Unit price cannot exceed 99,999,999.99.',
            'tax_rate.required' => 'Tax rate is required.',
            'tax_rate.numeric' => 'Tax rate must be a valid number.',
            'tax_rate.min' => 'Tax rate cannot be negative.',
            'tax_rate.max' => 'Tax rate cannot exceed 100%.',
            'discount_type.in' => 'Discount type must be either percentage or amount.',
            'discount_value.numeric' => 'Discount value must be a valid number.',
            'discount_value.min' => 'Discount value cannot be negative.',
            'status.in' => 'Status must be one of: draft, sent, paid, overdue, cancelled.',
            'payment_method.in' => 'Payment method must be one of: bank transfer, cash, check, credit card, other.',
            'payment_date.date' => 'Please provide a valid payment date.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
        ];
    }
}
