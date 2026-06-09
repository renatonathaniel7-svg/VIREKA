<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * InvestmentRequest
 *
 * Validasi form untuk InvestmentController@store dan @update.
 *
 * Design Note:
 *   - initial_amount hanya required saat CREATE (action=create)
 *   - Pada UPDATE, initial_amount tidak boleh ada di request (ignored di controller)
 *   - current_value boleh null saat create (akan default ke initial_amount)
 *   - return_pct dihitung otomatis, tidak perlu input dari user
 *   - allocation_type: 'saving' (tabungan konvensional) vs 'investment' (instrumen investasi)
 */
class InvestmentRequest extends FormRequest
{
    /**
     * Authorize hanya untuk user yang sudah login.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation rules.
     *
     * Menggunakan required_if untuk initial_amount:
     * Hanya required saat action=create (create form mengirim hidden field action=create).
     * Pada edit form, initial_amount tidak ada dan tidak boleh diubah.
     */
    public function rules(): array
    {
        return [
            'allocation_type' => ['required', 'in:saving,investment'],
            'instrument'      => ['nullable', 'string', 'max:100'],
            'initial_amount'  => ['required_if:action,create', 'nullable', 'numeric', 'min:10000'],
            'current_value'   => ['nullable', 'numeric', 'min:0'],
            'note'            => ['nullable', 'string', 'max:500'],
            // 'action' adalah hidden field helper untuk conditional validation
            'action'          => ['nullable', 'in:create,update'],
        ];
    }

    /**
     * Custom error messages dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'allocation_type.required' => 'Jenis alokasi wajib dipilih.',
            'allocation_type.in'       => 'Jenis alokasi harus saving atau investment.',
            'instrument.max'           => 'Nama instrumen maksimal 100 karakter.',
            'initial_amount.required_if' => 'Jumlah awal wajib diisi saat menambah investasi baru.',
            'initial_amount.numeric'   => 'Jumlah awal harus berupa angka.',
            'initial_amount.min'       => 'Jumlah awal minimal Rp 10.000.',
            'current_value.numeric'    => 'Nilai saat ini harus berupa angka.',
            'current_value.min'        => 'Nilai saat ini tidak boleh negatif.',
            'note.max'                 => 'Catatan maksimal 500 karakter.',
        ];
    }

    /**
     * Custom attribute names untuk pesan error yang lebih ramah.
     */
    public function attributes(): array
    {
        return [
            'allocation_type' => 'jenis alokasi',
            'instrument'      => 'instrumen investasi',
            'initial_amount'  => 'jumlah modal awal',
            'current_value'   => 'nilai saat ini',
            'note'            => 'catatan',
        ];
    }
}
