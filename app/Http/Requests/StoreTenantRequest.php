<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\TenantType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        // Only SUPER_ADMIN can create tenants
        return $user && $user->hasRole(UserRole::SUPER_ADMIN->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                'unique:tenants,slug',
            ],
            'type' => [
                'required',
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
            'currency' => 'required|string|size:3|alpha',
            'locale' => 'required|string|max:10',
            'theme_id' => 'nullable|integer|exists:themes,id',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du restaurant est requis.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'slug.required' => 'L\'identifiant unique (slug) est requis.',
            'slug.unique' => 'Cet identifiant est déjà utilisé.',
            'slug.alpha_dash' => 'L\'identifiant ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'type.required' => 'Le type d\'établissement est requis.',
            'type.in' => 'Le type sélectionné n\'est pas valide.',
            'logo.image' => 'Le logo doit être une image.',
            'logo.mimes' => 'Le logo doit être au format JPEG, PNG, JPG, GIF, SVG ou WebP.',
            'logo.max' => 'Le logo ne peut pas dépasser 2 Mo.',
            'cover.image' => 'La couverture doit être une image.',
            'cover.mimes' => 'La couverture doit être au format JPEG, PNG, JPG, GIF ou WebP.',
            'cover.max' => 'La couverture ne peut pas dépasser 5 Mo.',
            'currency.required' => 'La devise est requise.',
            'currency.size' => 'La devise doit être un code ISO à 3 lettres (ex: XOF, EUR).',
            'locale.required' => 'La langue est requise.',
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

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug from name if not provided
        if (!$this->slug && $this->name) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name),
            ]);
        }

        // Set default values
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        if (!$this->currency) {
            $this->merge(['currency' => 'XOF']);
        }

        if (!$this->locale) {
            $this->merge(['locale' => 'fr']);
        }
    }
}
