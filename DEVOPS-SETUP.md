# 🚀 DevOps & Monitoring Setup Guide

Guide complet pour configurer l'infrastructure DevOps de SmartMenu SaaS (Phase 3).

---

## 📦 Installation des Dépendances

### 1. Installer les packages PHP

```bash
composer install
```

Cela installera automatiquement :
- `sentry/sentry-laravel` - Monitoring d'erreurs
- `spatie/laravel-backup` - Backups automatiques
- `barryvdh/laravel-dompdf` - Export PDF
- `maatwebsite/excel` - Export Excel
- `laravel/telescope` (dev) - Performance monitoring
- `phpstan/phpstan` (dev) - Analyse statique
- `larastan/larastan` (dev) - PHPStan pour Laravel
- `laravel/pint` (dev) - Code formatting

### 2. Publier les configurations

```bash
# Sentry
php artisan sentry:publish --dsn=YOUR_SENTRY_DSN

# Laravel Backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# Laravel Telescope (dev/staging uniquement)
php artisan telescope:install
php artisan migrate
```

---

## 🔧 Configuration

### 1. Variables d'environnement (.env)

Copier les variables de `.env.example` vers `.env` et configurer :

#### Sentry (Monitoring d'erreurs)
```env
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project-id
SENTRY_TRACES_SAMPLE_RATE=0.2
SENTRY_ENVIRONMENT=production
```

