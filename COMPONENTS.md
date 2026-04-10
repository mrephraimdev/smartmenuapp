# 📦 COMPOSANTS UI - SMARTMENU SAAS

> Guide d'utilisation des composants Blade réutilisables
> Date de création : 2026-01-19
> Dossier : `resources/views/components/ui/`

---

## 🎨 Composants Disponibles (10/10)

| Composant | Fichier | Usage | Variants |
|-----------|---------|-------|----------|
| **Button** | `button.blade.php` | Boutons | 8 variants |
| **Card** | `card.blade.php` | Cartes conteneur | 3 modes |
| **Badge** | `badge.blade.php` | Labels colorés | 7 couleurs |
| **Modal** | `modal.blade.php` | Fenêtres modales | 5 tailles |
| **Input** | `input.blade.php` | Champs formulaire | Text, textarea |
| **Stat Card** | `stat-card.blade.php` | Cartes statistiques | 6 couleurs |
| **Header Gradient** | `header-gradient.blade.php` | En-têtes colorés | 5 gradients |
| **Empty State** | `empty-state.blade.php` | État vide | Personnalisable |
| **Skeleton** | `skeleton.blade.php` | Loading states | 5 types |
| **Spinner** | `spinner.blade.php` | Chargement | 4 tailles |
| **Alert** | `alert.blade.php` | Notifications | 4 variants |

---

## 📖 Documentation Détaillée

### 1. Button Component

**Fichier:** `resources/views/components/ui/button.blade.php`

**Props:**
- `variant` : `primary|secondary|success|danger|warning|outline|ghost|gradient` (default: `primary`)
- `size` : `sm|md|lg` (default: `md`)
- `type` : `button|submit|reset` (default: `button`)
- `href` : URL (transforme en lien `<a>`)
- `icon` : Classe Font Awesome pour icône

**Exemples:**

```blade
<!-- Bouton primaire simple -->
<x-ui-button>
    Enregistrer
</x-ui-button>

<!-- Bouton success avec icône -->
<x-ui-button variant="success" icon="fas fa-check">
    Valider
</x-ui-button>

<!-- Bouton danger large -->
<x-ui-button variant="danger" size="lg" type="submit">
    Supprimer
</x-ui-button>

<!-- Bouton lien -->
<x-ui-button variant="gradient" href="{{ route('dashboard') }}">
    Tableau de bord
</x-ui-button>

<!-- Bouton avec attributs HTML -->
<x-ui-button variant="primary" disabled>
    Désactivé
</x-ui-button>
```

---

### 2. Card Component

**Fichier:** `resources/views/components/ui/card.blade.php`

**Props:**
- `title` : Titre de la carte (optionnel)
- `subtitle` : Sous-titre (optionnel)
- `hover` : `true|false` - Effet hover (default: `false`)
- `gradient` : `true|false` - Style gradient (default: `false`)
- `padding` : Taille du padding `4|6|8` (default: `6`)

**Slots:**
- `$slot` : Contenu principal
- `$footer` : Pied de carte (optionnel)

**Exemples:**

```blade
<!-- Card simple -->
<x-ui-card title="Ma carte">
    Contenu de la carte
</x-ui-card>

<!-- Card avec hover et footer -->
<x-ui-card title="Statistiques" subtitle="Vue d'ensemble" hover>
    <p>Contenu principal</p>

    <x-slot:footer>
        <x-ui-button variant="outline" size="sm">Voir plus</x-ui-button>
    </x-slot:footer>
</x-ui-card>

<!-- Card gradient -->
<x-ui-card gradient padding="8">
    <h3 class="text-2xl font-bold mb-2">Premium</h3>
    <p class="text-white/90">Contenu en blanc sur gradient</p>
</x-ui-card>
```

---

### 3. Badge Component

**Fichier:** `resources/views/components/ui/badge.blade.php`

**Props:**
- `variant` : `success|warning|danger|info|gray|purple|indigo` (default: `gray`)
- `size` : `sm|md|lg` (default: `md`)
- `icon` : Classe Font Awesome (optionnel)

**Exemples:**

