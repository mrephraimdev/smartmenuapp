<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN = 'ADMIN';
    case CHEF = 'CHEF';
    case SERVEUR = 'SERVEUR';
    case CAISSIER = 'CAISSIER';
    case CLIENT = 'CLIENT';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrateur',
            self::ADMIN => 'Administrateur',
            self::CHEF => 'Chef Cuisinier',
            self::SERVEUR => 'Serveur',
            self::CAISSIER => 'Caissier(e)',
            self::CLIENT => 'Client',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Accès total à tous les tenants',
            self::ADMIN => 'Gère son restaurant : menu, tables, commandes, stats',
            self::CHEF => 'Cuisine : voir et préparer les commandes',
            self::SERVEUR => 'Salle : service, tables, commandes',
            self::CAISSIER => 'Caisse : paiements, encaissements, POS',
            self::CLIENT => 'Menu public et commandes',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::SUPER_ADMIN => ['*'],
            self::ADMIN => [
                'manage_menu',
                'manage_tables',
                'manage_orders',
                'manage_payments',
                'view_statistics',
                'manage_users',
                'manage_branding',
                'access_pos',
                'view_reports',
                'export_data',
            ],
            self::CHEF => [
                'view_orders',
                'update_order_status',
                'view_menu',
                'access_kds',
            ],
            self::SERVEUR => [
                'view_orders',
                'update_order_status',
                'view_menu',
                'view_tables',
                'create_order',
                'access_kds',
            ],
            self::CAISSIER => [
                'view_orders',
                'update_order_status',
                'view_menu',
                'view_tables',
                'create_order',
                'manage_payments',
                'access_pos',
                'view_daily_report',
                'print_receipts',
            ],
            self::CLIENT => [
                'view_menu',
                'create_order',
            ],
        };
    }

    /**
     * Vérifie si le rôle a accès au KDS (Kitchen Display System)
     */
    public function canAccessKDS(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN, self::CHEF, self::SERVEUR]);
    }

    /**
     * Vérifie si le rôle a accès au POS (Point of Sale)
     */
    public function canAccessPOS(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN, self::CAISSIER]);
    }

    /**
     * Vérifie si le rôle peut gérer les paiements
     */
    public function canManagePayments(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN, self::CAISSIER]);
    }

    /**
     * Vérifie si le rôle peut gérer le menu
     */
    public function canManageMenu(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    /**
     * Vérifie si le rôle peut gérer les tenants
     */
    public function canManageTenants(): bool
    {
        return $this === self::SUPER_ADMIN;
    }

    /**
     * Vérifie si le rôle peut voir les statistiques
     */
    public function canViewStatistics(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    /**
     * Vérifie si le rôle peut gérer les utilisateurs
     */
    public function canManageUsers(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    /**
     * Retourne les rôles disponibles pour un tenant (exclu SUPER_ADMIN et CLIENT)
     */
    public static function tenantRoles(): array
    {
        return [
            self::ADMIN,
            self::CHEF,
            self::SERVEUR,
            self::CAISSIER,
        ];
    }

    /**
     * Retourne les rôles opérationnels (staff du restaurant)
     */
    public static function staffRoles(): array
    {
        return [
            self::CHEF,
            self::SERVEUR,
            self::CAISSIER,
        ];
    }

    /**
     * Retourne le badge CSS pour l'affichage
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'bg-red-100 text-red-800',
            self::ADMIN => 'bg-purple-100 text-purple-800',
            self::CHEF => 'bg-orange-100 text-orange-800',
            self::SERVEUR => 'bg-blue-100 text-blue-800',
            self::CAISSIER => 'bg-green-100 text-green-800',
            self::CLIENT => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Retourne l'icône associée au rôle
     */
    public function icon(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'shield-check',
            self::ADMIN => 'building-office',
            self::CHEF => 'fire',
            self::SERVEUR => 'user-group',
            self::CAISSIER => 'banknotes',
            self::CLIENT => 'user',
        };
    }
}
