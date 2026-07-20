<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_date'   => ['nullable', 'date'],
            'to_date'     => ['nullable', 'date', 'after_or_equal:from_date'],
            'product_id'  => ['nullable', 'exists:products,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'active_tab'  => ['nullable', 'string'],
        ];
    }
}
