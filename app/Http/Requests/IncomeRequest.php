<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * IncomeRequest
 *
 * Validates income entry creation and updates.
 *
 * KEY DIFFERENCE from ExpenseRequest:
 * - source_id references income_sources (not categories)
 * - 'note' is nullable (income entries may not have a memo)
 * - No 'description' field — income uses 'note' per schema
 */
class IncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'source_id' => 'required|exists:income_sources,id',
            'amount'    => 'required|numeric|min:100|max:999999999',
            'date'      => 'required|date|before_or_equal:today',
            'note'      => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'source_id.required' => 'Sumber pendapatan wajib dipilih.',
            'source_id.exists'   => 'Sumber pendapatan tidak valid.',
            'amount.required'    => 'Nominal wajib diisi.',
            'amount.numeric'     => 'Nominal harus berupa angka.',
            'amount.min'         => 'Nominal minimal Rp 100.',
            'amount.max'         => 'Nominal maksimal Rp 999.999.999.',
            'date.required'      => 'Tanggal wajib diisi.',
            'date.date'          => 'Format tanggal tidak valid.',
            'date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'note.max'           => 'Catatan maksimal 500 karakter.',
        ];
    }
}
