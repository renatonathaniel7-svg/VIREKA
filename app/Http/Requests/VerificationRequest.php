<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerificationRequest extends FormRequest
{

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'screenshot' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    /**
     * Custom error messages dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'screenshot.required' => 'File screenshot wajib diupload.',
            'screenshot.file'     => 'Input harus berupa file yang diupload.',
            'screenshot.mimes'    => 'Format file harus JPG, PNG, atau PDF.',
            'screenshot.max'      => 'Ukuran file maksimal 5MB.',
        ];
    }
}
