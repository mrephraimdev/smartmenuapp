# DOSSIER PRODUCTION - SmartMenu
## Guide complet de deploiement VPS de A a Z

> **Stack technique** : Laravel 12 + PHP 8.2 + MySQL 8 + Nginx + Node.js (Vite) + Redis
> **Cible** : VPS Linux Ubuntu 22.04 LTS

---

# 1. ACHAT ET CHOIX DU VPS

## 1.1 Specs minimales recommandees

| Ressource | Minimum | Recommande | Production serieuse |
|-----------|---------|------------|---------------------|
| CPU       | 1 vCPU  | 2 vCPU     | 4 vCPU              |
| RAM       | 1 Go    | 2 Go       | 4 Go                |
| Stockage  | 20 Go SSD | 40 Go SSD | 80 Go NVMe        |
| Bande passante | 1 To | 2 To    | Illimitee           |

> **Pour SmartMenu** : 2 vCPU / 2 Go RAM / 40 Go SSD est ideal pour demarrer.
> Laravel + MySQL + Nginx tournent confortablement avec ca.

## 1.2 Comparatif des fournisseurs

| Fournisseur | Prix/mois | Avantages | Inconvenients |
|-------------|-----------|-----------|---------------|
| **Hetzner** | ~4-8 EUR | Meilleur rapport qualite/prix, datacenter EU, tres fiable | Interface basique |
| **DigitalOcean** | ~6-12 USD | Interface excellente, docs top, marketplace | Plus cher |
| **OVH** | ~3-7 EUR | Serveurs en France, bon prix | Support lent, interface vieillissante |
| **Hostinger** | ~3-10 EUR | Pas cher, panel facile | Performances variables, moins pro |
| **Vultr** | ~5-10 USD | Bonne perf, beaucoup de regions | Moins connu |
| **Contabo** | ~4-6 EUR | Tres pas cher, specs elevees | Perf reseau moyenne |

> **Recommandation** : **Hetzner** (meilleur rapport qualite/prix) ou **DigitalOcean** (meilleure experience debutant).

## 1.3 Quelle distribution Linux ?

**Ubuntu 22.04 LTS** - c'est le choix recommande car :
- Support Long Term (jusqu'en 2027, securite jusqu'en 2032)
- 95% des tutoriels sont ecrits pour Ubuntu
- Packages PHP/MySQL/Nginx toujours a jour
- Grande communaute francophone

> **Erreur a eviter** : Ne prenez PAS Ubuntu 23.x ou 24.x (non-LTS). Prenez toujours la version **LTS**.

## 1.4 A la reception du VPS

Votre fournisseur vous donne :
- **Adresse IP** : ex. `203.0.113.50`
- **Mot de passe root** ou **cle SSH** (selon le fournisseur)
- **Acces console** dans le panel (en secours si SSH ne marche plus)

Notez ces informations dans un endroit securise (gestionnaire de mots de passe).

---

# 2. CONNEXION ET SECURISATION DU SERVEUR

## 2.1 Premiere connexion SSH

Depuis votre PC Windows, ouvrez **PowerShell** ou **Windows Terminal** :

```bash
ssh root@203.0.113.50
```

> Remplacez `203.0.113.50` par l'IP de votre VPS.

Si c'est la premiere connexion, tapez `yes` quand il demande de confirmer le fingerprint.

## 2.2 Mise a jour immediate du systeme

```bash
apt update && apt upgrade -y
```

> **Pourquoi ?** Votre VPS fraichement installe peut avoir des failles de securite.
> Faites ca en PREMIER, toujours.

## 2.3 Creation d'un utilisateur non-root

```bash
# Creer l'utilisateur (remplacez "deploy" par le nom que vous voulez)
adduser deploy

# Lui donner les droits sudo
usermod -aG sudo deploy
```

Il vous demandera un mot de passe. Choisissez un mot de passe **fort** (min 16 caracteres).

## 2.4 Configuration des cles SSH

### Sur votre PC Windows (PowerShell) :

```powershell
# Generer une paire de cles (si vous n'en avez pas deja)
ssh-keygen -t ed25519 -C "votre-email@example.com"
```

