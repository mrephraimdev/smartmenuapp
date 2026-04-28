# HorusPOS — Documentation Complète de l'Application

> Document de présentation — Version détaillée  
> Dernière mise à jour : Avril 2026

---

## VUE D'ENSEMBLE

**HorusPOS** est une plateforme SaaS multi-tenant de gestion de restaurant. Elle permet à plusieurs établissements (restaurants, cafés, brasseries) de gérer l'intégralité de leurs opérations depuis une seule application web : menu digital via QR code, prise de commandes, paiements, réservations, avis clients, affichage cuisine, caisse, statistiques et bien plus.

L'application est construite avec **Laravel 12**, **PHP 8.2**, **Blade + Tailwind CSS + Alpine.js**, et utilise une architecture multi-tenant où chaque restaurant est isolé dans son propre espace avec ses propres données.

---

## ARCHITECTURE GÉNÉRALE

### Multi-Tenant

Chaque **restaurant** est un "tenant". Tous les tenants partagent la même instance de l'application mais leurs données sont totalement isolées :
- Chaque modèle de données est scopé automatiquement au tenant courant via un trait `TenantScope`
- Les URLs contiennent le slug du tenant : `/admin/{slug}/dashboard`
- Un SUPER_ADMIN peut accéder à tous les tenants

### Rôles utilisateurs (6 niveaux)

| Rôle | Nom | Ce qu'il peut faire |
|------|-----|---------------------|
| **SUPER_ADMIN** | Super Administrateur | Gérer tous les restaurants, tous les utilisateurs, accès total |
| **ADMIN** | Administrateur | Gérer son restaurant : menus, tables, staff, paiements, statistiques |
| **CHEF** | Chef Cuisinier | Voir et gérer les commandes en cuisine (KDS) |
| **SERVEUR** | Serveur | Prendre des commandes, voir le KDS, l'historique |
| **CAISSIER** | Caissier | Gérer les paiements, encaissements, rapports de caisse |
| **CLIENT** | Client | Accès public au menu, passer commande, réserver, laisser un avis |

---

## FONCTIONNALITÉS DÉTAILLÉES

---

### 1. MENU DIGITAL (QR CODE)

#### Pour le client (accès public)

La page client est accessible via un QR code affiché sur la table. L'URL est de la forme `/menu/{tenantId}/{tableId}`.

**Ce que le client voit et peut faire :**
- Affichage du menu complet du restaurant avec catégories et plats
- Photo de chaque plat (si disponible)
- Description, prix, allergènes, tags
- Sélection de variants (ex : taille Small/Medium/Large) avec prix additionnels
- Sélection d'options (ex : fromage supplémentaire, sans oignon) avec prix additionnels
- Ajout au panier
- Notes spéciales pour la cuisine sur chaque article
- Validation et envoi de la commande directement depuis la table
- Suivi du statut de la commande en temps réel
- **Appel serveur** : bouton pour demander l'assistance du serveur (SERVICE, QUESTION, URGENCE)

#### Gestion du menu (côté admin)

Un restaurant peut avoir **plusieurs menus** (ex : Menu Déjeuner, Menu Soir, Menu Enfant).

**Structure hiérarchique :**
```
Menu
  └── Catégorie (ex: Entrées, Plats, Desserts, Boissons)
        └── Plat
              ├── Variante (ex: Petite / Grande portion)
              └── Option (ex: Sauce piquante, Double fromage)
```

**Chaque Plat possède :**
- Nom, description
- Prix de base
- Photo (upload image)
- Liste d'allergènes (JSON)
- Tags personnalisés (JSON)
- Quantité en stock
- Temps de préparation (en minutes)
- Statut actif/inactif (masque le plat du menu public)
- Variantes avec supplément de prix
- Options avec supplément de prix (type : toggle, select, quantity)

**Actions disponibles :**
- Créer / modifier / supprimer des menus
- Créer / modifier / supprimer des catégories avec ordre de tri
- Créer / modifier / supprimer des plats
- Uploader et supprimer les photos des plats
- Activer ou désactiver un plat individuellement
- **Import en masse** via fichier Excel (template téléchargeable)

