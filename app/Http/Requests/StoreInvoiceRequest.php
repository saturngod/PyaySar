<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $invoiceId = $this->route('invoice') ? $this->route('invoice')->id : null;

        return [
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number,'.$invoiceId,
            'customer_id' => 'required|exists:customers,id',
            'open_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:open_date',
            'status' => 'required|in:Draft,Sent,Received,Reject',
            'currency' => 'required|in:USD,MMK',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'bank_account_info' => 'nullable|string',
        ];
    }
}