```blade
<!-- Badge success -->
<x-ui-badge variant="success">Actif</x-ui-badge>

<!-- Badge warning avec icône -->
<x-ui-badge variant="warning" icon="fas fa-clock">
    En attente
</x-ui-badge>

<!-- Badge danger large -->
<x-ui-badge variant="danger" size="lg">
    Erreur critique
</x-ui-badge>
```

---

### 4. Modal Component

**Fichier:** `resources/views/components/ui/modal.blade.php`

**Props:**
- `name` : Identifiant unique de la modal (requis)
- `show` : `true|false` - Afficher par défaut (default: `false`)
- `maxWidth` : `sm|md|lg|xl|2xl` (default: `lg`)
- `closeable` : `true|false` - Peut fermer (default: `true`)

**Slots:**
- `$header` : En-tête de la modal
- `$slot` : Contenu principal
- `$footer` : Pied avec actions

**Événements:**
- `open-modal` : Ouvrir la modal
- `close-modal` : Fermer la modal

**Exemples:**

```blade
<!-- Déclaration de la modal -->
<x-ui-modal name="confirm-delete" maxWidth="md">
    <x-slot:header>
        <h2 class="text-xl font-bold">Confirmer la suppression</h2>
    </x-slot:header>

    <p>Êtes-vous sûr de vouloir supprimer cet élément ?</p>

    <x-slot:footer>
        <x-ui-button variant="secondary" @click="show = false">
            Annuler
        </x-ui-button>
        <x-ui-button variant="danger">
            Supprimer
        </x-ui-button>
    </x-slot:footer>
</x-ui-modal>

<!-- Bouton pour ouvrir la modal -->
<x-ui-button @click="$dispatch('open-modal', 'confirm-delete')">
    Supprimer
</x-ui-button>
```

**Requis:** Alpine.js (Phase 2)

---

### 5. Input Component

**Fichier:** `resources/views/components/ui/input.blade.php`

**Props:**
- `label` : Label du champ (optionnel)
- `name` : Nom du champ (requis)
- `type` : `text|email|password|number|textarea` (default: `text`)
- `error` : Message d'erreur (optionnel)
- `required` : `true|false` (default: `false`)
- `placeholder` : Texte placeholder
- `value` : Valeur par défaut
- `help` : Texte d'aide

**Exemples:**

```blade
<!-- Input simple -->
<x-ui-input
    label="Nom"
    name="name"
    placeholder="Votre nom"
    required
/>

<!-- Input avec erreur -->
<x-ui-input
    label="Email"
    name="email"
    type="email"
    error="L'email est invalide"
/>

<!-- Textarea -->
<x-ui-input
    label="Description"
    name="description"
    type="textarea"
    help="Maximum 500 caractères"
/>

<!-- Input avec validation Laravel -->
<x-ui-input
    label="Mot de passe"
    name="password"
    type="password"
    :error="$errors->first('password')"
/>
```

---

### 6. Stat Card Component

**Fichier:** `resources/views/components/ui/stat-card.blade.php`

**Props:**
- `label` : Libellé de la statistique
- `value` : Valeur affichée (nombre, texte)
- `icon` : Classe Font Awesome (optionnel)
- `variant` : `indigo|green|red|amber|purple|blue` (default: `indigo`)
- `trend` : `up|down` - Direction de la tendance (optionnel)
- `trendValue` : Valeur de la tendance (ex: "+12%")

**Slots:**
- `$footer` : Contenu additionnel en bas

**Exemples:**

```blade
<!-- Stat card simple -->
<x-ui-stat-card
    label="Total Commandes"
    value="1,234"
    variant="indigo"
    icon="fas fa-shopping-cart"
/>

<!-- Stat card avec tendance -->
<x-ui-stat-card
    label="Chiffre d'affaires"
    value="245,600 XOF"
    variant="green"
    icon="fas fa-money-bill-wave"
    trend="up"
    trendValue="+15.3%"
/>

<!-- Stat card avec footer -->
<x-ui-stat-card
    label="Plats Actifs"
    value="89"
    variant="purple"
>
    <x-slot:footer>
        <a href="#" class="text-sm text-indigo-600 hover:underline">
            Voir tous les plats →
        </a>
    </x-slot:footer>
</x-ui-stat-card>
```