**Comment obtenir le DSN :**
1. Créer un compte sur [sentry.io](https://sentry.io)
2. Créer un nouveau projet Laravel
3. Copier le DSN fourni

#### Backups (S3)
```env
BACKUP_DISK=s3
BACKUP_NAME=smartmenu-backup
BACKUP_MAIL_TO=admin@votre-domaine.com

# AWS S3 ou DigitalOcean Spaces
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-backup-bucket

# Pour DigitalOcean Spaces, ajouter :
# AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
# AWS_DEFAULT_REGION=nyc3
```

#### Slack Notifications
```env
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

**Comment obtenir le webhook :**
1. Aller sur [api.slack.com/apps](https://api.slack.com/apps)
2. Créer une app Slack
3. Activer "Incoming Webhooks"
4. Créer un webhook pour votre canal

#### Laravel Telescope (dev/staging)
```env
TELESCOPE_ENABLED=true  # false en production
TELESCOPE_DRIVER=database
```

### 2. GitHub Secrets

Pour que les workflows GitHub Actions fonctionnent, ajouter ces secrets dans :
`Settings > Secrets and variables > Actions`

- `DOCKER_USERNAME` - Votre username Docker Hub
- `DOCKER_PASSWORD` - Votre password/token Docker Hub
- `SERVER_HOST` - IP du serveur de production
- `SERVER_USER` - Username SSH
- `SSH_PRIVATE_KEY` - Clé SSH privée
- `SERVER_PORT` - Port SSH (optionnel, défaut: 22)
- `SLACK_WEBHOOK_URL` - Pour notifications déploiement
- `CODECOV_TOKEN` - Pour upload couverture tests (optionnel)

---

## 🔍 Utilisation

### Health Checks

Vérifier l'état de l'application :

```bash
# Health check complet
curl http://localhost/health

# Ping simple
curl http://localhost/ping
```

**Réponse exemple :**
```json
{
  "status": "ok",
  "healthy": true,
  "timestamp": "2026-01-27T14:30:45.000000Z",
  "database": {
    "status": "ok",
    "response_time_ms": 12.5,
    "driver": "pgsql"
  },
  "cache": {
    "status": "ok",
    "response_time_ms": 5.2,
    "driver": "redis"
  },
  "queue": {
    "status": "ok",
    "driver": "database",
    "pending_jobs": 3
  },
  "storage": {
    "status": "ok",
    "driver": "local",
    "disk_space": {
      "total_gb": 100,
      "free_gb": 60,
      "used_percent": 40
    }
  }
}
```

### Backups Manuels

```bash
# Créer un backup
php artisan backup:run

# Nettoyer vieux backups
php artisan backup:clean

# Lister backups
php artisan backup:list

# Vérifier santé backups
php artisan backup:monitor
```

### Laravel Telescope

Accéder à Telescope (dev/staging) :
```
http://localhost/telescope
```

**Fonctionnalités :**
- Requêtes SQL avec temps d'exécution
- Logs en temps réel
- Requêtes HTTP
- Jobs queue
- Mails envoyés
- Cache hits/misses
- Exceptions

### Analyse Statique (PHPStan)

```bash
# Analyser le code
vendor/bin/phpstan analyse

# Avec rapport détaillé
vendor/bin/phpstan analyse --level=5 --memory-limit=2G
```

### Formatage Code (Laravel Pint)

```bash
# Formater tout le code
vendor/bin/pint

# Vérifier sans modifier
vendor/bin/pint --test

# Formater fichiers spécifiques
vendor/bin/pint app/Models
```

---

## 🤖 GitHub Actions

### Workflows Automatiques

**1. Tests (`.github/workflows/tests.yml`)**
- Déclenché sur : Push/PR sur `main` et `develop`
- Services : PostgreSQL, Redis
- Actions :
  - Install dépendances
  - Build assets
  - Run migrations
  - Execute tous les tests
  - Upload coverage Codecov

**2. Code Quality (`.github/workflows/lint.yml`)**
- Déclenché sur : Push/PR sur `main` et `develop`
- Jobs parallèles :
  - PHPStan (analyse statique niveau 5)
  - Laravel Pint (PSR-12 formatting)
  - ESLint (JavaScript)
  - Security audit (composer audit)

**3. Déploiement (`.github/workflows/deploy.yml`)**
- Déclenché sur : Tags `v*.*.*` (ex: v1.5.0)
- Actions :
  - Build image Docker
  - Push vers Docker Hub
  - Deploy sur serveur via SSH
  - Run migrations
  - Clear caches
  - Restart queue workers
  - Health check
  - Notifications Slack

### Créer un Déploiement

```bash
# Tag nouvelle version
git tag v1.5.0
git push origin v1.5.0

# Le workflow deploy.yml se déclenche automatiquement
# Suivre progression : https://github.com/your-repo/actions
```

### Branch Protection

Configurer dans GitHub : `Settings > Branches > Add rule`

**Pour `main` :**
- ✅ Require pull request reviews
- ✅ Require status checks to pass (tests, lint)
- ✅ Require branches to be up to date
- ✅ Include administrators

---

## 📊 Monitoring en Production

### 1. Sentry (Erreurs)

**Dashboard :** https://sentry.io

**Fonctionnalités :**
- Capture automatique exceptions
- Stack traces complètes
- Context utilisateur/tenant
- Breadcrumbs requêtes
- Alertes email/Slack
- Performance monitoring (20% sample)

### 2. UptimeRobot (Disponibilité)

**Setup :**
1. Créer compte gratuit : [uptimerobot.com](https://uptimerobot.com)
2. Add New Monitor :
   - Type : HTTP(s)
   - URL : https://votre-domaine.com/health
   - Interval : 5 minutes
   - Alert Contacts : Email, SMS

### 3. Logs

**Localisation :**
- `storage/logs/laravel.log` - Logs quotidiens
- `storage/logs/laravel-json.log` - Logs JSON structurés

**Commandes :**
```bash
# Voir logs temps réel
php artisan pail

# Filtrer erreurs
php artisan pail --filter=error

# Voir logs JSON structurés
tail -f storage/logs/laravel-json.log | jq '.'
```

---

## 🔐 Sécurité

### Checklist Production

- [ ] `.env` configuré avec vraies valeurs
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `TELESCOPE_ENABLED=false`
- [ ] HTTPS activé (certificat SSL)
- [ ] Firewall configuré (ports 80, 443, 22 uniquement)
- [ ] SSH par clé (pas password)
- [ ] Backups S3 chiffrés
- [ ] Sentry DSN configuré
- [ ] Rate limiting activé
- [ ] Secrets GitHub configurés

### Commandes Sécurité

```bash
# Audit dépendances
composer audit

# Vérifier configuration
php artisan config:show

# Clear all caches
php artisan optimize:clear
```

---

## 📝 Troubleshooting

### Tests échouent sur CI

```bash
# Localement avec mêmes conditions CI
docker run -it --rm \
  -v $(pwd):/app \
  -w /app \
  php:8.2-cli \
  bash -c "composer install && php artisan test"
```

### Backup échoue

```bash
# Vérifier configuration S3
php artisan tinker
>>> Storage::disk('s3')->exists('test.txt')

# Tester backup
php artisan backup:run --only-db
```

### Health check retourne 503

```bash
# Checker individuellement
php artisan tinker
>>> DB::connection()->getPdo()
>>> Cache::put('test', 'value', 10)
>>> Cache::get('test')
```

### Telescope n'apparaît pas

```bash
# Publier assets
php artisan telescope:publish

# Run migrations
php artisan migrate

# Vérifier .env
TELESCOPE_ENABLED=true
```

---

## 📚 Ressources

- [Sentry Laravel Docs](https://docs.sentry.io/platforms/php/guides/laravel/)
- [Laravel Backup Docs](https://spatie.be/docs/laravel-backup)
- [Laravel Telescope Docs](https://laravel.com/docs/telescope)
- [GitHub Actions Docs](https://docs.github.com/en/actions)
- [PHPStan Docs](https://phpstan.org/user-guide/getting-started)
- [Laravel Pint Docs](https://laravel.com/docs/pint)

---

## ✅ Checklist Phase 3 Complète

- [x] GitHub Actions workflows (tests, lint, deploy)
- [x] Configuration PHPStan niveau 5
- [x] Configuration Laravel Pint (PSR-12)
- [x] Sentry monitoring configuré
- [x] Logs structurés JSON
- [x] Endpoint /health avec checks
- [x] Laravel Backup configuré
- [x] Scheduling backups quotidiens
- [x] Laravel Telescope (dev/staging)
- [x] Documentation complète

**🎉 Phase 3 : DevOps & Monitoring - COMPLÉTÉE !**
