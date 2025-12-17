<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu - {{ $theme->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family={{ urlencode($theme->getHeadingFont()) }}:wght@400;700&family={{ urlencode($theme->getBodyFont()) }}:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ $theme->getPrimaryColor() }};
            --secondary-color: {{ $theme->getSecondaryColor() }};
            --accent-color: {{ $theme->getAccentColor() }};
            --background-color: {{ $theme->getBackgroundColor() }};
            --text-color: {{ $theme->getTextColor() }};
            --heading-font: '{{ $theme->getHeadingFont() }}', sans-serif;
            --body-font: '{{ $theme->getBodyFont() }}', sans-serif;
        }

        body {
            font-family: var(--body-font);
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .header h1 {
            font-family: var(--heading-font);
            font-size: 2.5rem;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .menu-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .menu-title {
            font-family: var(--heading-font);
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .dish-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .dish-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .dish-name {
            font-family: var(--heading-font);
            font-size: 1.2rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .dish-price {
            font-weight: bold;
            color: var(--accent-color);
            font-size: 1.1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-secondary {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-secondary:hover {
            opacity: 0.9;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: var(--secondary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <a href="{{ route('themes.show', $theme) }}" class="back-btn">← Retour</a>

    <header class="header">
        <h1>🍽️ Restaurant Exemple</h1>
        <p>Aperçu du thème "{{ $theme->name }}"</p>
    </header>

    <div class="container">
        <div class="menu-section">
            <h2 class="menu-title">🍕 Entrées</h2>

            <div class="dish-card">
                <div class="dish-name">Bruschetta Classique</div>
                <p class="dish-description">Pain grillé avec tomates, basilic et huile d'olive</p>
                <div class="dish-price">8,50 €</div>
                <button class="btn-primary" style="margin-top: 1rem;">Ajouter au panier</button>
            </div>

            <div class="dish-card">
                <div class="dish-name">Carpaccio de Saint-Jacques</div>
                <p class="dish-description">Saint-Jacques fraîches, huile d'olive et citron</p>
                <div class="dish-price">12,00 €</div>
                <button class="btn-primary" style="margin-top: 1rem;">Ajouter au panier</button>
            </div>
        </div>

        <div class="menu-section">
            <h2 class="menu-title">🍝 Plats Principaux</h2>

            <div class="dish-card">
                <div class="dish-name">Pâtes aux Fruits de Mer</div>
                <p class="dish-description">Spaghetti avec crevettes, moules et sauce tomate</p>
                <div class="dish-price">18,50 €</div>
                <button class="btn-primary" style="margin-top: 1rem;">Ajouter au panier</button>
            </div>

            <div class="dish-card">
                <div class="dish-name">Entrecôte Grillée</div>
                <p class="dish-description">Entrecôte de bœuf avec frites et salade</p>
                <div class="dish-price">24,00 €</div>
                <button class="btn-primary" style="margin-top: 1rem;">Ajouter au panier</button>
            </div>
        </div>

        <div class="menu-section">
            <h2 class="menu-title">🍰 Desserts</h2>

            <div class="dish-card">
                <div class="dish-name">Tiramisu Maison</div>
                <p class="dish-description">Café, mascarpone et biscuits cuillère</p>
                <div class="dish-price">7,50 €</div>
                <button class="btn-primary" style="margin-top: 1rem;">Ajouter au panier</button>
            </div>

            <div class="dish-card">
                <div class="dish-name">Crème Brûlée</div>
                <p class="dish-description">Crème vanille avec caramel croquant</p>
                <div class="dish-price">6,50 €</div>
                <button class="btn-primary" style="margin-top: 1rem;">Ajouter au panier</button>
            </div>
        </div>

        <div class="menu-section" style="text-align: center;">
            <h2 class="menu-title">Panier</h2>
            <p>Total: 0,00 €</p>
            <button class="btn-secondary" style="margin: 1rem;">Passer commande</button>
        </div>
    </div>
</body>
</html>
