<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\TenantType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
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

        // SUPER_ADMIN can update any tenant
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            return true;
        }

        // ADMIN can update their own tenant
        if ($user->hasRole(UserRole::ADMIN->value)) {
            $tenant = $this->route('tenant');
            if (is_numeric($tenant)) {
                return $user->tenant_id == $tenant;
            }
            return $tenant && $user->tenant_id === $tenant->id;
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
        $tenantId = $this->route('tenant');
        if (!is_numeric($tenantId)) {
            $tenantId = $tenantId->id ?? null;
        }

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('tenants', 'slug')->ignore($tenantId),
            ],
            'type' => [
                'sometimes',
                'string',
                Rule::in(array_column(TenantType::cases(), 'value')),
            ],
            'logo_url' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'cover_url' => 'nullable|string|max:500',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'branding' => 'nullable|array',
            'branding.primary_color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'branding.secondary_color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'branding.accent_color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'branding.heading_font' => 'nullable|string|max:100',
            'branding.body_font' => 'nullable|string|max:100',
            'currency' => 'sometimes|string|size:3|alpha',
            'locale' => 'sometimes|string|max:10',
            'theme_id' => 'nullable|integer|exists:themes,id',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'slug.unique' => 'Cet identifiant est déjà utilisé.',
            'slug.alpha_dash' => 'L\'identifiant ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'type.in' => 'Le type sélectionné n\'est pas valide.',
            'logo.image' => 'Le logo doit être une image.',
            'logo.mimes' => 'Le logo doit être au format JPEG, PNG, JPG, GIF, SVG ou WebP.',
            'logo.max' => 'Le logo ne peut pas dépasser 2 Mo.',
            'cover.image' => 'La couverture doit être une image.',
            'cover.mimes' => 'La couverture doit être au format JPEG, PNG, JPG, GIF ou WebP.',
            'cover.max' => 'La couverture ne peut pas dépasser 5 Mo.',
            'currency.size' => 'La devise doit être un code ISO à 3 lettres (ex: XOF, EUR).',
            'branding.primary_color.regex' => 'La couleur principale doit être au format hexadécimal (#RRGGBB).',
            'branding.secondary_color.regex' => 'La couleur secondaire doit être au format hexadécimal (#RRGGBB).',
            'branding.accent_color.regex' => 'La couleur d\'accent doit être au format hexadécimal (#RRGGBB).',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'slug' => 'identifiant unique',
            'type' => 'type d\'établissement',
            'logo_url' => 'URL du logo',
            'logo' => 'logo',
            'cover_url' => 'URL de la couverture',
            'cover' => 'image de couverture',
            'branding' => 'personnalisation',
            'branding.primary_color' => 'couleur principale',
            'branding.secondary_color' => 'couleur secondaire',
            'branding.accent_color' => 'couleur d\'accent',
            'branding.heading_font' => 'police des titres',
            'branding.body_font' => 'police du texte',
            'currency' => 'devise',
            'locale' => 'langue',
            'theme_id' => 'thème',
        ];
    }
}
