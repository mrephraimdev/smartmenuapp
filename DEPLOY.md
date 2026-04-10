# GUIDE DE DEPLOIEMENT - SmartMenu

> Guide pas-a-pas pour mettre SmartMenu en production.
> Ecrit pour les debutants. Chaque etape est detaillee.

---

## TABLE DES MATIERES

1. [Ce qu'il faut avant de commencer](#1-ce-quil-faut-avant-de-commencer)
2. [Option A : Deploiement avec Docker (Recommande)](#2-option-a--deploiement-avec-docker-recommande)
3. [Option B : Deploiement sur un serveur classique](#3-option-b--deploiement-sur-un-serveur-classique)
4. [Configuration de la base de donnees](#4-configuration-de-la-base-de-donnees)
5. [Configuration du fichier .env de production](#5-configuration-du-fichier-env-de-production)
6. [Lancer l'application](#6-lancer-lapplication)
7. [Configurer le nom de domaine et HTTPS](#7-configurer-le-nom-de-domaine-et-https)
8. [Sauvegardes automatiques](#8-sauvegardes-automatiques)
9. [Mise a jour de l'application](#9-mise-a-jour-de-lapplication)
10. [Depannage](#10-depannage)
11. [Checklist finale avant mise en ligne](#11-checklist-finale-avant-mise-en-ligne)

---

## 1. CE QU'IL FAUT AVANT DE COMMENCER

### Ce que tu dois avoir :

- **Un serveur** (VPS) chez un hebergeur : DigitalOcean, OVH, Hetzner, etc.
  - Minimum : 2 CPU, 4 Go RAM, 20 Go disque
  - Systeme : Ubuntu 22.04 ou 24.04
- **Un nom de domaine** (ex: smartmenu.ci, monrestaurant.com)
- **Un acces SSH** a ton serveur (terminal ou PuTTY sur Windows)

### Se connecter au serveur :

```bash
# Depuis ton terminal (remplace par ton IP)
ssh root@123.456.789.0
```

---

## 2. OPTION A : DEPLOIEMENT AVEC DOCKER (Recommande)

Docker installe tout automatiquement. C'est la methode la plus simple.

### Etape 1 : Installer Docker sur le serveur

```bash
# Mettre a jour le systeme
sudo apt update && sudo apt upgrade -y

# Installer Docker
curl -fsSL https://get.docker.com | sh

# Installer Docker Compose
sudo apt install docker-compose-plugin -y

# Verifier l'installation
docker --version
docker compose version
```

### Etape 2 : Copier le projet sur le serveur

```bash
# Creer le dossier de l'application
mkdir -p /var/www/smartmenu
cd /var/www/smartmenu

# Cloner le projet depuis GitHub
git clone https://github.com/TON-UTILISATEUR/menu-qr-app.git .
```

### Etape 3 : Configurer l'environnement de production

```bash
# Copier le fichier de configuration production
cp .env.production.example .env

# Generer la cle de l'application
# (on le fera apres le build Docker, voir etape 5)
```

### Etape 4 : Modifier le fichier .env

```bash
# Ouvrir le fichier avec nano (editeur de texte simple)
nano .env
```

Modifie ces lignes (voir Section 5 pour le detail complet) :

```env
APP_NAME=SmartMenu
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ton-domaine.com

DB_PASSWORD=un_mot_de_passe_tres_long_et_complexe
```

Pour sauvegarder dans nano : `Ctrl + O` puis `Entree`, pour quitter : `Ctrl + X`

### Etape 5 : Lancer l'application

```bash
# Construire et demarrer tous les services
docker compose up -d --build

# Attendre 30 secondes que tout demarre...

# Generer la cle de l'application
docker compose exec app php artisan key:generate

# Lancer les migrations (creer les tables)
docker compose exec app php artisan migrate --force

# Creer le compte Super Admin
docker compose exec app php artisan db:seed --force

# Optimiser pour la production
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

### Etape 6 : Verifier que tout marche

```bash
# Voir l'etat des services
docker compose ps

# Tu dois voir 6 services "running" :
# app, queue, scheduler, postgres, redis, nginx

# Tester l'application
curl http://localhost
```

Ouvre ton navigateur sur `http://IP-DU-SERVEUR` - tu dois voir SmartMenu.

---

## 3. OPTION B : DEPLOIEMENT SUR UN SERVEUR CLASSIQUE

Si tu ne veux pas utiliser Docker, voici comment installer directement.

### Etape 1 : Installer les logiciels necessaires

```bash
# Mettre a jour le systeme
sudo apt update && sudo apt upgrade -y

# Installer PHP 8.2
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-pgsql \
  php8.2-redis php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip \
  php8.2-gd php8.2-bcmath php8.2-intl php8.2-opcache

# Installer MySQL
sudo apt install -y mysql-server

# Installer Redis (pour le cache en production)
sudo apt install -y redis-server

# Installer Nginx
sudo apt install -y nginx

# Installer Composer (gestionnaire de paquets PHP)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installer Node.js 20 (pour compiler les assets CSS/JS)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### Etape 2 : Configurer MySQL

```bash
# Se connecter a MySQL
sudo mysql

# Dans MySQL, taper ces commandes :
CREATE DATABASE smartmenu;
CREATE USER 'smartmenu'@'localhost' IDENTIFIED BY 'TON_MOT_DE_PASSE_ICI';
GRANT ALL PRIVILEGES ON smartmenu.* TO 'smartmenu'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Etape 3 : Copier et configurer le projet

```bash
# Creer le dossier
sudo mkdir -p /var/www/smartmenu
cd /var/www/smartmenu

# Cloner le projet
git clone https://github.com/TON-UTILISATEUR/menu-qr-app.git .

# Installer les dependances PHP
composer install --optimize-autoloader --no-dev

# Installer les dependances JS et compiler
npm ci
npm run build

# Copier et configurer l'environnement
cp .env.production.example .env
nano .env
# (modifier les valeurs, voir Section 5)

# Generer la cle
php artisan key:generate

# Lancer les migrations
php artisan migrate --force

# Creer le compte admin
php artisan db:seed --force

# Optimiser
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Donner les permissions au serveur web
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Etape 4 : Configurer Nginx

```bash
# Creer la configuration Nginx
sudo nano /etc/nginx/sites-available/smartmenu
```

Coller ce contenu :

```nginx
server {
    listen 80;
    server_name ton-domaine.com www.ton-domaine.com;
    root /var/www/smartmenu/public;

    index index.php;

    # Taille max des uploads (images de plats)
    client_max_body_size 10M;

    # Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Bloquer l'acces aux fichiers caches
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Activer le site
sudo ln -s /etc/nginx/sites-available/smartmenu /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default

# Tester la config Nginx
sudo nginx -t

# Redemarrer Nginx
sudo systemctl restart nginx
```

### Etape 5 : Configurer le worker de queue

```bash
# Creer un service systemd pour le queue worker
sudo nano /etc/systemd/system/smartmenu-queue.service
```

Coller :

```ini
[Unit]
Description=SmartMenu Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=5
WorkingDirectory=/var/www/smartmenu
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
# Activer et demarrer
sudo systemctl enable smartmenu-queue
sudo systemctl start smartmenu-queue
```

---

## 4. CONFIGURATION DE LA BASE DE DONNEES

### En local (developpement) - MySQL :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=menu_qr_app
DB_USERNAME=root
DB_PASSWORD=root
```

### En production avec Docker - PostgreSQL :

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=smartmenu
DB_USERNAME=smartmenu
DB_PASSWORD=un_mot_de_passe_tres_complexe_ici_42!
```

### En production sans Docker - MySQL :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartmenu
DB_USERNAME=smartmenu
DB_PASSWORD=un_mot_de_passe_tres_complexe_ici_42!
```

---

## 5. CONFIGURATION DU FICHIER .ENV DE PRODUCTION

Voici TOUTES les lignes a modifier dans le `.env` pour la production :

```env
# ============================================
# APPLICATION
# ============================================
APP_NAME=SmartMenu
APP_ENV=production          # IMPORTANT: "production", pas "local"
APP_DEBUG=false              # IMPORTANT: "false" en production
APP_URL=https://ton-domaine.com  # Ton vrai domaine avec https
APP_TIMEZONE=Africa/Abidjan
APP_LOCALE=fr

# ============================================
# BASE DE DONNEES
# ============================================
DB_CONNECTION=pgsql          # ou "mysql" si tu utilises MySQL
DB_HOST=postgres             # "postgres" avec Docker, "127.0.0.1" sans Docker
DB_PORT=5432                 # 5432 pour PostgreSQL, 3306 pour MySQL
DB_DATABASE=smartmenu
DB_USERNAME=smartmenu
DB_PASSWORD=CHANGE_MOI_mot_de_passe_complexe_123!

# ============================================
# SESSIONS ET CACHE (utiliser Redis en production)
# ============================================
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis             # "redis" avec Docker, "127.0.0.1" sans Docker
REDIS_PORT=6379

# ============================================
# EMAILS (optionnel - pour les notifications)
# ============================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com     # ou ton fournisseur SMTP
MAIL_PORT=587
MAIL_USERNAME=ton-email@gmail.com
MAIL_PASSWORD=ton-mot-de-passe-application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=contact@ton-domaine.com
MAIL_FROM_NAME="SmartMenu"

# ============================================
# SECURITE
# ============================================
BCRYPT_ROUNDS=12
```

### Les regles d'or du .env :

| Regle | Pourquoi |
|-------|----------|
| `APP_DEBUG=false` | Ne JAMAIS mettre true en production (fuite d'infos) |
| `APP_ENV=production` | Active les optimisations de Laravel |
| Mot de passe DB complexe | Au moins 20 caracteres avec chiffres et symboles |
| `SESSION_SECURE_COOKIE=true` | Les cookies ne passent que par HTTPS |
| `SESSION_ENCRYPT=true` | Les donnees de session sont chiffrees |

---

## 6. LANCER L'APPLICATION

### Avec Docker :

```bash
# Demarrer
docker compose up -d

# Arreter
docker compose down

# Voir les logs en temps reel
docker compose logs -f app

# Redemarrer un service
docker compose restart app
```

### Sans Docker :

```bash
# Demarrer PHP-FPM et Nginx
sudo systemctl start php8.2-fpm
sudo systemctl start nginx
sudo systemctl start smartmenu-queue

# Voir les logs Laravel
tail -f /var/www/smartmenu/storage/logs/laravel.log
```

### Verifier que tout fonctionne :

```bash
# Test rapide
curl -I https://ton-domaine.com
# Tu dois voir : HTTP/2 200

# Test du health check
curl https://ton-domaine.com/up
# Tu dois voir : une page qui dit que tout est OK
```

---

## 7. CONFIGURER LE NOM DE DOMAINE ET HTTPS

### Etape 1 : Configurer le DNS

Chez ton registrar de domaine (GoDaddy, Namecheap, etc.) :

| Type | Nom | Valeur |
|------|-----|--------|
| A | @ | IP-DE-TON-SERVEUR |
| A | www | IP-DE-TON-SERVEUR |

Attendre 5-30 minutes que le DNS se propage.

### Etape 2 : Installer le certificat SSL (HTTPS gratuit)

```bash
# Installer Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtenir le certificat (remplace par ton domaine)
sudo certbot --nginx -d ton-domaine.com -d www.ton-domaine.com

# Suivre les instructions a l'ecran :
# - Entrer ton email
# - Accepter les conditions
# - Choisir de rediriger HTTP vers HTTPS (option 2)

# Le renouvellement est automatique, mais on peut tester :
sudo certbot renew --dry-run
```

Apres ca, ton site est accessible en `https://ton-domaine.com`

### Avec Docker :

Si tu utilises Docker, le certificat SSL se configure differemment.
Le plus simple est d'utiliser un reverse proxy comme Traefik ou Caddy
devant Nginx. Sinon, monte le certificat dans le conteneur :

```bash
# Generer le certificat sur le serveur
sudo certbot certonly --standalone -d ton-domaine.com

# Les certificats sont dans /etc/letsencrypt/live/ton-domaine.com/
# Monte-les dans docker-compose.yml sous le service nginx
```

---

## 8. SAUVEGARDES AUTOMATIQUES

### Sauvegarder la base de donnees

```bash
# Avec Docker (PostgreSQL) :
docker compose exec postgres pg_dump -U smartmenu smartmenu > backup_$(date +%Y%m%d).sql

# Sans Docker (MySQL) :
mysqldump -u smartmenu -p smartmenu > backup_$(date +%Y%m%d).sql
```

### Automatiser les sauvegardes (tous les jours a 3h du matin)

```bash
# Ouvrir le crontab
crontab -e

# Ajouter cette ligne :
0 3 * * * cd /var/www/smartmenu && docker compose exec -T postgres pg_dump -U smartmenu smartmenu > /var/backups/smartmenu_$(date +\%Y\%m\%d).sql

# Nettoyer les backups de plus de 30 jours
0 4 * * * find /var/backups/ -name "smartmenu_*.sql" -mtime +30 -delete
```

### Sauvegarder les fichiers uploades (photos de plats)

```bash
# Copier le dossier storage
tar czf /var/backups/storage_$(date +%Y%m%d).tar.gz /var/www/smartmenu/storage/app/public
```

---

## 9. MISE A JOUR DE L'APPLICATION

Quand tu as une nouvelle version a deployer :

### Avec Docker :

```bash
cd /var/www/smartmenu

# Recuperer les changements
git pull origin main

# Reconstruire et redemarrer
docker compose up -d --build

# Lancer les nouvelles migrations
docker compose exec app php artisan migrate --force

# Nettoyer le cache
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

### Sans Docker :

```bash
cd /var/www/smartmenu

# Passer en mode maintenance (affiche "Site en maintenance")
php artisan down

# Recuperer les changements
git pull origin main

# Mettre a jour les dependances
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# Lancer les migrations
php artisan migrate --force

# Nettoyer et recacher
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Redemarrer le queue worker
sudo systemctl restart smartmenu-queue

# Remettre en ligne
php artisan up
```

---

## 10. DEPANNAGE

### Problemes courants et solutions :

| Probleme | Cause probable | Solution |
|----------|---------------|----------|
| Page blanche | `APP_DEBUG=false` cache l'erreur | Regarder `storage/logs/laravel.log` |
| Erreur 500 | Permissions ou config | `chmod -R 775 storage bootstrap/cache` |
| Erreur 419 | CSRF / Sessions | Verifier `SESSION_DRIVER` et vider le cache |
| Images non affichees | Lien symbolique manquant | `php artisan storage:link` |
| CSS/JS non charges | Assets non compiles | `npm run build` |
| Login impossible | Mauvais mot de passe | Recreer le user (voir ci-dessous) |

### Commandes utiles :

```bash
# Voir les logs en temps reel
# Docker :
docker compose logs -f app
# Sans Docker :
tail -f storage/logs/laravel.log

# Vider TOUS les caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Recreer le lien des fichiers publics (images)
php artisan storage:link

# Recreer un compte Super Admin
php artisan tinker
# Puis taper :
# App\Models\User::create(['name'=>'Admin', 'email'=>'admin@smartmenu.com', 'password'=>Hash::make('admin1234'), 'role'=>'SUPER_ADMIN']);

# Verifier l'etat de la base de donnees
php artisan migrate:status
```

### Verifier les services (avec Docker) :

```bash
# Tous les services tournent ?
docker compose ps

# Redemarrer un service qui plante
docker compose restart app

# Voir les logs d'un service specifique
docker compose logs -f postgres
docker compose logs -f redis
docker compose logs -f nginx
```

---

## 11. CHECKLIST FINALE AVANT MISE EN LIGNE

Coche chaque element avant de mettre en ligne :

### Securite
- [ ] `APP_DEBUG=false` dans le .env
- [ ] `APP_ENV=production` dans le .env
- [ ] Mot de passe de la base de donnees complexe (20+ caracteres)
- [ ] HTTPS active avec certificat SSL
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_ENCRYPT=true`
- [ ] Le fichier `.env` n'est PAS dans le depot Git

### Base de donnees
- [ ] Toutes les migrations sont passees (`php artisan migrate:status`)
- [ ] Le compte Super Admin est cree
- [ ] Les sauvegardes automatiques sont configurees

### Performance
- [ ] `php artisan config:cache` execute
- [ ] `php artisan route:cache` execute
- [ ] `php artisan view:cache` execute
- [ ] `npm run build` execute (assets compiles)
- [ ] `php artisan storage:link` execute (images accessibles)

### Fonctionnel
- [ ] La page de login s'affiche
- [ ] Le Super Admin peut se connecter
- [ ] On peut creer un tenant (restaurant)
- [ ] On peut ajouter des plats avec images
- [ ] Le QR code fonctionne et affiche le menu
- [ ] Une commande peut etre passee depuis le menu client
- [ ] La commande apparait dans le KDS (cuisine)

### Monitoring
- [ ] Les logs Laravel fonctionnent (`storage/logs/`)
- [ ] Le health check repond (`/up`)

---

## RESUME RAPIDE

```
1. Louer un serveur Ubuntu
2. Installer Docker
3. Cloner le projet
4. Configurer le .env
5. docker compose up -d --build
6. php artisan migrate --force
7. php artisan db:seed --force
8. Configurer le domaine + SSL
9. C'est en ligne !
```

**Identifiants par defaut du Super Admin :**
- Email : `admin@smartmenu.com`
- Mot de passe : `admin1234`
- **A CHANGER immediatement apres la premiere connexion !**

---

*Guide cree le 6 fevrier 2026 - SmartMenu v1.0*