Appuyez sur Entree pour accepter le chemin par defaut (`C:\Users\VotreNom\.ssh\id_ed25519`).

```powershell
# Copier la cle publique sur le serveur
type $env:USERPROFILE\.ssh\id_ed25519.pub | ssh root@203.0.113.50 "mkdir -p /home/deploy/.ssh && cat >> /home/deploy/.ssh/authorized_keys && chmod 700 /home/deploy/.ssh && chmod 600 /home/deploy/.ssh/authorized_keys && chown -R deploy:deploy /home/deploy/.ssh"
```

### Tester la connexion avec le nouvel utilisateur :

```powershell
ssh deploy@203.0.113.50
```

Vous devriez vous connecter **sans mot de passe**.

## 2.5 Desactiver le root et le mot de passe SSH

```bash
sudo nano /etc/ssh/sshd_config
```

Trouvez et modifiez ces lignes :

```
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
```

Redemarrer SSH :

```bash
sudo systemctl restart sshd
```

> **ATTENTION** : Avant de fermer votre terminal actuel, ouvrez un NOUVEAU terminal
> et testez `ssh deploy@203.0.113.50`. Si ca marche, tout est bon.
> Si ca ne marche pas, vous avez encore l'ancien terminal pour corriger.

## 2.6 Configuration du Firewall (UFW)

```bash
# Activer UFW avec les regles de base
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Autoriser SSH (TRES IMPORTANT - sinon vous etes verrouille)
sudo ufw allow 22/tcp

# Autoriser HTTP et HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Activer le firewall
sudo ufw enable

# Verifier le statut
sudo ufw status verbose
```

Resultat attendu :
```
Status: active
To                         Action      From
--                         ------      ----
22/tcp                     ALLOW       Anywhere
80/tcp                     ALLOW       Anywhere
443/tcp                    ALLOW       Anywhere
```

## 2.7 Installation de Fail2ban

```bash
sudo apt install fail2ban -y

# Creer la config locale
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local
```

Trouvez la section `[sshd]` et assurez-vous que :

```ini
[sshd]
enabled = true
port = 22
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 3600
findtime = 600
```

> Cela signifie : 3 tentatives echouees en 10 minutes = banni 1 heure.

```bash
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Verifier
sudo fail2ban-client status sshd
```

## 2.8 Configurer le timezone

```bash
sudo timedatectl set-timezone Africa/Abidjan
# ou Africa/Dakar, Europe/Paris, etc. selon votre zone

# Verifier
date
```

---

# 3. INSTALLATION DE L'ENVIRONNEMENT

## 3.1 PHP 8.2 + Extensions

```bash
# Ajouter le depot PHP
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Installer PHP 8.2 et toutes les extensions necessaires pour Laravel
sudo apt install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-sqlite3 \
    php8.2-xml \
    php8.2-curl \
    php8.2-gd \
    php8.2-imagick \
    php8.2-mbstring \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-readline \
    php8.2-redis \
    php8.2-opcache

# Verifier l'installation
php -v
```

> **Note** : `php8.2-imagick` est important pour votre app (generation QR codes PNG).
> `php8.2-gd` est aussi utilise par DomPDF.

### Optimiser PHP-FPM pour la production

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Modifiez ces valeurs :

```ini
upload_max_filesize = 20M
post_max_size = 25M
memory_limit = 256M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
```

> **`opcache.validate_timestamps = 0`** : En production, PHP ne verifie plus si les fichiers
> ont change. C'est plus rapide. Mais apres chaque deploiement, il faut relancer PHP-FPM.

```bash
sudo systemctl restart php8.2-fpm
```

## 3.2 Composer (gestionnaire PHP)

```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verifier
composer --version
```

## 3.3 Node.js 20 LTS (pour Vite/Tailwind)

```bash
# Installer via NodeSource
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verifier
node -v   # v20.x.x
npm -v    # 10.x.x
```

> **Pourquoi Node.js ?** Votre app utilise Vite pour compiler les assets CSS/JS.
> On en a besoin uniquement pour le `npm run build`, pas en runtime.

## 3.4 MySQL 8

```bash
sudo apt install mysql-server -y

# Securiser l'installation
sudo mysql_secure_installation
```

