<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateDishRequest extends FormRequest
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

        // SUPER_ADMIN can update any dish
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            return true;
        }

        // ADMIN can only update dishes from their tenant
        if ($user->hasRole(UserRole::ADMIN->value)) {
            $dish = $this->route('dish') ?? $this->route('dishId');
            if (is_numeric($dish)) {
                $dish = \App\Models\Dish::find($dish);
            }
            return $dish && $dish->tenant_id === $user->tenant_id;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price_base' => 'sometimes|numeric|min:0|max:9999999.99',
            'photo_url' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'allergens' => 'nullable|array',
            'allergens.*' => 'string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'stock_quantity' => 'nullable|integer|min:0|max:9999',
            'preparation_time_minutes' => 'nullable|integer|min:1|max:480',
            'active' => 'sometimes|boolean',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:variants,id',
            'variants.*.name' => 'required_with:variants|string|max:100',
            'variants.*.price_modifier' => 'required_with:variants|numeric|min:-9999|max:9999',
            'options' => 'nullable|array',
            'options.*.id' => 'nullable|integer|exists:options,id',
            'options.*.name' => 'required_with:options|string|max:100',
            'options.*.price' => 'required_with:options|numeric|min:0|max:9999',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'Le nom du plat ne peut pas dépasser 255 caractères.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'price_base.min' => 'Le prix ne peut pas être négatif.',
            'price_base.max' => 'Le prix est trop élevé.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'L\'image doit être au format JPEG, PNG, JPG, GIF ou WebP.',
            'photo.max' => 'L\'image ne peut pas dépasser 5 Mo.',
            'stock_quantity.min' => 'La quantité en stock ne peut pas être négative.',
            'preparation_time_minutes.min' => 'Le temps de préparation doit être d\'au moins 1 minute.',
            'preparation_time_minutes.max' => 'Le temps de préparation ne peut pas dépasser 8 heures.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'catégorie',
            'name' => 'nom',
            'description' => 'description',
            'price_base' => 'prix de base',
            'photo_url' => 'URL de la photo',
            'photo' => 'photo',
            'allergens' => 'allergènes',
            'tags' => 'tags',
            'stock_quantity' => 'quantité en stock',
            'preparation_time_minutes' => 'temps de préparation',
            'variants' => 'variantes',
            'options' => 'options',
        ];
    }
}
