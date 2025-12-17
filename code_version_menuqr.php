<?php
/**
 * Platforme Menu QR & Commande - Version CODE (PHP/Laravel Architecture)
 * Ce fichier contient uniquement la partie développement "Code" extraite du cahier des charges.
 * Lisible dans VS Code.
 */

/* -------------------------------------------------------------------------- */
/*                                1. STACK CODE                               */
/* -------------------------------------------------------------------------- */

// Backend : PHP Laravel
// Frontend : Vue.js ou React
// DB : MySQL ou PostgreSQL
// Realtime : WebSockets (Pusher/Laravel Echo) ou SSE
// Storage : S3/MinIO
// Infra : Docker, Nginx, Redis (cache/queues), CI/CD

/* -------------------------------------------------------------------------- */
/*                         2. ARCHITECTURE MULTI‑TENANTS                      */
/* -------------------------------------------------------------------------- */

// Stratégie : Single DB avec tenant_id sur chaque table
// Middleware d’isolation par sous‑domaine (clientX.domain.com) ou via QR signé

/* Exemple de Middleware Laravel (multi‑tenant) */

// namespace App\Http\Middleware;
// class TenantMiddleware {
//     public function handle($request, Closure $next) {
//         $tenant = $this->resolveTenantFromDomain($request->getHost());
//         app()->instance('tenant', $tenant);
//         return $next($request);
//     }
// }

/* -------------------------------------------------------------------------- */
/*                            3. SCHÉMA DE DONNÉES SQL                        */
/* -------------------------------------------------------------------------- */

// Table: tenants
// id | name | brandingJSON | locale | currency | created_at | updated_at

// Table: users
// id | tenantId | name | email | role (SUPER_ADMIN, ADMIN, CHEF, SERVEUR) | ...

// Table: tables
// id | tenantId | code | label

// Table: menus
// id | tenantId | title | active

// Table: categories
// id | menuId | name | sort

// Table: dishes
// id | categoryId | name | description | priceBase | photoURL | active

// Table: variants
// id | dishId | name | extra

// Table: options
// id | dishId | name | kind | extra

// Table: orders
// id | tenantId | tableId | status | total | createdAt | updatedAt

// Table: order_items
// id | orderId | dishId | variantId | optionsJSON | qty | unitPrice

/* -------------------------------------------------------------------------- */
/*                          4. ENDPOINTS API REST                             */
/* -------------------------------------------------------------------------- */

// GET /api/menu?tenant=xyz&table=A12
// POST /api/orders (items[], notes, table)
// PATCH /api/orders/{id}/status (role: CHEF or SERVEUR)
// GET /api/orders/stream (SSE/WebSocket)
// POST /api/webhooks/payment (future)

/* Exemple de payload POST /api/orders */
// {
//   "tenant": "resto-xy",
//   "table": "A12",
//   "items": [{
//       "dishId": 1204,
//       "variantId": 3,
//       "options": ["sans_piment", "sauce_graine_plus"],
//       "qty": 2,
//       "notes": "moins salé"
//   }]
// }

/* -------------------------------------------------------------------------- */
/*                           5. KITCHEN DISPLAY SYSTEM                        */
/* -------------------------------------------------------------------------- */

// - Vue temps réel avec colonnes ou file chronologique
// - Statuts: RECU → PREP → PRET → SERVI
// - Filtres par section (chaud/froid/dessert)

/* -------------------------------------------------------------------------- */
/*                             6. SÉCURITÉ & AUTH                             */
/* -------------------------------------------------------------------------- */

// Auth: JWT + Refresh Token
// RBAC: SUPER_ADMIN, ADMIN, CHEF, SERVEUR
// CORS par domaine
// Soft delete + audit logs
// Backups quotidiens + tests de restauration

/* -------------------------------------------------------------------------- */
/*                             7. DÉPLOIEMENT DEVOPS                          */
/* -------------------------------------------------------------------------- */

// ENV: DEV → STAGING → PROD
// CI/CD: tests, migrations DB, rollback automatisé
// Logs centralisés + alertes erreurs/latence

?>