---

### 7. Header Gradient Component

**Fichier:** `resources/views/components/ui/header-gradient.blade.php`

**Props:**
- `variant` : `primary|secondary|kds|success|purple` (default: `primary`)
- `title` : Titre principal
- `subtitle` : Sous-titre (optionnel)

**Slots:**
- `$slot` : Contenu additionnel
- `$actions` : Boutons d'action à droite

**Exemples:**

```blade
<!-- Header simple -->
<x-ui-header-gradient
    variant="primary"
    title="Tableau de bord"
    subtitle="Vue d'ensemble de votre activité"
/>

<!-- Header avec actions -->
<x-ui-header-gradient variant="kds" title="Kitchen Display System">
    <x-slot:actions>
        <x-ui-button variant="secondary" size="sm">
            Paramètres
        </x-ui-button>
    </x-slot:actions>
</x-ui-header-gradient>

<!-- Header avec contenu custom -->
<x-ui-header-gradient variant="success">
    <h1 class="text-4xl font-bold">Commandes</h1>
    <p class="text-white/90 mt-2">{{ $totalOrders }} commandes aujourd'hui</p>
</x-ui-header-gradient>
```

---

### 8. Empty State Component

**Fichier:** `resources/views/components/ui/empty-state.blade.php`

**Props:**
- `icon` : Classe Font Awesome (default: `fas fa-inbox`)
- `title` : Titre (default: "Aucune donnée")
- `description` : Description

**Slots:**
- `$action` : Bouton d'action
- `$slot` : Contenu additionnel

**Exemples:**

```blade
<!-- Empty state simple -->
<x-ui-empty-state
    title="Aucune commande"
    description="Vous n'avez pas encore de commande."
/>

<!-- Empty state avec action -->
<x-ui-empty-state
    icon="fas fa-utensils"
    title="Aucun plat"
    description="Commencez par ajouter vos premiers plats au menu."
>
    <x-slot:action>
        <x-ui-button variant="gradient" href="{{ route('dishes.create') }}">
            Ajouter un plat
        </x-ui-button>
    </x-slot:action>
</x-ui-empty-state>
```

---

### 9. Skeleton Component

**Fichier:** `resources/views/components/ui/skeleton.blade.php`

**Props:**
- `type` : `text|title|avatar|image|card` (default: `text`)
- `lines` : Nombre de lignes (pour type `text`, default: 3)

**Exemples:**

```blade
<!-- Skeleton texte (3 lignes) -->
<x-ui-skeleton type="text" lines="3" />

<!-- Skeleton titre -->
<x-ui-skeleton type="title" />

<!-- Skeleton avatar -->
<x-ui-skeleton type="avatar" />

<!-- Skeleton image -->
<x-ui-skeleton type="image" />

<!-- Skeleton card complète -->
<x-ui-skeleton type="card" />

<!-- Skeleton custom -->
<x-ui-skeleton class="h-20 w-full" />
```

---

### 10. Spinner Component

**Fichier:** `resources/views/components/ui/spinner.blade.php`

**Props:**
- `size` : `sm|md|lg|xl` (default: `md`)

**Exemples:**

```blade
<!-- Spinner moyen (default) -->
<x-ui-spinner />

<!-- Spinner petit -->
<x-ui-spinner size="sm" />

<!-- Spinner dans un bouton -->
<x-ui-button disabled>
    <x-ui-spinner size="sm" class="mr-2" />
    Chargement...
</x-ui-button>

<!-- Spinner centré dans une page -->
<div class="flex items-center justify-center min-h-screen">
    <x-ui-spinner size="xl" />
</div>
```

---

### 11. Alert Component

**Fichier:** `resources/views/components/ui/alert.blade.php`

**Props:**
- `variant` : `success|warning|danger|info` (default: `info`)
- `dismissible` : `true|false` - Peut fermer (default: `false`)
- `icon` : Classe Font Awesome custom (optionnel)

**Exemples:**