Repondez aux questions :
- `VALIDATE PASSWORD COMPONENT` → **Y** (oui)
- Niveau de validation → **1** (MEDIUM)
- Nouveau mot de passe root → **choisissez un mot de passe fort**
- Remove anonymous users → **Y**
- Disallow root login remotely → **Y**
- Remove test database → **Y**
- Reload privilege tables → **Y**

### Creer la base de donnees et l'utilisateur

```bash
sudo mysql
```

Dans la console MySQL :

```sql
-- Creer la base de donnees
CREATE DATABASE smartmenu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Creer l'utilisateur (CHANGEZ le mot de passe !)
CREATE USER 'smartmenu_user'@'localhost' IDENTIFIED BY 'VotreMotDePasse_Tres_Fort_2026!';

-- Donner les droits
GRANT ALL PRIVILEGES ON smartmenu.* TO 'smartmenu_user'@'localhost';
FLUSH PRIVILEGES;

-- Verifier
SHOW DATABASES;

EXIT;
```

> **Erreur frequente** : Ne JAMAIS utiliser `root` comme utilisateur de base de donnees
> dans votre application. Creez toujours un utilisateur dedie.

## 3.5 Redis (cache + sessions + queues)

```bash
sudo apt install redis-server -y

# Configurer Redis pour systemd
sudo nano /etc/redis/redis.conf
```

Trouvez `supervised no` et changez en :

```
supervised systemd
```

```bash
sudo systemctl restart redis
sudo systemctl enable redis

# Tester
redis-cli ping
# Reponse attendue : PONG
```

## 3.6 Nginx

```bash
sudo apt install nginx -y

# Verifier que Nginx tourne
sudo systemctl status nginx
```

Ouvrez votre navigateur et allez sur `http://203.0.113.50`. Vous devriez voir la page d'accueil Nginx.

## 3.7 Git

```bash
sudo apt install git -y
git --version
```

## 3.8 Supervision : Supervisor (pour les queues Laravel)

```bash
sudo apt install supervisor -y
sudo systemctl enable supervisor
```

---

# 4. DEPLOIEMENT DE L'APPLICATION

## 4.1 Preparer la structure des dossiers

```bash
# Creer le dossier de l'application
sudo mkdir -p /var/www/smartmenu
sudo chown deploy:deploy /var/www/smartmenu
```

## 4.2 Configurer Git sur le serveur

```bash
# En tant que deploy
git config --global user.name "Deploy"
git config --global user.email "deploy@smartmenu.com"
```

### Option A : Depot prive (GitHub/GitLab) avec cle SSH

```bash
# Generer une cle SSH pour le serveur
ssh-keygen -t ed25519 -C "deploy@serveur"

# Afficher la cle publique
cat ~/.ssh/id_ed25519.pub
```

Copiez cette cle et ajoutez-la dans :
- **GitHub** → Settings → SSH and GPG keys → New SSH key
- **GitLab** → Preferences → SSH Keys

### Option B : Depot prive avec token HTTPS

```bash
git clone https://VOTRE_TOKEN@github.com/votre-user/menu-qr-app.git /var/www/smartmenu
```

## 4.3 Cloner le projet

```bash
cd /var/www
git clone git@github.com:votre-user/menu-qr-app.git smartmenu
cd smartmenu
```

## 4.4 Installer les dependances PHP

```bash
# IMPORTANT : --no-dev exclut les paquets de developpement
composer install --no-dev --optimize-autoloader
```

> **`--optimize-autoloader`** genere un autoloader optimise = chargement des classes plus rapide.
> **`--no-dev`** n'installe pas phpunit, faker, etc. = plus leger et plus securise.

## 4.5 Installer les dependances Node.js et builder les assets

```bash
npm ci
npm run build
```

> **`npm ci`** est plus fiable que `npm install` en production.
> Il utilise exactement les versions du `package-lock.json`.

## 4.6 Configurer les variables d'environnement

```bash
cp .env.example .env
nano .env
```

Voici le `.env` de production complet :