---

### 2. GESTION DES COMMANDES

#### Cycle de vie d'une commande

```
REÇU → EN PRÉPARATION → PRÊT → SERVI
         (ou)
       ANNULÉ
```

Chaque commande a :
- Un numéro unique généré automatiquement : `YYYYMMDD-T{tenantId}-{compteur}`
- Un statut coloré
- La table d'origine
- Les articles commandés (avec variantes et options)
- Les notes cuisine
- Le montant total (calculé automatiquement)
- Le statut de paiement (En attente, Payé, Partiel, Remboursé, Annulé)

#### Sources de commandes

1. **Depuis la table** — Le client scanne le QR code et commande depuis son téléphone
2. **Depuis le comptoir** — Le caissier/admin prend une commande en face à face (POS comptoir)
3. **Depuis le KDS** — Le serveur crée une commande manuellement

#### Suivi des commandes (Tableau Kanban)

La page `/suivi` affiche toutes les commandes actives en colonnes Kanban par statut. Chaque card affiche :
- Numéro de commande et table
- Liste des articles
- Heure de création
- Boutons pour faire avancer le statut
- Bouton d'annulation

**Notifications temps réel :** L'admin reçoit des alertes sonores et visuelles à chaque nouvelle commande.

---

### 3. SYSTÈME DE CAISSE (POS)

#### Sessions de caisse

Chaque journée de travail correspond à une **session de caisse** :
- Ouverture avec fond de caisse (montant initial)
- Toutes les transactions sont liées à la session
- Fermeture avec réconciliation :
  - Montant attendu (calculé)
  - Montant réel (compté physiquement)
  - Écart affiché

#### Encaissements

**Méthodes de paiement acceptées :**
| Méthode | Description |
|---------|-------------|
| **Espèces** | Paiement cash, calcul de la monnaie automatique |
| **Carte** | Paiement par carte bancaire |
| **Orange Money** | Mobile money Orange |
| **MTN MoMo** | Mobile money MTN |
| **Moov Money** | Mobile money Moov |
| **Wave** | Paiement Wave |

**Fonctionnalités d'encaissement :**
- Voir toutes les commandes non payées
- Sélectionner une commande et choisir le mode de paiement
- Paiements partiels (payer une partie maintenant)
- Calcul automatique de la monnaie à rendre (paiement cash)
- Génération et impression du reçu
- Historique complet des paiements avec filtres

#### Rapport journalier

- Total des ventes du jour
- Répartition par méthode de paiement
- Nombre de commandes
- Nombre d'articles vendus
- Annulations
- Remboursements
- Impression du rapport journalier

---

### 4. AFFICHAGE CUISINE (KDS — Kitchen Display System)

La page KDS (`/kds/{tenantSlug}`) est conçue pour être affichée sur un écran en cuisine.

**Fonctionnalités :**
- Liste de toutes les commandes actives (REÇU, EN PRÉPARATION)
- Chaque commande affiche : numéro, table, articles, heure, notes
- Boutons de progression du statut (REÇU → PREP → PRÊT → SERVI)
- Code couleur par statut
- Actualisation automatique en temps réel
- Vue optimisée pour grand écran de cuisine

---

### 5. PRISE DE COMMANDES PAR LE SERVEUR

Le serveur (rôle SERVEUR ou ADMIN) peut :
- Accéder à la page `/kds/{tenantSlug}/commande`
- Sélectionner une table
- Parcourir le menu et ajouter des articles
- Soumettre la commande directement en cuisine
- Consulter l'historique de ses commandes sur `/kds/{tenantSlug}/historique`

---

### 6. APPELS SERVEUR (WAITER CALLS)

Les clients peuvent depuis le menu public appeler un serveur. Le système distingue 3 types d'appels :
- **SERVICE** — Besoin d'assistance générale
- **QUESTION** — Question sur le menu ou la commande
- **URGENCE** — Situation urgente