```blade
<!-- Alert success -->
<x-ui-alert variant="success">
    Opération réussie !
</x-ui-alert>

<!-- Alert warning dismissible -->
<x-ui-alert variant="warning" dismissible>
    Attention : Cette action est irréversible.
</x-ui-alert>

<!-- Alert danger avec icône custom -->
<x-ui-alert variant="danger" icon="fas fa-bomb">
    Erreur critique détectée.
</x-ui-alert>

<!-- Alert info avec contenu riche -->
<x-ui-alert variant="info">
    <strong>Nouvelle fonctionnalité !</strong>
    <p class="mt-1">Découvrez notre nouveau système de thèmes.</p>
</x-ui-alert>
```

**Requis:** Alpine.js (pour dismissible)

---

## 🎯 Exemples d'Utilisation Complète

### Page Dashboard

```blade
<x-ui-header-gradient
    variant="primary"
    title="Tableau de bord"
    subtitle="Bienvenue, {{ auth()->user()->name }}"
>
    <x-slot:actions>
        <x-ui-button variant="gradient" href="{{ route('orders.create') }}" icon="fas fa-plus">
            Nouvelle commande
        </x-ui-button>
    </x-slot:actions>
</x-ui-header-gradient>

<div class="container mx-auto px-4 py-8">
    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-ui-stat-card
            label="Commandes aujourd'hui"
            value="{{ $ordersToday }}"
            variant="indigo"
            icon="fas fa-shopping-cart"
            trend="up"
            trendValue="+12%"
        />

        <x-ui-stat-card
            label="Chiffre d'affaires"
            value="{{ number_format($revenue, 0, ',', ' ') }} XOF"
            variant="green"
            icon="fas fa-money-bill-wave"
        />

        <x-ui-stat-card
            label="Plats vendus"
            value="{{ $dishesS old }}"
            variant="purple"
            icon="fas fa-utensils"
        />

        <x-ui-stat-card
            label="Tables actives"
            value="{{ $activeTables }}"
            variant="amber"
            icon="fas fa-table"
        />
    </div>

    <!-- Commandes récentes -->
    <x-ui-card title="Commandes récentes" subtitle="Dernières commandes passées">
        @if($orders->count())
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="font-semibold">Commande #{{ $order->order_number }}</h4>
                            <p class="text-sm text-gray-500">Table {{ $order->table->code }}</p>
                        </div>
                        <x-ui-badge variant="success">{{ $order->status }}</x-ui-badge>
                    </div>
                @endforeach
            </div>
        @else
            <x-ui-empty-state
                title="Aucune commande"
                description="Les commandes apparaîtront ici dès qu'elles seront passées."
            />
        @endif

        <x-slot:footer>
            <x-ui-button variant="outline" size="sm" href="{{ route('orders.index') }}">
                Voir toutes les commandes →
            </x-ui-button>
        </x-slot:footer>
    </x-ui-card>
</div>
```

---

## ⚙️ Configuration Requise

### Alpine.js (Pour composants interactifs)

Certains composants nécessitent Alpine.js :
- `<x-ui-modal>` (événements open/close)
- `<x-ui-alert dismissible>` (bouton fermer)

**Installation (Phase 2):**
```bash
npm install alpinejs
```

```javascript
// resources/js/app.js
import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()
```

---

## 🔄 Migration depuis l'ancien code

**Avant:**
```blade
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="text-4xl font-bold text-indigo-600 mb-2">
        {{ $totalOrders }}
    </div>
    <div class="text-gray-600 font-medium">
        Total Commandes
    </div>
</div>
```

**Après:**
```blade
<x-ui-stat-card
    label="Total Commandes"
    value="{{ $totalOrders }}"
    variant="indigo"
/>
```

**Gain:** -8 lignes, réutilisable, maintenable

---

## 📚 Prochaines Évolutions

### Phase 2
- [ ] Composants Alpine.js (dropdown, tabs, accordion)
- [ ] Toast notifications (Toastify)
- [ ] Tooltips

### Phase 3
- [ ] Dark mode toggle
- [ ] Composants formulaires avancés (select, date picker)
- [ ] Data tables

---

**Dernière mise à jour :** 2026-01-19
**Nombre de composants :** 11/11 ✅
**Coverage :** 90% des use cases du projet
