#!/bin/sh
set -e

echo "=== HorusPOS - Démarrage du conteneur ==="

# -----------------------------------------------
# 1. Attendre que PostgreSQL soit prêt
# -----------------------------------------------
echo "Attente de PostgreSQL sur ${DB_HOST}:${DB_PORT}..."
until php -r "
    try {
        \$pdo = new PDO(
            'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD')
        );
        echo 'OK';
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    echo "PostgreSQL pas encore prêt, nouvelle tentative dans 3s..."
    sleep 3
done
echo "PostgreSQL est prêt."

# -----------------------------------------------
# 2. Générer APP_KEY si absent
# -----------------------------------------------
if [ -z "$APP_KEY" ]; then
    echo "Génération de APP_KEY..."
    php artisan key:generate --force
fi

# -----------------------------------------------
# 3. Migrations
# -----------------------------------------------
echo "Exécution des migrations..."
php artisan migrate --force -v 2>&1 || echo "⚠ Migration échouée — voir erreur ci-dessus"

# -----------------------------------------------
# 4. Lien symbolique storage
# -----------------------------------------------
echo "Création du lien storage..."
php artisan storage:link 2>/dev/null || true

# -----------------------------------------------
# 5. Cache (uniquement en production/staging)
# -----------------------------------------------
if [ "$APP_ENV" = "production" ] || [ "$APP_ENV" = "staging" ]; then
    echo "Mise en cache de la configuration..."
    php artisan config:cache 2>&1 || echo "⚠ config:cache ignoré"
    php artisan route:cache 2>&1 || echo "⚠ route:cache ignoré"
    php artisan view:cache 2>&1 || echo "⚠ view:cache ignoré"
else
    echo "Mode ${APP_ENV} — cache ignoré."
fi

# -----------------------------------------------
# 6. Corriger les permissions storage (volumes bind-mount)
# -----------------------------------------------
echo "Correction des permissions storage..."
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "=== Démarrage des services ==="
exec "$@"