**Workflow :**
1. Client clique "Appeler le serveur" depuis la page menu
2. L'appel est créé (statut : EN ATTENTE)
3. Le serveur/admin voit l'alerte et prend en charge (statut : PRIS EN CHARGE)
4. Une fois réglé : statut RÉSOLU
5. Les appels expirent automatiquement après 2 heures

---

### 7. GESTION DES TABLES

**Chaque table possède :**
- Un code unique (ex : A01, B03, VIP-1)
- Un label descriptif
- Une capacité (nombre de couverts)
- Un statut actif/inactif
- Un QR code associé

**Fonctionnalités :**
- Créer les tables une par une ou en lot (génération automatique)
- Activer / désactiver une table
- Voir les commandes en cours sur chaque table
- Générer et télécharger le QR code de chaque table

---

### 8. QR CODES

**Génération automatique :** Chaque table dispose d'un QR code unique pointant vers le menu public de ce restaurant et de cette table.

**Format :** SVG (compatible avec l'export PDF sans dépendances Imagick)

**Fonctionnalités :**
- Visualiser le QR code de chaque table
- Télécharger le QR code d'une table en PDF
- Télécharger **tous les QR codes** du restaurant en un seul PDF (idéal pour impression et plastification)

---

### 9. RÉSERVATIONS

#### Page publique de réservation

Accessible via `/reservation/{tenantSlug}`, cette page permet aux clients de réserver une table en ligne.

**Formulaire de réservation :**
- Nom du client
- Téléphone
- Email
- Date de réservation
- Heure
- Nombre de personnes
- Demandes spéciales

**À la soumission :**
- Vérification de disponibilité en temps réel
- Attribution d'un code de confirmation unique (8 caractères)
- Email de confirmation envoyé au client
- Page de confirmation avec récapitulatif

#### Gestion admin des réservations

**Cycle de vie d'une réservation :**
```
EN ATTENTE → CONFIRMÉE → INSTALLÉE → COMPLÉTÉE
                    ↘ ANNULÉE / NO-SHOW
```

**Vue liste :** Toutes les réservations avec filtres par date, statut, table

**Vue calendrier :** Affichage calendaire des réservations du mois

**Actions disponibles :**
- Créer manuellement une réservation
- Confirmer une réservation en attente
- Marquer comme installée (client arrivé)
- Marquer comme complétée
- Annuler (avec motif)
- Marquer comme no-show
- Modifier les détails
- Supprimer

---

### 10. AVIS CLIENTS (REVIEWS)

#### Page publique d'avis

Accessible via `/review/{tenantSlug}`, les clients peuvent laisser un avis après leur repas.

**Formulaire d'avis :**
- Note Nourriture (1 à 5 étoiles)
- Note Service (1 à 5 étoiles)
- Note Ambiance (1 à 5 étoiles)
- Note Globale (calculée automatiquement)
- Commentaire libre
- Nom (optionnel si anonyme)
- Email (optionnel)
- Soumission limitée à 3 par minute (protection anti-spam)

**Page de liste publique** (`/reviews/{tenantSlug}`) : Affiche les avis publiés du restaurant

#### Gestion admin des avis

**Workflow de modération :**
- Les avis arrivents en mode "En attente"
- L'admin peut publier ou rejeter chaque avis
- L'admin peut **mettre en avant** (featured) un avis
- L'admin peut **répondre** à un avis (réponse visible publiquement)
- L'admin peut supprimer un avis

**Statistiques des avis :**
- Note moyenne globale
- Distribution par nombre d'étoiles
- Notes moyennes par catégorie (nourriture, service, ambiance)

---

### 11. STATISTIQUES & ANALYTIQUE

Accessible depuis `/admin/{slug}/statistics`, ce tableau de bord analytique offre :

#### Métriques disponibles

**Chiffre d'affaires :**
- CA du jour / semaine / mois / période personnalisée
- Évolution temporelle (graphique)
- Comparaison avec la période précédente

**Commandes :**
- Nombre total de commandes
- Taux d'annulation
- Volume par heure (analyse des pics)
- Répartition par statut

**Plats :**
- Top 10 des plats les plus commandés
- Revenus générés par plat
- Plats jamais commandés

**Tables :**
- Taux d'occupation
- Taux de conversion (table → commande)
- Commandes moyennes par table

**Paiements :**
- Répartition par méthode de paiement
- Total encaissé vs en attente

---

### 12. EXPORTS & RAPPORTS

#### Exports disponibles

| Type | Format | Contenu |
|------|---------|---------|
| Commandes | CSV | Toutes les commandes avec détails articles |
| Menu | CSV | Catalogue complet des plats |
| Réservations | CSV | Toutes les réservations |
| Avis | CSV | Tous les avis clients |
| Commandes | PDF | Rapport de commandes mis en forme |
| Statistiques | PDF | Rapport analytique complet |
| Menu | PDF | Menu exportable |
| Commandes | Excel | Données tabulaires avancées |
| Statistiques | Excel | Données analytiques avancées |

#### Impression

- **Ticket de cuisine** : Envoyé à l'imprimante cuisine
- **Reçu client** : Reçu de paiement imprimable
- **Rapport journalier** : Récapitulatif de fin de journée

---

### 13. GESTION DU PERSONNEL

L'ADMIN peut créer et gérer les comptes de son équipe :

**Création d'un compte staff :**
- Nom et prénom
- Nom d'utilisateur (login)
- Email
- Rôle (CHEF, SERVEUR, CAISSIER)
- Mot de passe

**Actions disponibles :**
- Voir la liste du personnel
- Créer un nouveau membre
- Modifier les informations
- Supprimer un compte

---

### 14. IMPORT DE MENU EN MASSE (EXCEL)

Pour faciliter la création d'un menu complet, l'admin peut :
1. Télécharger le **template Excel** pré-formaté
2. Remplir ses plats dans Excel (nom, catégorie, prix, description, etc.)
3. Uploader le fichier pour import automatique en base de données

---

### 15. LOGS D'AUDIT

Toutes les actions importantes dans le système sont enregistrées automatiquement.

**Chaque log contient :**
- Utilisateur ayant effectué l'action
- Type d'action (créé, modifié, supprimé, connexion, déconnexion, changement de statut)
- Entité concernée (commande, plat, réservation, etc.)
- Anciennes et nouvelles valeurs
- Champs modifiés
- Adresse IP
- User Agent (navigateur)
- URL et méthode HTTP
- Horodatage précis

**Consultation :** Liste filtrables, détail de chaque log, export CSV

---

### 16. THÈMES & PERSONNALISATION

L'application supporte des thèmes visuels par restaurant :

**Configuration d'un thème :**
- Couleur primaire, secondaire, accent, fond, texte
- Police de titre et de corps
- Catégories : Default, Modern, Elegant, Casual, Luxury
- Thème par défaut assignable

---

### 17. GESTION SUPER_ADMIN

Le SUPER_ADMIN a une vue globale sur toute la plateforme :

**Dashboard global :**
- Vue de tous les restaurants actifs
- Statistiques globales

**Gestion des tenants :**
- Créer un nouveau restaurant
- Modifier les informations (nom, slug, logo, couleurs, type, devise, etc.)
- Activer / désactiver un restaurant
- Supprimer un restaurant

**Gestion des utilisateurs :**
- Voir tous les utilisateurs de tous les tenants
- Créer, modifier, supprimer des utilisateurs
- Assigner les rôles

---

## PAGES PUBLIQUES (sans connexion)

| URL | Description |
|-----|-------------|
| `/menu/{tenantId}/{tableId}` | Menu digital client |
| `/reservation/{tenantSlug}` | Formulaire de réservation |
| `/reservation/{tenantSlug}/confirmation/{code}` | Confirmation de réservation |
| `/review/{tenantSlug}` | Formulaire d'avis |
| `/reviews/{tenantSlug}` | Liste des avis publics |
| `/qrcode/{tenantId}/{tableCode}` | Affichage du QR code |

---

## PAGES PROTÉGÉES (connexion requise)

### Espace Admin (`/admin/{slug}/`)

| URL | Accès | Description |
|-----|-------|-------------|
| `/dashboard` | ADMIN | Tableau de bord principal |
| `/menus` | ADMIN | Gestion des menus |
| `/tables` | ADMIN | Gestion des tables |
| `/qrcodes` | ADMIN | Gestion des QR codes |
| `/orders` | ADMIN | Liste des commandes |
| `/suivi` | ADMIN, CAISSIER | Kanban de suivi commandes |
| `/comptoir` | ADMIN, CAISSIER | POS comptoir |
| `/payments` | ADMIN, CAISSIER | Gestion des paiements |
| `/reservations` | ADMIN | Gestion des réservations |
| `/reviews` | ADMIN | Modération des avis |
| `/statistics` | ADMIN | Statistiques |
| `/reports` | ADMIN | Rapports et exports |
| `/staff` | ADMIN | Gestion du personnel |
| `/audit-logs` | ADMIN | Journal d'activité |
| `/import` | ADMIN | Import menu Excel |

### Espace Caisse (`/caisse/{slug}/`)

| URL | Accès | Description |
|-----|-------|-------------|
| `/payments` | CAISSIER, ADMIN | Encaissements |
| `/orders` | CAISSIER, ADMIN | Commandes |

### Espace KDS (`/{slug}/`)

| URL | Accès | Description |
|-----|-------|-------------|
| `/kds/{slug}` | CHEF, SERVEUR, ADMIN | Affichage cuisine |
| `/kds/{slug}/commande` | SERVEUR, ADMIN | Prise de commande |
| `/kds/{slug}/historique` | SERVEUR, ADMIN | Historique commandes |

---

## INFRASTRUCTURE TECHNIQUE

### Backend
- **Framework** : Laravel 12 (PHP 8.2)
- **Base de données** : SQLite (dev), MySQL/PostgreSQL (prod)
- **ORM** : Eloquent avec traits custom (TenantScope, SoftDeletes)
- **Auth** : Laravel sanctum (API) + session web
- **File storage** : Laravel Storage (images, exports)
- **Queue** : Jobs asynchrones pour emails, notifications
- **Cache** : Cache sur les données de menu et statistiques

### Frontend
- **Templates** : Blade (Laravel)
- **CSS** : Tailwind CSS v4
- **JS** : Alpine.js pour l'interactivité
- **Build** : Vite
- **Icônes** : Heroicons (composants Blade)

### Packages clés
- `barryvdh/laravel-dompdf` — Génération PDF
- `simplesoftwareio/simple-qrcode` — QR codes SVG
- `maatwebsite/excel` — Import/Export Excel
- `laravel/sanctum` — Authentification API

### Sécurité
- CSRF sur tous les formulaires
- Rate limiting sur les pages publiques (réservations : 5/min, avis : 3/min, API : 60/min)
- Isolation multi-tenant stricte
- Journalisation de toutes les actions sensibles
- Validation stricte des données en entrée
- Hachage des mots de passe (bcrypt)

---

## RÉSUMÉ DES FONCTIONNALITÉS

| Domaine | Fonctionnalités clés |
|---------|---------------------|
| **Menu digital** | QR code, menu multi-niveaux, variantes, options, photos |
| **Commandes** | Depuis table / comptoir / serveur, kanban, notifications temps réel |
| **Caisse (POS)** | Sessions, 6 modes de paiement, reçus, rapport journalier |
| **Cuisine (KDS)** | Affichage temps réel, progression des commandes |
| **Réservations** | Booking public, gestion admin, workflow complet, emails |
| **Avis clients** | 4 critères de notation, modération, réponses admin |
| **Statistiques** | CA, commandes, top plats, pics horaires, exports |
| **Multi-tenant** | Isolation totale, 6 rôles, SUPER_ADMIN global |
| **Personnalisation** | Thèmes, couleurs, polices, logo, couverture |
| **Sécurité** | Audit logs complets, rate limiting, isolation tenant |

---

*Document généré automatiquement — HorusPOS © 2026*
