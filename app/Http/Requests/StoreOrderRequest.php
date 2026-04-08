<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Orders can be created by anyone (guests ordering from menu)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenant_id' => 'required|integer|exists:tenants,id',
            'table_id' => 'required|integer|exists:tables,id',
            'items' => 'required|array|min:1',
            'items.*.dish_id' => 'required|integer|exists:dishes,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'items.*.variant_id' => 'nullable|integer|exists:variants,id',
            'items.*.options' => 'nullable|array',
            'items.*.options.*' => 'integer|exists:options,id',
            'items.*.notes' => 'nullable|string|max:500',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'total' => 'required|numeric|min:0',
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
            'table_id.required' => 'La table est requise.',
            'table_id.exists' => 'La table sélectionnée n\'existe pas.',
            'items.required' => 'La commande doit contenir au moins un article.',
            'items.min' => 'La commande doit contenir au moins un article.',
            'items.*.dish_id.required' => 'Chaque article doit avoir un plat.',
            'items.*.dish_id.exists' => 'Un des plats sélectionnés n\'existe pas.',
            'items.*.quantity.required' => 'La quantité est requise pour chaque article.',
            'items.*.quantity.min' => 'La quantité minimum est 1.',
            'items.*.quantity.max' => 'La quantité maximum est 99.',
            'items.*.unit_price.required' => 'Le prix unitaire est requis.',
            'total.required' => 'Le total de la commande est requis.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tenant_id' => 'restaurant',
            'table_id' => 'table',
            'items' => 'articles',
            'items.*.dish_id' => 'plat',
            'items.*.quantity' => 'quantité',
            'items.*.variant_id' => 'variante',
            'items.*.options' => 'options',
            'items.*.notes' => 'notes',
            'items.*.unit_price' => 'prix unitaire',
        ];
    }
}
