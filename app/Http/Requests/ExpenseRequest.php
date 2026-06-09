<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ExpenseRequest
 *
 * Centralizes validation logic for expense create/update.
 *
 * WHY FormRequest instead of controller validation?
 * - Keeps controllers thin (SRP)
 * - Reusable across store() and update()
 * - authorize() enforces auth check at the request layer
 *
 * WHY date before_or_equal:today?
 * FinTrack tracks actual spending. Future-dated expenses would corrupt
 * the daily behavioral engine calculations — you can't have "spent"
 * money on a date that hasn't happened yet.
 */
class ExpenseRequest extends FormRequest
{
    /**
     * Only authenticated users may submit expense forms.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation rules for expense data.
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'amount'      => 'required|numeric|min:100|max:999999999',
            'description' => 'required|string|max:255',
            'date'        => 'required|date|before_or_equal:today',
        ];
    }

    /**
     * Human-readable validation messages in Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Kategori tidak valid.',
            'amount.required'      => 'Nominal wajib diisi.',
            'amount.numeric'       => 'Nominal harus berupa angka.',
            'amount.min'           => 'Nominal minimal Rp 100.',
            'amount.max'           => 'Nominal maksimal Rp 999.999.999.',
            'description.required' => 'Deskripsi wajib diisi.',
            'description.max'      => 'Deskripsi maksimal 255 karakter.',
            'date.required'        => 'Tanggal wajib diisi.',
            'date.date'            => 'Format tanggal tidak valid.',
            'date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
        ];
    }
}