```env
APP_NAME=SmartMenu
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Africa/Abidjan
APP_URL=https://votre-domaine.com

# IMPORTANT : APP_DEBUG=false en production !
# Sinon vos erreurs affichent des infos sensibles (mots de passe DB, etc.)

LOG_CHANNEL=daily
LOG_LEVEL=error

# Base de donnees
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartmenu
DB_USERNAME=smartmenu_user
DB_PASSWORD=VotreMotDePasse_Tres_Fort_2026!

# Cache et Sessions via Redis (plus performant que file/database)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (configurez selon votre fournisseur)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votre-domaine.com
MAIL_FROM_NAME="SmartMenu"

# Pas de Vite en production (les assets sont deja compiles)
VITE_APP_NAME=SmartMenu
```

### Generer la cle d'application

```bash
php artisan key:generate
```

> **CRITIQUE** : Ne partagez JAMAIS votre fichier `.env`. Ne le commitez JAMAIS dans Git.

## 4.7 Permissions des dossiers

```bash
# Le serveur web (www-data) doit pouvoir ecrire dans storage et cache
sudo chown -R deploy:www-data /var/www/smartmenu
sudo chmod -R 775 storage bootstrap/cache

# Creer le lien symbolique pour le stockage public (logos, photos)
php artisan storage:link
```

## 4.8 Executer les migrations

```bash
php artisan migrate --force
```

> **`--force`** est necessaire en production car Laravel refuse de migrer sans ce flag
> quand `APP_ENV=production` (protection contre les migrations accidentelles).

## 4.9 Optimisations Laravel pour la production

```bash
# Cache les routes (chargement ~5x plus rapide)
php artisan route:cache

# Cache la configuration (ne lit plus .env a chaque requete)
php artisan config:cache

# Cache les vues Blade (compilation anticipee)
php artisan view:cache

# Cache les evenements
php artisan event:cache
```

> **IMPORTANT** : Apres chaque deploiement, vous devez re-executer ces commandes.
> Si vous modifiez `.env`, lancez `php artisan config:cache` pour appliquer les changements.

## 4.10 Seeder initial (si necessaire)

```bash
# Uniquement la premiere fois
php artisan db:seed --force
```

---

# 5. CONFIGURATION DU DOMAINE

## 5.1 Ou acheter un domaine ?

| Registrar | Prix .com/an | Avantages |
|-----------|-------------|-----------|
| **Namecheap** | ~9 USD | Pas cher, WHOIS privacy gratuit |
| **Cloudflare Registrar** | ~9 USD | Prix coutant, DNS integre |
| **OVH** | ~10 EUR | Francais, DNS inclus |
| **Google Domains** (via Squarespace) | ~12 USD | Simple |
| **GoDaddy** | ~15 USD | Eviter (cher au renouvellement) |

> **Recommandation** : **Cloudflare Registrar** ou **Namecheap**.

## 5.2 Configuration DNS

Allez dans le panel DNS de votre registrar et ajoutez :

| Type | Nom | Valeur | TTL |
|------|-----|--------|-----|
| **A** | `@` | `203.0.113.50` | 3600 |
| **A** | `www` | `203.0.113.50` | 3600 |

> `@` = domaine racine (votre-domaine.com)
> `www` = sous-domaine www (www.votre-domaine.com)

## 5.3 Verifier la propagation DNS

```bash
# Depuis votre PC
nslookup votre-domaine.com

# Ou en ligne : https://dnschecker.org
```

> La propagation DNS peut prendre de **5 minutes a 48 heures**.
> En general, c'est fait en 5-30 minutes.

---

# 6. REVERSE PROXY NGINX

## 6.1 Creer la configuration Nginx

```bash
sudo nano /etc/nginx/sites-available/smartmenu
```

