<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Normalize CPF to digits-only so validation/uniqueness is consistent.
     */
    protected function prepareForValidation()
    {
        if ($this->has('cpf')) {
            $cpf = $this->input('cpf');
            $normalized = preg_replace('/\D/', '', (string) $cpf);
            $this->merge(['cpf' => $normalized === '' ? null : $normalized]);
        }

        // Optionally trim strings
        if ($this->has('name')) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
        if ($this->has('email')) {
            $this->merge(['email' => $this->input('email') ? trim($this->input('email')) : null]);
        }
        // Normalize phone to digits-only
        if ($this->has('phone')) {
            $phone = $this->input('phone');
            $normalized = $phone ? preg_replace('/\D/', '', (string) $phone) : null;
            $this->merge(['phone' => $normalized === '' ? null : $normalized]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     * For create operations; if you use this for update, adjust unique rules accordingly.
     *
     * CPF is validated as 11 digits after normalization.
     */
    public function rules(): array
    {
        return [
            'stay_id' => ['required', 'exists:stays,id'],
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['nullable', 'email', 'max:255', 'unique:tenants,email'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'cpf'     => ['nullable', 'digits:11', 'unique:tenants,cpf'],
        ];
    }
}
