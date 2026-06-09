<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * WithdrawalFormRequest
 *
 * Validasi form untuk WithdrawalController@store.
 *
 * Validasi lanjutan (amount <= current_value) dilakukan di Controller
 * karena memerlukan data dari database yang tidak tersedia di FormRequest
 * tanpa menambahkan business logic di layer request (separation of concerns).
 */
class WithdrawalFormRequest extends FormRequest
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
     * Note: min:10000 adalah minimum pencairan Rp 10.000.
     * Ini design decision untuk mencegah withdrawal micro-amounts yang tidak praktis.
     */
    public function rules(): array
    {
        return [
            'amount_requested' => ['required', 'numeric', 'min:10000'],
            'note'             => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom error messages dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'amount_requested.required' => 'Jumlah pencairan wajib diisi.',
            'amount_requested.numeric'  => 'Jumlah pencairan harus berupa angka.',
            'amount_requested.min'      => 'Jumlah pencairan minimal Rp 10.000.',
            'note.max'                  => 'Catatan maksimal 500 karakter.',
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'amount_requested' => 'jumlah pencairan',
            'note'             => 'catatan',
        ];
    }
}