Collez cette configuration **complete** :

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name votre-domaine.com www.votre-domaine.com;

    root /var/www/smartmenu/public;
    index index.php;

    # Taille max des uploads (photos de plats, logos)
    client_max_body_size 20M;

    # Logs
    access_log /var/log/nginx/smartmenu-access.log;
    error_log /var/log/nginx/smartmenu-error.log;

    # Securite headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_comp_level 5;
    gzip_min_length 256;
    gzip_types
        application/javascript
        application/json
        application/xml
        text/css
        text/javascript
        text/plain
        image/svg+xml;

    # Assets statiques - cache longue duree
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Laravel : toutes les requetes passent par index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;

        # Timeouts pour les requetes longues (PDF generation, etc.)
        fastcgi_read_timeout 120;
    }

    # Bloquer l'acces aux fichiers sensibles
    location ~ /\.(?!well-known) {
        deny all;
    }

    location ~ \.(env|log|md)$ {
        deny all;
    }
}
```

## 6.2 Activer le site

```bash
# Creer le lien symbolique
sudo ln -s /etc/nginx/sites-available/smartmenu /etc/nginx/sites-enabled/

# Supprimer le site par defaut
sudo rm /etc/nginx/sites-enabled/default

# Tester la configuration (TOUJOURS faire ca avant de redemarrer)
sudo nginx -t
```

Si le test dit `syntax is ok` et `test is successful` :

```bash
sudo systemctl reload nginx
```

> **Erreur frequente** : Si `nginx -t` echoue, lisez l'erreur attentivement.
> C'est souvent un `;` manquant ou un chemin incorrect.

---

# 7. HTTPS ET SSL

## 7.1 Installer Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
```

## 7.2 Generer le certificat SSL

```bash
sudo certbot --nginx -d votre-domaine.com -d www.votre-domaine.com
```

Il va vous demander :
1. Votre email → pour les notifications d'expiration
2. Accepter les conditions → **Y**
3. Partager l'email avec EFF → **N** (optionnel)

Certbot modifie automatiquement votre config Nginx pour ajouter HTTPS et la redirection HTTP → HTTPS.

## 7.3 Verifier le renouvellement automatique

```bash
# Tester le renouvellement (simulation)
sudo certbot renew --dry-run
```

Si ca dit "Congratulations", le renouvellement automatique est configure.

> Les certificats Let's Encrypt expirent tous les 90 jours.
> Certbot les renouvelle automatiquement via un timer systemd.

```bash
# Verifier que le timer est actif
sudo systemctl status certbot.timer
```

## 7.4 Tester votre HTTPS

Allez sur `https://votre-domaine.com`. Vous devriez voir le cadenas vert.

Test avance : https://www.ssllabs.com/ssltest/analyze.html?d=votre-domaine.com

---

# 8. QUEUES ET WORKERS LARAVEL

## 8.1 Configurer Supervisor pour les queues

```bash
sudo nano /etc/supervisor/conf.d/smartmenu-worker.conf
```

```ini
[program:smartmenu-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/smartmenu/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/smartmenu/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start smartmenu-worker:*

# Verifier
sudo supervisorctl status
```

---

# 9. CRON JOB LARAVEL

```bash
sudo crontab -u deploy -e
```

Ajoutez cette ligne :

```
* * * * * cd /var/www/smartmenu && php artisan schedule:run >> /dev/null 2>&1
```

> Cela execute le scheduler Laravel chaque minute.
> Laravel decide ensuite quelles taches lancer selon votre `app/Console/Kernel.php`.

---

# 10. BACKUPS AUTOMATIQUES

## 10.1 Script de backup base de donnees

```bash
sudo mkdir -p /var/backups/smartmenu
sudo chown deploy:deploy /var/backups/smartmenu

nano /home/deploy/backup-db.sh
```

```bash
#!/bin/bash
# Backup de la base de donnees SmartMenu

BACKUP_DIR="/var/backups/smartmenu"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="smartmenu"
DB_USER="smartmenu_user"
DB_PASS="VotreMotDePasse_Tres_Fort_2026!"

# Creer le backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Supprimer les backups de plus de 30 jours
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete

echo "Backup termine : db_$DATE.sql.gz"
```

```bash
chmod +x /home/deploy/backup-db.sh
```

## 10.2 Automatiser avec cron

```bash
crontab -e
```

Ajoutez :

```
# Backup DB tous les jours a 3h du matin
0 3 * * * /home/deploy/backup-db.sh >> /var/log/smartmenu-backup.log 2>&1

# Backup fichiers uploads tous les dimanches a 4h
0 4 * * 0 tar -czf /var/backups/smartmenu/uploads_$(date +\%Y\%m\%d).tar.gz /var/www/smartmenu/storage/app/public
```

