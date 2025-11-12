<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('cpf')) {
            $cpf = $this->input('cpf');
            $normalized = preg_replace('/\D/', '', (string) $cpf);
            $this->merge(['cpf' => $normalized === '' ? null : $normalized]);
        }

        if ($this->has('name')) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
        if ($this->has('email')) {
            $this->merge(['email' => $this->input('email') ? trim($this->input('email')) : null]);
        }
        if ($this->has('phone')) {
            $phone = $this->input('phone');
            $normalized = $phone ? preg_replace('/\D/', '', (string) $phone) : null;
            $this->merge(['phone' => $normalized === '' ? null : $normalized]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $tenant = $this->route('tenant');
        $tenantId = $tenant?->getKey();

        return [
            'stay_id' => ['required', 'exists:stays,id'],
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['nullable', 'email', 'max:255', Rule::unique('tenants','email')->ignore($tenantId)],
            'phone'   => ['nullable', 'string', 'max:20'],
            'cpf'     => ['nullable', 'digits:11', Rule::unique('tenants','cpf')->ignore($tenantId)],
        ];
    }
}
