<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - {{ $tenant->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            border-radius: 8px;
        }
        .logo {
            max-width: 120px;
            margin-bottom: 15px;
            filter: brightness(0) invert(1);
        }
        h1 {
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .tagline {
            font-size: 13px;
            opacity: 0.9;
            font-style: italic;
        }
        .category {
            margin-bottom: 35px;
            page-break-inside: avoid;
        }
        .category-header {
            background: #4f46e5;
            color: white;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .dish {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            page-break-inside: avoid;
            background: #ffffff;
        }
        .dish-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .dish-name {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
            flex: 1;
        }
        .dish-price {
            font-size: 16px;
            font-weight: 700;
            color: #4f46e5;
            white-space: nowrap;
            margin-left: 15px;
        }
        .dish-description {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .dish-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-variant {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-option {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-allergen {
            background: #fee2e2;
            color: #991b1b;
        }
        .variants, .options {
            margin-top: 10px;
            padding: 10px;
            background: #f9fafb;
            border-radius: 4px;
        }
        .variants-title, .options-title {
            font-size: 10px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .variant-item, .option-item {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 3px;
            display: flex;
            justify-content: space-between;
        }
        .variant-price, .option-price {
            font-weight: 600;
            color: #4f46e5;
        }
        .allergens {
            margin-top: 8px;
            padding: 8px 12px;
            background: #fef2f2;
            border-left: 3px solid #dc2626;
            border-radius: 3px;
        }
        .allergens-title {
            font-size: 9px;
            font-weight: 600;
            color: #991b1b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .allergens-list {
            font-size: 10px;
            color: #dc2626;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer-contact {
            margin-top: 8px;
        }
        .no-dishes {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            font-style: italic;
        }
        .unavailable {
            opacity: 0.5;
        }
        .unavailable-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($tenant->logo)
            <img src="{{ public_path('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}" class="logo">
        @endif
        <h1>{{ $menu->name }}</h1>
        @if($menu->description)
            <div class="tagline">{{ $menu->description }}</div>
        @endif
    </div>

    @forelse($menu->categories as $category)
        <div class="category">
            <div class="category-header">
                {{ $category->name }}
            </div>

            @forelse($category->dishes->where('active', true) as $dish)
                <div class="dish">
                    <div class="dish-header">
                        <div class="dish-name">
                            {{ $dish->name }}
                            @if($dish->stock_quantity !== null && $dish->stock_quantity <= 0)
                                <span class="unavailable-badge">Rupture de stock</span>
                            @endif
                        </div>
                        <div class="dish-price">
                            {{ number_format($dish->price_base, 0, ',', ' ') }} FCFA
                        </div>
                    </div>

                    @if($dish->description)
                        <div class="dish-description">
                            {{ $dish->description }}
                        </div>
                    @endif

                    <!-- Variantes -->
                    @if($dish->variants && $dish->variants->count() > 0)
                        <div class="variants">
                            <div class="variants-title">Variantes disponibles</div>
                            @foreach($dish->variants as $variant)
                                <div class="variant-item">
                                    <span>{{ $variant->name }}</span>
                                    <span class="variant-price">
                                        +{{ number_format($variant->price_adjustment, 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Options -->
                    @if($dish->options && $dish->options->count() > 0)
                        <div class="options">
                            <div class="options-title">Options supplémentaires</div>
                            @foreach($dish->options as $option)
                                <div class="option-item">
                                    <span>{{ $option->name }}</span>
                                    <span class="option-price">
                                        +{{ number_format($option->price, 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Allergènes -->
                    @if($dish->allergens && is_array($dish->allergens) && count($dish->allergens) > 0)
                        <div class="allergens">
                            <div class="allergens-title">⚠ Allergènes</div>
                            <div class="allergens-list">
                                {{ implode(', ', $dish->allergens) }}
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="no-dishes">
                    Aucun plat disponible dans cette catégorie
                </div>
            @endforelse
        </div>
    @empty
        <div class="no-dishes">
            Aucune catégorie disponible dans ce menu
        </div>
    @endforelse

    <div class="footer">
        <div>{{ $tenant->name }}</div>
        @if($tenant->address)
            <div class="footer-contact">{{ $tenant->address }}</div>
        @endif
        @if($tenant->phone)
            <div class="footer-contact">Tél : {{ $tenant->phone }}</div>
        @endif
        <div style="margin-top: 15px;">
            Menu imprimé le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>