## 10.3 Restaurer un backup

```bash
# Restaurer la base de donnees
gunzip < /var/backups/smartmenu/db_20260213_030000.sql.gz | mysql -u smartmenu_user -p smartmenu
```

---

# 11. MONITORING ET LOGS

## 11.1 Logs Laravel

```bash
# Voir les derniers logs en temps reel
tail -f /var/www/smartmenu/storage/logs/laravel.log

# Logs d'aujourd'hui (si LOG_CHANNEL=daily)
cat /var/www/smartmenu/storage/logs/laravel-$(date +%Y-%m-%d).log
```

## 11.2 Logs Nginx

```bash
# Logs d'acces
tail -f /var/log/nginx/smartmenu-access.log

# Logs d'erreur
tail -f /var/log/nginx/smartmenu-error.log
```

## 11.3 Surveillance systeme

```bash
# Utilisation RAM/CPU en temps reel
htop

# Espace disque
df -h

# Utilisation memoire
free -m

# Processus qui consomment le plus
top -o %MEM
```

## 11.4 Outils recommandes

| Outil | Usage | Installation |
|-------|-------|-------------|
| **htop** | Monitoring CPU/RAM en terminal | `sudo apt install htop` |
| **ncdu** | Analyser l'espace disque | `sudo apt install ncdu` |
| **UptimeRobot** | Alertes si site down (gratuit) | uptimerobot.com |
| **Netdata** | Dashboard monitoring complet | `bash <(curl -Ss https://my-netdata.io/kickstart.sh)` |

---

# 12. SCRIPT DE DEPLOIEMENT

## 12.1 Script deploy.sh

Creez ce script pour automatiser les futurs deploiements :

```bash
nano /var/www/smartmenu/deploy.sh
```

```bash
#!/bin/bash
set -e

echo "=========================================="
echo " DEPLOIEMENT SMARTMENU"
echo " $(date)"
echo "=========================================="

cd /var/www/smartmenu

# 1. Mode maintenance
echo "[1/9] Activation du mode maintenance..."
php artisan down --render="errors::503"

# 2. Pull du code
echo "[2/9] Mise a jour du code..."
git pull origin main

# 3. Dependances PHP
echo "[3/9] Installation des dependances PHP..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Dependances Node.js + Build
echo "[4/9] Build des assets..."
npm ci
npm run build

# 5. Migrations
echo "[5/9] Migrations base de donnees..."
php artisan migrate --force

# 6. Cache
echo "[6/9] Optimisation du cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Permissions
echo "[7/9] Verification des permissions..."
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 8. Redemarrer les services
echo "[8/9] Redemarrage des services..."
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart smartmenu-worker:*

# 9. Sortir du mode maintenance
echo "[9/9] Remise en ligne..."
php artisan up

echo ""
echo "=========================================="
echo " DEPLOIEMENT TERMINE AVEC SUCCES !"
echo "=========================================="
```

```bash
chmod +x /var/www/smartmenu/deploy.sh
```

### Utilisation :

```bash
cd /var/www/smartmenu
./deploy.sh
```

---

# 13. ERREURS FREQUENTES ET SOLUTIONS

## 13.1 Page blanche / Erreur 500

```bash
# Verifier les logs Laravel
tail -50 /var/www/smartmenu/storage/logs/laravel.log

# Causes les plus frequentes :
# 1. Permissions storage/
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R deploy:www-data storage bootstrap/cache

# 2. Cle d'application manquante
php artisan key:generate

# 3. Cache corrompue
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 13.2 "SQLSTATE[HY000] [2002] Connection refused"

```bash
# MySQL ne tourne pas
sudo systemctl status mysql
sudo systemctl start mysql

# Ou mauvais credentials dans .env
# Verifiez DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

## 13.3 "413 Request Entity Too Large"

```bash
# Nginx bloque les uploads trop gros
sudo nano /etc/nginx/sites-available/smartmenu
# Augmentez client_max_body_size
# client_max_body_size 20M;
sudo nginx -t && sudo systemctl reload nginx
```

## 13.4 Assets CSS/JS ne chargent pas (404)

