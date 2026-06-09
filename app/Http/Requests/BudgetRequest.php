<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * BudgetRequest
 *
 * Validates budget creation and daily_limit updates.
 *
 * WHY min:1000 for daily_limit?
 * A daily limit below Rp 1.000 is practically meaningless in the
 * Indonesian cost-of-living context. It would also cause division-by-zero
 * edge cases in percentage calculations if someone entered 0.
 */
class BudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'daily_limit' => 'required|numeric|min:1000|max:99999999',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'  => 'Kategori wajib dipilih.',
            'category_id.exists'    => 'Kategori tidak valid.',
            'daily_limit.required'  => 'Limit harian wajib diisi.',
            'daily_limit.numeric'   => 'Limit harian harus berupa angka.',
            'daily_limit.min'       => 'Limit harian minimal Rp 1.000.',
            'daily_limit.max'       => 'Limit harian maksimal Rp 99.999.999.',
        ];
    }
}
