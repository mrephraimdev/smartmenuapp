# MAPPING FONT AWESOME → HEROICONS

> Guide de migration des icônes
> Date : 2026-01-19
> Status : **MIGRATION COMPLETE**

---

## MAPPING COMPLET

| Font Awesome | Heroicons | Usage |
|--------------|-----------|-------|
| `fas fa-home` | `@svg('heroicon-o-home')` | Navigation |
| `fas fa-arrow-right` | `@svg('heroicon-o-arrow-right')` | Navigation |
| `fas fa-arrow-left` | `@svg('heroicon-o-arrow-left')` | Navigation |
| `fas fa-arrow-up` | `@svg('heroicon-o-arrow-up')` | Stats |
| `fas fa-arrow-down` | `@svg('heroicon-o-arrow-down')` | Stats |
| `fas fa-utensils` | `@svg('heroicon-o-cake')` | Restaurant |
| `fas fa-fire` | `@svg('heroicon-o-fire')` | KDS |
| `fas fa-table` | `@svg('heroicon-o-table-cells')` | Tables |
| `fas fa-qrcode` | `@svg('heroicon-o-qr-code')` | QR Code |
| `fas fa-plus` | `@svg('heroicon-o-plus')` | Actions |
| `fas fa-trash` | `@svg('heroicon-o-trash')` | Actions |
| `fas fa-edit` | `@svg('heroicon-o-pencil')` | Actions |
| `fas fa-check` | `@svg('heroicon-o-check')` | Validation |
| `fas fa-times` | `@svg('heroicon-o-x-mark')` | Fermer |
| `fas fa-clock` | `@svg('heroicon-o-clock')` | Temps |
| `fas fa-bolt` | `@svg('heroicon-o-bolt')` | Vitesse |
| `fas fa-chart-line` | `@svg('heroicon-o-chart-bar')` | Stats |
| `fas fa-shopping-cart` | `@svg('heroicon-o-shopping-cart')` | Commandes |
| `fas fa-money-bill-wave` | `@svg('heroicon-o-banknotes')` | Argent |
| `fas fa-inbox` | `@svg('heroicon-o-inbox')` | Empty state |
| `fas fa-check-circle` | `@svg('heroicon-o-check-circle')` | Success |
| `fas fa-exclamation-circle` | `@svg('heroicon-o-exclamation-circle')` | Erreur |
| `fas fa-exclamation-triangle` | `@svg('heroicon-o-exclamation-triangle')` | Warning |
| `fas fa-info-circle` | `@svg('heroicon-o-information-circle')` | Info |
| `fas fa-user` | `@svg('heroicon-o-user')` | Utilisateur |
| `fas fa-users` | `@svg('heroicon-o-users')` | Utilisateurs |
| `fas fa-cog` | `@svg('heroicon-o-cog-6-tooth')` | Paramètres |
| `fas fa-search` | `@svg('heroicon-o-magnifying-glass')` | Recherche |
| `fas fa-filter` | `@svg('heroicon-o-funnel')` | Filtrer |
| `fas fa-download` | `@svg('heroicon-o-arrow-down-tray')` | Télécharger |
| `fas fa-upload` | `@svg('heroicon-o-arrow-up-tray')` | Upload |
| `fas fa-bars` | `@svg('heroicon-o-bars-3')` | Menu |
| `fas fa-ellipsis-v` | `@svg('heroicon-o-ellipsis-vertical')` | More |
| `fas fa-store` | `@svg('heroicon-o-building-storefront')` | Restaurant |
| `fas fa-shield-alt` | `@svg('heroicon-o-shield-check')` | Sécurité |
| `fas fa-print` | `@svg('heroicon-o-printer')` | Imprimer |
| `fas fa-external-link-alt` | `@svg('heroicon-o-arrow-top-right-on-square')` | Lien externe |
| `fas fa-save` | `@svg('heroicon-o-check')` | Sauvegarder |
| `fas fa-receipt` | `@svg('heroicon-o-document-text')` | Reçu |
| `fas fa-hashtag` | `@svg('heroicon-o-hashtag')` | Numéro |
| `fas fa-ban` | `@svg('heroicon-o-no-symbol')` | Interdit |
| `fas fa-history` | `@svg('heroicon-o-clock')` | Historique |
| `fas fa-bell` | `@svg('heroicon-o-bell')` | Notification |

---

## VARIANTS HEROICONS

Heroicons propose 3 variants :
- **Outline** (`heroicon-o-*`) : Par défaut, stroke 1.5px
- **Solid** (`heroicon-s-*`) : Rempli, pour petites tailles
- **Mini** (`heroicon-m-*`) : 20x20px, très petit

**Recommandation :** Utiliser **Outline** partout sauf badges/petits boutons (Solid)

---

## SYNTAXE

### Basique
```blade
<!-- Font Awesome -->
<i class="fas fa-home"></i>

<!-- Heroicons -->
@svg('heroicon-o-home', 'w-6 h-6')
```

### Avec Classes Tailwind
```blade
@svg('heroicon-o-check', 'w-5 h-5 text-green-500')
```

### Composant Blade
```blade
<x-heroicon-o-home class="w-6 h-6 text-gray-600" />
```

### Dans composants
```blade
<!-- Button avec icône -->
<x-ui-button>
    @svg('heroicon-o-plus', 'w-5 h-5 mr-2')
    Ajouter
</x-ui-button>
```

---

## TAILLES RECOMMANDÉES

| Usage | Taille | Classes |
|-------|--------|---------|
| Icône dans texte | 16px | `w-4 h-4` |
| Icône bouton | 20px | `w-5 h-5` |
| Icône normale | 24px | `w-6 h-6` |
| Icône grande | 32px | `w-8 h-8` |
| Icône hero | 48px+ | `w-12 h-12` |

---

## CHECKLIST MIGRATION

- [x] Installer blade-ui-kit/blade-heroicons
- [x] Mettre à jour composants UI (alert, modal)
- [x] Migrer welcome.blade.php
- [x] Migrer home.blade.php
- [x] Migrer kds.blade.php
- [x] Migrer menu-client.blade.php
- [x] Migrer admin/*.blade.php
- [x] Migrer superadmin/*.blade.php
- [x] Migrer tenants/*.blade.php
- [x] Migrer users/*.blade.php
- [x] Migrer qrcode.blade.php
- [x] Supprimer CDN Font Awesome
- [x] Tester build (npm run build)

---

## GAINS

| Métrique | Avant | Après |
|----------|-------|-------|
| CDN externe | 1 requête (~70KB) | 0 |
| Type d'icônes | Font (CSS) | SVG inline |
| Tree-shaking | Non | Oui |
| Accessibilité | Limitée | Améliorée |
