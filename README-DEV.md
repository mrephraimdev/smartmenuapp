# SmartMenu - Guide Développeur

Guide technique complet pour les développeurs travaillant sur SmartMenu.

## Table des matières

- [Architecture](#architecture)
- [Installation](#installation)
- [Structure du projet](#structure-du-projet)
- [Multi-tenancy](#multi-tenancy)
- [Services](#services)
- [API](#api)
- [Cache](#cache)
- [Tests](#tests)
- [Déploiement](#déploiement)

---

## Architecture

### Stack technique

| Composant | Technologie | Version |
|-----------|-------------|---------|
| Backend | Laravel | 11.x |
| Frontend | Alpine.js + Tailwind CSS | 3.x |
| Base de données | PostgreSQL/MySQL | 15+ / 8+ |
| Cache/Session | Redis (prod) / Database (dev) | 7+ |
| Build | Vite | 5.x |

### Patterns utilisés

- **Service Layer**: Logique métier isolée dans `app/Services/`
- **Repository Pattern** (implicite via Eloquent)
- **Observer Pattern**: Audit logging automatique
- **Trait Pattern**: `TenantScope` pour l'isolation multi-tenant
- **Enum Pattern**: Statuts typés (`OrderStatus`, `UserRole`)

---

## Installation

### Prérequis

- PHP 8.2+
- Composer 2.x
- Node.js 18+
- PostgreSQL 15+ ou MySQL 8+
- Redis 7+ (recommandé pour production)

### Installation locale

```bash
# Cloner le repo
git clone <repo-url>
cd menu-qr-app

# Installer les dépendances
composer install
npm install

# Configurer l'environnement
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate --seed

# Build assets
npm run dev

# Démarrer le serveur
php artisan serve
```

### Installation Docker

```bash
# Démarrer la stack
docker-compose up -d

# Exécuter les migrations
docker-compose exec app php artisan migrate --seed
```

---

## Structure du projet

```
app/
├── Console/           # Commandes Artisan
├── Enums/             # Énumérations (OrderStatus, UserRole)
├── Events/            # Événements (OrderCreated, etc.)
├── Exports/           # Classes d'export (Excel, PDF)
├── Http/
│   ├── Controllers/   # Contrôleurs (Admin, API, Public)
│   ├── Middleware/    # Middlewares (CheckRole, SecurityHeaders)
│   ├── Requests/      # Form Requests (validation)
│   └── Resources/     # API Resources (transformation)
├── Models/            # Modèles Eloquent
├── Observers/         # Observers (audit logging)
├── Services/          # Services métier
└── Traits/            # Traits réutilisables (TenantScope)
```

---

## Multi-tenancy

### Concept

Chaque restaurant est un "tenant" isolé. Les données sont filtrées automatiquement par `tenant_id`.

### Implémentation

```php
// Trait appliqué aux modèles
use App\Traits\TenantScope;

class Order extends Model
{
    use TenantScope;
}

// Le trait ajoute automatiquement:
// - Global scope filtrant par tenant_id de l'utilisateur connecté
// - Assignation automatique du tenant_id à la création
```

### Modèles avec TenantScope

- `Order`, `OrderItem`
- `Menu`, `Category`, `Dish`
- `Table`, `Reservation`
- `WaiterCall`, `Review`
- `Payment`, `PosSession`

---

## Services

### OrderService

Gestion du cycle de vie des commandes.

```php
use App\Services\OrderService;

$orderService = app(OrderService::class);

// Créer une commande
$order = $orderService->createOrder([
    'tenant_id' => 1,
    'table_id' => 5,
    'items' => [
        ['dish_id' => 1, 'quantity' => 2, 'variant_id' => null],
    ],
    'notes' => 'Sans oignons'
]);

// Faire progresser le statut
$orderService->progressStatus($order); // RECU → PREP

// Annuler
$orderService->cancelOrder($order, 'Client absent');
```

### StatisticsService

Calcul des statistiques et rapports.

```php
use App\Services\StatisticsService;

$stats = app(StatisticsService::class);
$data = $stats->getDashboardStats($tenantId);
```

### QrCodeService

Génération des QR codes.

```php
use App\Services\QrCodeService;

$qrService = app(QrCodeService::class);
$qrCode = $qrService->generate($tenantId, $tableCode);
```

---

## API

### Endpoints publics (sans auth)

| Méthode | URL | Description |
|---------|-----|-------------|
| GET | `/api/menu?tenant={id}&table={code}` | Récupérer le menu |
| POST | `/api/orders` | Créer une commande |
| GET | `/api/orders/{id}` | Suivi commande client |
| POST | `/api/waiter-calls` | Appeler un serveur |

### Endpoints authentifiés

| Méthode | URL | Description | Rôles |
|---------|-----|-------------|-------|
| GET | `/api/orders/tenant/{slug}` | Commandes du tenant | Auth |
| PATCH | `/api/orders/{id}/status` | Changer statut | Auth |
| GET | `/api/waiter-calls` | Liste des appels | Auth |

### Rate Limiting

| Endpoint | Limite |
|----------|--------|
| `/api/orders` (POST) | 5/min par IP+table |
| `/api/waiter-calls` (POST) | 3/min par IP+table |
| Réservations | 3/heure par IP |
| Avis | 2/jour par IP |

---

## Cache

### Clés de cache

| Clé | Durée | Description |
|-----|-------|-------------|
| `dashboard_full_{tenantId}` | 5 min | Données dashboard |
| `dashboard_stats_{tenantId}` | 2 min | Statistiques rapides |
| `menu_client_{tenantId}` | 5 min | Menu public |
| `statistics_{tenantId}` | 5 min | Page statistiques |
| `chart_data_{tenantId}_{period}` | 1 min | Données graphiques |

### Invalidation

Le cache est automatiquement invalidé lors des modifications via `AdminMenuController::invalidateTenantCache()`.

### Configuration Redis (production)

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

---

## Tests

### Lancer les tests

```bash
# Tous les tests
php artisan test

# Avec couverture
php artisan test --coverage

# Tests spécifiques
php artisan test --filter=OrderFlowTest
```

### Structure des tests

```
tests/
├── Feature/
│   ├── AuthenticationTest.php
│   ├── MenuCrudTest.php
│   ├── OrderFlowTest.php
│   ├── MultiTenancyIsolationTest.php
│   └── ...
└── Unit/
    ├── OrderServiceTest.php
    ├── StatisticsServiceTest.php
    └── ...
```

### Factories disponibles

- `TenantFactory`, `UserFactory`
- `MenuFactory`, `CategoryFactory`, `DishFactory`
- `OrderFactory`, `OrderItemFactory`
- `TableFactory`, `ReservationFactory`

---

## Déploiement

### Checklist production

```bash
# 1. Optimisation
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 2. Migrations
php artisan migrate --force

# 3. Assets
npm run build

# 4. Permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Variables d'environnement critiques

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Sécurité
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true

# Performance
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Monitoring recommandé

- **Sentry**: Tracking des erreurs
- **Laravel Telescope**: Debug (staging only)
- **Redis Insight**: Monitoring Redis
- **pg_stat_statements**: Requêtes lentes PostgreSQL

---

## Conventions de code

### Nommage

- **Controllers**: `PascalCase` + `Controller` suffix
- **Services**: `PascalCase` + `Service` suffix
- **Models**: `PascalCase` singulier
- **Tables DB**: `snake_case` pluriel
- **Colonnes DB**: `snake_case`

### Commits

Format: `type(scope): description`

```
feat(orders): add order cancellation with stock restore
fix(auth): resolve session timeout issue
docs(readme): update installation guide
```

### PHPStan

```bash
# Analyser le code
./vendor/bin/phpstan analyse

# Niveau actuel: 5
# Objectif: 9
```

### Laravel Pint

```bash
# Formater le code
./vendor/bin/pint
```

---

## Contacts

- **Issues**: GitHub Issues
- **Documentation API**: `/api/documentation` (si Swagger configuré)
