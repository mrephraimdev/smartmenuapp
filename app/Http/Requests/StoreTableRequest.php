<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // SUPER_ADMIN or ADMIN can create tables
        return $user->hasRole(UserRole::SUPER_ADMIN->value) ||
               $user->hasRole(UserRole::ADMIN->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->tenant_id ?? Auth::user()->tenant_id;

        return [
            'tenant_id' => 'required|integer|exists:tenants,id',
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tables', 'code')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                }),
            ],
            'label' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1|max:50',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tenant_id.required' => 'Le restaurant est requis.',
            'tenant_id.exists' => 'Le restaurant sélectionné n\'existe pas.',
            'code.required' => 'Le code de la table est requis.',
            'code.unique' => 'Ce code de table existe déjà pour ce restaurant.',
            'code.max' => 'Le code ne peut pas dépasser 20 caractères.',
            'label.required' => 'Le libellé de la table est requis.',
            'label.max' => 'Le libellé ne peut pas dépasser 100 caractères.',
            'capacity.required' => 'La capacité est requise.',
            'capacity.min' => 'La capacité minimum est de 1 personne.',
            'capacity.max' => 'La capacité maximum est de 50 personnes.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tenant_id' => 'restaurant',
            'code' => 'code',
            'label' => 'libellé',
            'capacity' => 'capacité',
            'is_active' => 'actif',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default tenant_id from authenticated user if not provided
        if (!$this->tenant_id && Auth::check()) {
            $user = Auth::user();
            if ($user->tenant_id) {
                $this->merge(['tenant_id' => $user->tenant_id]);
            }
        }

        // Set default is_active
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