```bash
# Verifiez que le build a ete fait
ls /var/www/smartmenu/public/build/

# Si le dossier est vide, rebuildez
npm run build

# Verifiez le lien symbolique storage
php artisan storage:link
```

## 13.5 "Permission denied" dans les logs

```bash
sudo chown -R deploy:www-data /var/www/smartmenu/storage
sudo chmod -R 775 /var/www/smartmenu/storage
```

---

# 14. BONNES PRATIQUES PRODUCTION

## 14.1 Securite

- [ ] `APP_DEBUG=false` (JAMAIS true en production)
- [ ] `APP_ENV=production`
- [ ] Mot de passe DB fort (min 20 caracteres)
- [ ] HTTPS partout (SSL active)
- [ ] Firewall UFW actif
- [ ] Fail2ban actif
- [ ] Root SSH desactive
- [ ] Fichier `.env` non versionne dans Git
- [ ] Headers de securite dans Nginx

## 14.2 Performance

- [ ] `opcache` active dans PHP
- [ ] Cache Laravel (config, route, view)
- [ ] Redis pour sessions et cache
- [ ] Gzip dans Nginx
- [ ] Cache longue duree sur les assets statiques
- [ ] `--no-dev` pour Composer

## 14.3 Maintenance reguliere

```bash
# Chaque semaine
sudo apt update && sudo apt upgrade -y

# Chaque mois
sudo certbot renew       # Normalement automatique
php artisan optimize     # Re-optimiser le cache

# Verifier l'espace disque
df -h
ncdu /var/www/smartmenu/storage
```

## 14.4 Architecture finale

```
VPS (Ubuntu 22.04)
├── Nginx (port 80/443) ← Reverse proxy + SSL + assets statiques
│   └── PHP-FPM (socket) ← Execute le code Laravel
│       ├── MySQL (port 3306) ← Base de donnees
│       ├── Redis (port 6379) ← Cache, sessions, queues
│       └── Supervisor ← Workers queue
├── Certbot ← Renouvellement SSL automatique
├── Fail2ban ← Protection anti-bruteforce
├── UFW Firewall ← Ports 22, 80, 443 uniquement
└── Cron
    ├── Laravel Scheduler (chaque minute)
    ├── Backup DB (chaque nuit)
    └── Backup uploads (chaque semaine)
```

---

# 15. CHECKLIST FINALE DE DEPLOIEMENT

```
AVANT DE LANCER :
[ ] VPS achete et accessible via SSH
[ ] Utilisateur non-root cree
[ ] Root SSH desactive
[ ] UFW actif (22, 80, 443)
[ ] Fail2ban installe

ENVIRONNEMENT :
[ ] PHP 8.2 + extensions installees
[ ] Composer installe
[ ] Node.js 20 LTS installe
[ ] MySQL 8 installe et securise
[ ] Redis installe
[ ] Nginx installe
[ ] Supervisor installe
[ ] Git installe

APPLICATION :
[ ] Code clone dans /var/www/smartmenu
[ ] composer install --no-dev --optimize-autoloader
[ ] npm ci && npm run build
[ ] .env configure (APP_DEBUG=false, credentials DB, etc.)
[ ] php artisan key:generate
[ ] php artisan migrate --force
[ ] php artisan storage:link
[ ] Permissions storage/ et bootstrap/cache/ correctes
[ ] php artisan config:cache
[ ] php artisan route:cache
[ ] php artisan view:cache

DOMAINE ET SSL :
[ ] DNS configure (A record → IP du VPS)
[ ] Nginx configure avec le bon domaine
[ ] Certbot SSL installe et certificat genere
[ ] HTTP redirige vers HTTPS
[ ] APP_URL dans .env correspond au domaine

MAINTENANCE :
[ ] Cron scheduler Laravel actif
[ ] Supervisor workers actifs
[ ] Script backup configure
[ ] Script deploy.sh cree
[ ] UptimeRobot configure (monitoring)
```

---

> **Felicitations !** Si vous avez suivi chaque etape, votre application SmartMenu
> est en production, securisee, optimisee et facile a maintenir.
>
> Pour deployer une mise a jour, il suffit de lancer `./deploy.sh` depuis le serveur.
