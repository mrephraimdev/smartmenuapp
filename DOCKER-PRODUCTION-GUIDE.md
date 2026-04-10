# GUIDE COMPLET : Deployer SmartMenu avec Docker sur un VPS
## Du zero absolu a la production

> **Application** : SmartMenu (Laravel 12 + MySQL + Nginx + Node.js/Vite + Redis)
> **Niveau** : Debutant Docker
> **Objectif** : Comprendre, puis appliquer

---

# 1. C'EST QUOI DOCKER ?

## 1.1 Explication simple

Imaginez que vous demenagez. Vous avez deux options :

**Sans Docker (installation classique)** :
Vous arrivez dans une maison vide. Vous devez acheter les meubles un par un,
les assembler, les placer. Si un meuble ne rentre pas, vous devez adapter.
Si vous demenagez encore, vous recommencez tout a zero.

**Avec Docker** :
Vous emballez toute votre maison dans des **conteneurs de transport**.
Chaque conteneur contient une piece complete (cuisine, salon, chambre).
Vous les posez n'importe ou dans le monde, vous ouvrez, tout est deja en place.

En termes techniques :

```
SANS DOCKER :
┌─────────────────────────────────────┐
│           VOTRE SERVEUR             │
│                                     │
│  PHP 8.2 (installe manuellement)   │
│  MySQL 8 (installe manuellement)   │
│  Nginx (installe manuellement)     │
│  Redis (installe manuellement)     │
│  Node.js (installe manuellement)   │
│                                     │
│  → Tout est melange sur le serveur  │
│  → Si PHP plante, ca peut affecter │
│    MySQL                            │
│  → Difficile a reproduire ailleurs  │
└─────────────────────────────────────┘

AVEC DOCKER :
┌─────────────────────────────────────────────────┐
│                 VOTRE SERVEUR                    │
│                                                  │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐        │
│  │ Container│ │ Container│ │ Container│        │
│  │   APP    │ │  MySQL   │ │  Redis   │        │
│  │ PHP+Nginx│ │          │ │          │        │
│  └──────────┘ └──────────┘ └──────────┘        │
│                                                  │
│  → Chacun est isole dans sa boite               │
│  → Si l'app plante, MySQL continue              │
│  → Reproductible a l'identique partout          │
└─────────────────────────────────────────────────┘
```

## 1.2 Les 4 concepts cles

### IMAGE = La recette

Une image Docker, c'est comme une **recette de cuisine**.
Elle decrit exactement ce qu'il faut installer et comment configurer.

```
Image "php:8.2-fpm" contient :
- Linux minimal (Alpine ou Debian)
- PHP 8.2 deja installe
- PHP-FPM configure
→ Prete a l'emploi, identique pour tout le monde
```

Vous ne modifiez jamais une image. Vous la telechargez et l'utilisez.

### CONTAINER = Le plat cuisine

Un container, c'est une image **en cours d'execution**.
C'est la recette qui a ete cuisinee et qui tourne.

```
Image (recette)    →    Container (plat en cours)
php:8.2-fpm        →    Mon serveur PHP qui tourne
mysql:8.0          →    Ma base de donnees qui tourne
redis:7            →    Mon cache qui tourne
```

Vous pouvez lancer **plusieurs containers** a partir de la meme image.

### DOCKERFILE = Votre recette personnalisee

Un Dockerfile, c'est **votre propre recette** basee sur une image existante.

```dockerfile
# Je pars de la recette PHP officielle
FROM php:8.2-fpm

# J'ajoute mes ingredients (extensions PHP)
RUN apt-get update && apt-get install -y libpng-dev
RUN docker-php-ext-install gd

# Je mets mon code dedans
COPY . /var/www/html

# Je dis comment lancer le tout
CMD ["php-fpm"]
```

### DOCKER COMPOSE = Le menu complet

Docker Compose permet de lancer **plusieurs containers ensemble**
avec un seul fichier de configuration.

```
docker-compose.yml :
  "Lance-moi :
   - 1 container PHP/Laravel
   - 1 container MySQL
   - 1 container Redis
   - 1 container Nginx
   Et connecte-les entre eux"
```

## 1.3 Comment ca fonctionne sur un serveur ?

```
VOTRE PC                          VOTRE VPS
─────────                         ─────────
                  git push
Code source    ──────────────►    Code source
                                      │
                                      ▼
                                 docker compose
                                  build + up
                                      │
                                      ▼
                               ┌──────────────┐
                               │   Container   │
                               │    Nginx      │◄── Internet (port 80/443)
                               │  (port 80)    │
                               └──────┬───────┘
                                      │
                               ┌──────▼───────┐
                               │   Container   │
                               │  PHP/Laravel  │
                               │  (port 9000)  │
                               └──────┬───────┘
                                      │
                          ┌───────────┼───────────┐
                          │                       │
                   ┌──────▼───────┐       ┌──────▼───────┐
                   │   Container   │       │   Container   │
                   │    MySQL      │       │    Redis      │
                   │  (port 3306)  │       │  (port 6379)  │
                   └──────────────┘       └──────────────┘
```

Les containers communiquent entre eux via un **reseau Docker prive**.
Seul Nginx est expose au monde exterieur.

---

# 2. DOIS-JE UTILISER DOCKER ?

## 2.1 Avantages

### Isolation
Chaque service vit dans sa boite. PHP ne peut pas casser MySQL.
Si un container plante, les autres continuent.

### Portabilite
Ca marche sur votre PC Windows ? Ca marchera a **l'identique** sur le VPS Linux.
Fini le "ca marche sur ma machine mais pas en prod".

### Deploiement simplifie
Au lieu de 50 commandes d'installation, vous faites :
```bash
docker compose up -d
```
Et tout est lance.

### Gestion des dependances
Plus de conflit entre PHP 8.1 et PHP 8.2. Chaque projet a sa propre version
dans son container, sans affecter les autres.

### Scalabilite
Besoin de plus de performance ? Lancez 3 containers PHP au lieu d'1 :
```bash
docker compose up -d --scale app=3
```

### Travail en equipe
Tout le monde a le meme environnement. Un nouveau developpeur clone le repo,
lance `docker compose up`, et c'est pret.

## 2.2 Inconvenients

### Complexite au debut
Il faut apprendre Docker, les Dockerfiles, les volumes, les reseaux.
Pour un debutant, ca ajoute une couche de complexite.

### Consommation de ressources
Docker utilise un peu plus de RAM qu'une installation directe.
Sur un VPS 1 Go de RAM, ca peut etre serre.

### Debug parfois plus difficile
Les logs sont dans les containers. Il faut savoir naviguer avec
`docker logs`, `docker exec`, etc.

### Mauvaise configuration possible
Un Dockerfile mal ecrit peut creer une image de 2 Go au lieu de 200 Mo.
Des volumes mal configures peuvent perdre vos donnees.

## 2.3 Recommandation claire

### Utilisez Docker SI :

- Vous avez **2 Go+ de RAM** sur votre VPS
- Vous voulez **pouvoir migrer** facilement de serveur
- Vous travaillez en **equipe**
- Vous avez **plusieurs projets** sur le meme serveur
- Vous voulez un deploiement **reproductible**

### Vous pouvez vous en passer SI :

- Vous avez un **petit VPS** (1 Go RAM)
- C'est votre **seul projet** sur ce serveur
- Vous voulez la **simplicite maximale** pour debuter
- Vous etes a l'aise avec l'installation manuelle (voir PRODUCTION-GUIDE.md)

### Pour SmartMenu : ma recommandation

> **Avec un VPS 2 Go+ RAM** : Utilisez Docker. C'est un investissement
> d'apprentissage qui vous servira toute votre carriere.
>
> **Avec un VPS 1 Go RAM** : Installez directement (voir PRODUCTION-GUIDE.md).
> Docker consommerait trop de ressources.

---

# 3. ARCHITECTURE RECOMMANDEE

## 3.1 Schema de l'architecture

```
                    INTERNET
                       │
                       ▼
              ┌─────────────────┐
              │     NGINX       │  ← Reverse proxy + SSL
              │   (Container)   │  ← Sert les fichiers statiques (CSS, JS, images)
              │    Port 80/443  │  ← Redirige les requetes PHP vers l'app
              └────────┬────────┘
                       │
            ┌──────────┼──────────┐
            │                     │
   ┌────────▼─────────┐         │
   │    APP Laravel    │         │
   │   (Container)     │         │
   │ PHP 8.2-FPM       │         │
   │ + Composer         │         │
   │ + Extensions       │         │
   │ Port 9000 (interne)│         │
   └────────┬─────────┘         │
            │                     │
   ┌────────┼─────────────────────┤
   │        │                     │
   │  ┌─────▼──────┐  ┌──────────▼────┐
   │  │   MySQL     │  │    Redis       │
   │  │ (Container) │  │  (Container)   │
   │  │ Port 3306   │  │  Port 6379     │
   │  │ (interne)   │  │  (interne)     │
   │  └─────────────┘  └───────────────┘
   │
   │  VOLUMES (donnees persistantes)
   │  ├── mysql_data/     ← Donnees MySQL (survit au redemarrage)
   │  ├── redis_data/     ← Donnees Redis
   │  └── storage/        ← Uploads (logos, photos plats)
```

## 3.2 Ce que fait chaque container

| Container | Role | Image de base | Ports |
|-----------|------|---------------|-------|
| **nginx** | Reverse proxy, sert les assets, SSL | `nginx:alpine` | 80, 443 (public) |
| **app** | Code Laravel, PHP-FPM | `php:8.2-fpm` (personnalisee) | 9000 (interne) |
| **mysql** | Base de donnees | `mysql:8.0` | 3306 (interne) |
| **redis** | Cache, sessions, queues | `redis:7-alpine` | 6379 (interne) |

> **Interne** = accessible uniquement par les autres containers, pas depuis Internet.
> **Public** = accessible depuis Internet via le firewall.

## 3.3 Pourquoi des Volumes ?

Sans volume, quand vous arretez un container, **toutes ses donnees disparaissent**.
Un volume est un dossier partage entre le container et le serveur.

```
Container MySQL s'arrete → Les donnees sont dans le volume → Rien n'est perdu
Container MySQL redemarre → Il retrouve ses donnees dans le volume
```

---

# 4. INSTALLATION DE DOCKER SUR LE VPS

## 4.1 Prerequis

Vous devez avoir suivi les sections 1-2 du PRODUCTION-GUIDE.md :
- VPS Ubuntu 22.04 LTS
- Utilisateur non-root (deploy)
- SSH configure
- Firewall UFW actif

## 4.2 Installer Docker Engine

```bash
# Mettre a jour les paquets
sudo apt update

# Installer les prerequis
sudo apt install -y \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

# Ajouter la cle GPG officielle Docker
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Ajouter le depot Docker
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Installer Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Verifier l'installation
docker --version
# Docker version 27.x.x

docker compose version
# Docker Compose version v2.x.x
```

## 4.3 Permettre a votre utilisateur d'utiliser Docker sans sudo

```bash
# Ajouter l'utilisateur deploy au groupe docker
sudo usermod -aG docker deploy

# IMPORTANT : Deconnectez-vous et reconnectez-vous pour que ca prenne effet
exit
ssh deploy@203.0.113.50

# Tester sans sudo
docker run hello-world
```

Vous devriez voir "Hello from Docker!" - ca veut dire que tout marche.

## 4.4 Configurer Docker pour demarrer au boot

```bash
sudo systemctl enable docker
sudo systemctl enable containerd
```

---

# 5. DOCKERISER SMARTMENU

## 5.1 Structure des fichiers Docker

Voici les fichiers a creer dans votre projet :

```
menu-qr-app/
├── docker/
│   ├── nginx/
│   │   └── default.conf        ← Config Nginx
│   └── php/
│       └── local.ini           ← Config PHP
├── Dockerfile                   ← Recette pour l'image de l'app
├── docker-compose.yml           ← Orchestration de tous les containers
├── docker-compose.prod.yml      ← Surcharges specifiques a la production
└── .dockerignore                ← Fichiers a exclure de l'image
```

## 5.2 Le fichier .dockerignore

Ce fichier dit a Docker **quoi ne PAS inclure** dans l'image.
C'est comme un .gitignore mais pour Docker.

```
# A creer a la racine du projet : .dockerignore

node_modules
vendor
.git
.env
.env.local
.env.production
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
bootstrap/cache/*
tests
phpunit.xml
.vscode
.idea
*.md
docker-compose*.yml
Dockerfile
```

> **Pourquoi ?** Sans ce fichier, Docker copie TOUT dans l'image,
> y compris node_modules (500 Mo+), .git (tout l'historique), etc.
> Resultat : une image de 2 Go au lieu de 200 Mo.

## 5.3 Le Dockerfile (explique ligne par ligne)

```dockerfile
# =============================================================================
# ETAPE 1 : BUILDER LES ASSETS (Node.js)
# =============================================================================
# On utilise une image Node.js temporaire juste pour compiler CSS/JS
# Cette image sera jetee apres, elle ne sera PAS dans l'image finale
FROM node:20-alpine AS node-builder

# Definir le dossier de travail dans le container
WORKDIR /app

# Copier UNIQUEMENT les fichiers de dependances Node
# Docker met en cache cette etape. Si package.json n'a pas change,
# il ne reinstalle pas les dependances (= build plus rapide)
COPY package.json package-lock.json ./

# Installer les dependances Node
RUN npm ci

# Maintenant copier le reste du code source
COPY resources/ resources/
COPY vite.config.js postcss.config.js tailwind.config.js ./

# Compiler les assets pour la production
RUN npm run build

# A ce stade, les fichiers compiles sont dans /app/public/build/


# =============================================================================
# ETAPE 2 : IMAGE PHP DE L'APPLICATION
# =============================================================================
FROM php:8.2-fpm-alpine

# Installer les dependances systeme necessaires
RUN apk add --no-cache \
    # Pour l'extension GD (images, DomPDF)
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    # Pour l'extension ZIP
    libzip-dev \
    # Pour l'extension intl
    icu-dev \
    # Pour l'extension imagick (QR codes PNG)
    imagemagick-dev \
    # Outils utiles
    git \
    curl \
    supervisor

# Installer les extensions PHP necessaires pour Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    gd \
    zip \
    bcmath \
    intl \
    opcache \
    pcntl

# Installer l'extension Redis
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Installer Composer (gestionnaire de paquets PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir le dossier de travail
WORKDIR /var/www/html

# Copier les fichiers de dependances PHP en premier (pour le cache Docker)
COPY composer.json composer.lock ./

# Installer les dependances PHP (sans les paquets de dev)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copier tout le code source
COPY . .

# Copier les assets compiles depuis l'etape 1 (le builder Node.js)
COPY --from=node-builder /app/public/build/ public/build/

# Finaliser l'installation Composer (genere l'autoloader optimise)
RUN composer dump-autoload --optimize

# Configurer les permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copier la config PHP personnalisee
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Exposer le port PHP-FPM (interne uniquement)
EXPOSE 9000

# Commande de demarrage
CMD ["php-fpm"]
```

### Pourquoi 2 etapes (multi-stage build) ?

```
SANS multi-stage :
Image finale = PHP + Node.js + node_modules + code
Taille : ~1.5 Go

AVEC multi-stage :
Image finale = PHP + code + assets compiles (sans Node.js)
Taille : ~250 Mo

→ 6x plus leger, plus rapide a deployer, plus securise
```

## 5.4 Configuration PHP pour Docker

```bash
# Creer le dossier
mkdir -p docker/php
```

Fichier `docker/php/local.ini` :

```ini
; Configuration PHP optimisee pour la production
upload_max_filesize = 20M
post_max_size = 25M
memory_limit = 256M
max_execution_time = 60

; OPcache (acceleration PHP)
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
opcache.interned_strings_buffer = 16

; Securite
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

## 5.5 Configuration Nginx pour Docker

```bash
mkdir -p docker/nginx
```

Fichier `docker/nginx/default.conf` :

```nginx
server {
    listen 80;
    server_name _;

    root /var/www/html/public;
    index index.php;

    # Taille max uploads
    client_max_body_size 20M;

    # Headers de securite
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip
    gzip on;
    gzip_comp_level 5;
    gzip_min_length 256;
    gzip_types application/javascript application/json text/css text/javascript text/plain image/svg+xml;

    # Assets statiques - cache longue duree
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Toutes les requetes passent par Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM : le nom "app" correspond au container dans docker-compose
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 120;
    }

    # Bloquer les fichiers sensibles
    location ~ /\.(?!well-known) {
        deny all;
    }
    location ~ \.(env|log|md)$ {
        deny all;
    }
}
```

> **`fastcgi_pass app:9000`** : Docker resout automatiquement `app`
> vers l'IP du container PHP. Pas besoin de connaitre l'IP.

---

# 6. DOCKER COMPOSE COMPLET

## 6.1 Fichier docker-compose.yml

```yaml
# =============================================================================
# docker-compose.yml - SmartMenu Production
# =============================================================================
# Pour lancer : docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

services:

  # ─────────────────────────────────────────────────────────────────────────
  # APPLICATION LARAVEL (PHP-FPM)
  # ─────────────────────────────────────────────────────────────────────────
  app:
    build:
      context: .                    # Le Dockerfile est a la racine
      dockerfile: Dockerfile
    container_name: smartmenu-app
    restart: unless-stopped         # Redemarre automatiquement sauf arret manuel
    working_dir: /var/www/html
    volumes:
      - app_storage:/var/www/html/storage/app    # Photos, logos (persistant)
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    env_file:
      - .env.docker                 # Variables d'environnement
    depends_on:
      mysql:
        condition: service_healthy  # Attend que MySQL soit pret
      redis:
        condition: service_started
    networks:
      - smartmenu-network

  # ─────────────────────────────────────────────────────────────────────────
  # NGINX (REVERSE PROXY)
  # ─────────────────────────────────────────────────────────────────────────
  nginx:
    image: nginx:alpine             # Image officielle Nginx (tres legere)
    container_name: smartmenu-nginx
    restart: unless-stopped
    ports:
      - "80:80"                     # HTTP : accessible depuis Internet
      - "443:443"                   # HTTPS : accessible depuis Internet
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      # ro = read-only (le container ne peut pas modifier la config)

      # Nginx a besoin d'acceder aux fichiers statiques de Laravel
      # On monte le dossier public en lecture seule
      - ./public:/var/www/html/public:ro

      # Certificats SSL (on les montera plus tard avec Certbot)
      - certbot_etc:/etc/letsencrypt:ro
      - certbot_var:/var/lib/letsencrypt
    depends_on:
      - app
    networks:
      - smartmenu-network

  # ─────────────────────────────────────────────────────────────────────────
  # MYSQL (BASE DE DONNEES)
  # ─────────────────────────────────────────────────────────────────────────
  mysql:
    image: mysql:8.0                # Image officielle MySQL 8.0
    container_name: smartmenu-mysql
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}    # Depuis .env.docker
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql   # CRUCIAL : donnees persistantes
    # PAS de "ports:" ici = MySQL n'est PAS accessible depuis Internet
    # Seuls les containers sur le meme reseau peuvent y acceder
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - smartmenu-network

  # ─────────────────────────────────────────────────────────────────────────
  # REDIS (CACHE + SESSIONS + QUEUES)
  # ─────────────────────────────────────────────────────────────────────────
  redis:
    image: redis:7-alpine           # Image officielle Redis (tres legere)
    container_name: smartmenu-redis
    restart: unless-stopped
    command: redis-server --maxmemory 100mb --maxmemory-policy allkeys-lru
    # maxmemory : limite la RAM utilisee par Redis
    # allkeys-lru : supprime les cles les moins recemment utilisees quand plein
    volumes:
      - redis_data:/data
    # PAS de "ports:" = Redis n'est PAS accessible depuis Internet
    networks:
      - smartmenu-network

  # ─────────────────────────────────────────────────────────────────────────
  # QUEUE WORKER (traitement des taches en arriere-plan)
  # ─────────────────────────────────────────────────────────────────────────
  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: smartmenu-queue
    restart: unless-stopped
    command: php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
    # Ce container utilise la MEME image que "app", mais lance les queues
    # au lieu de PHP-FPM
    env_file:
      - .env.docker
    depends_on:
      - app
      - redis
    networks:
      - smartmenu-network

  # ─────────────────────────────────────────────────────────────────────────
  # SCHEDULER (taches planifiees Laravel)
  # ─────────────────────────────────────────────────────────────────────────
  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: smartmenu-scheduler
    restart: unless-stopped
    command: >
      sh -c "while true; do php artisan schedule:run --no-interaction; sleep 60; done"
    # Execute le scheduler toutes les 60 secondes
    env_file:
      - .env.docker
    depends_on:
      - app
      - mysql
      - redis
    networks:
      - smartmenu-network

# =============================================================================
# VOLUMES (donnees persistantes)
# =============================================================================
volumes:
  mysql_data:          # Donnees MySQL - survit aux redemarrages
    driver: local
  redis_data:          # Donnees Redis
    driver: local
  app_storage:         # Fichiers uploades (logos, photos)
    driver: local
  certbot_etc:         # Certificats SSL
    driver: local
  certbot_var:         # Donnees Certbot
    driver: local

# =============================================================================
# RESEAUX
# =============================================================================
networks:
  smartmenu-network:
    driver: bridge     # Reseau prive entre les containers
```

## 6.2 Explication de chaque concept

### `restart: unless-stopped`
```
Le container redemarre automatiquement si :
- Il plante
- Le serveur redemarare

Il ne redemarre PAS si :
- Vous l'arretez manuellement (docker compose stop)
```

### `depends_on` avec `condition`
```yaml
depends_on:
  mysql:
    condition: service_healthy
```
```
"Ne demarre l'app que quand MySQL est VRAIMENT pret"
Sans ca, l'app peut demarrer avant MySQL et planter avec "Connection refused"
```

### `volumes` (persistance)
```yaml
volumes:
  - mysql_data:/var/lib/mysql
```
```
SANS volume :
  docker compose down → Donnees MySQL PERDUES
  docker compose up   → Base de donnees VIDE

AVEC volume :
  docker compose down → Donnees sauvees dans mysql_data
  docker compose up   → Donnees toujours la !
```

### `networks` (communication)
```
Tous les containers sur "smartmenu-network" peuvent se parler
en utilisant le NOM du service comme adresse :

  app → peut contacter "mysql" sur le port 3306
  app → peut contacter "redis" sur le port 6379
  nginx → peut contacter "app" sur le port 9000

MAIS depuis Internet :
  → SEUL nginx est accessible (ports 80 et 443)
  → MySQL, Redis, PHP sont INVISIBLES
```

## 6.3 Fichier .env.docker

```bash
# A creer a la racine du projet
```

```env
APP_NAME=SmartMenu
APP_ENV=production
APP_KEY=base64:VOTRE_CLE_ICI
APP_DEBUG=false
APP_TIMEZONE=Africa/Abidjan
APP_URL=https://votre-domaine.com

LOG_CHANNEL=daily
LOG_LEVEL=error

# Base de donnees
# IMPORTANT : le host est "mysql" (nom du container), PAS "127.0.0.1"
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=smartmenu
DB_USERNAME=smartmenu_user
DB_PASSWORD=VotreMotDePasse_Tres_Fort_2026!
DB_ROOT_PASSWORD=AutreMotDePasse_Root_Fort_2026!

# Cache via Redis
# IMPORTANT : le host est "redis" (nom du container)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votre-domaine.com
MAIL_FROM_NAME="SmartMenu"
```

> **Point crucial** : Dans Docker, `DB_HOST=mysql` et `REDIS_HOST=redis`
> (les noms des services dans docker-compose.yml, PAS localhost ou 127.0.0.1).

---

# 7. LANCER EN PRODUCTION

## 7.1 Premiere mise en place

```bash
# Se connecter au VPS
ssh deploy@203.0.113.50

# Cloner le projet
cd /var/www
git clone git@github.com:votre-user/menu-qr-app.git smartmenu
cd smartmenu

# Creer le fichier .env.docker
nano .env.docker
# (collez le contenu de la section 6.3 et adaptez)

# Builder et lancer tous les containers
docker compose up -d --build
```

> **`-d`** = detached mode (les containers tournent en arriere-plan)
> **`--build`** = rebuild les images (a faire la premiere fois et apres chaque modif du Dockerfile)

### Attendre que tout soit pret (30-60 secondes)

```bash
# Voir le statut de tous les containers
docker compose ps
```

Resultat attendu :
```
NAME                  STATUS                    PORTS
smartmenu-app         Up (healthy)              9000/tcp
smartmenu-nginx       Up                        0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp
smartmenu-mysql       Up (healthy)              3306/tcp
smartmenu-redis       Up                        6379/tcp
smartmenu-queue       Up
smartmenu-scheduler   Up
```

> Tous les containers doivent etre "Up". Si l'un est "Restarting", il y a un probleme.

## 7.2 Initialiser l'application

```bash
# Generer la cle d'application
docker compose exec app php artisan key:generate

# Executer les migrations
docker compose exec app php artisan migrate --force

# Creer le lien symbolique storage
docker compose exec app php artisan storage:link

# Optimiser pour la production
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Seeder (premiere fois uniquement)
docker compose exec app php artisan db:seed --force
```

> **`docker compose exec app`** = "execute cette commande DANS le container app"

## 7.3 Commandes quotidiennes

### Voir les logs

```bash
# Logs de tous les containers
docker compose logs

# Logs d'un container specifique
docker compose logs app
docker compose logs nginx
docker compose logs mysql

# Logs en temps reel (comme tail -f)
docker compose logs -f app

# Les 50 dernieres lignes
docker compose logs --tail 50 app
```

### Voir les logs Laravel

```bash
docker compose exec app tail -f storage/logs/laravel.log
```

### Redemarrer les containers

```bash
# Redemarrer tout
docker compose restart

# Redemarrer un seul container
docker compose restart app

# Arreter tout (les donnees sont preservees grace aux volumes)
docker compose down

# Relancer
docker compose up -d
```

### Entrer dans un container (pour debugger)

```bash
# Ouvrir un shell dans le container app
docker compose exec app sh

# Depuis le shell du container, vous pouvez :
php artisan tinker
php artisan migrate:status
ls storage/logs/
# etc.

# Pour sortir :
exit
```

### Voir l'utilisation des ressources

```bash
docker stats
```

```
CONTAINER            CPU %    MEM USAGE / LIMIT
smartmenu-app        0.50%    128MiB / 2GiB
smartmenu-nginx      0.01%    8MiB / 2GiB
smartmenu-mysql      1.20%    256MiB / 2GiB
smartmenu-redis      0.10%    12MiB / 2GiB
```

## 7.4 Mettre a jour l'application

```bash
cd /var/www/smartmenu

# Recuperer le nouveau code
git pull origin main

# Rebuilder et relancer
docker compose up -d --build

# Executer les migrations
docker compose exec app php artisan migrate --force

# Re-cacher
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Redemarrer les workers
docker compose restart queue
```

### Script de deploiement Docker

Creez `deploy-docker.sh` :

```bash
#!/bin/bash
set -e

echo "=========================================="
echo " DEPLOIEMENT SMARTMENU (Docker)"
echo " $(date)"
echo "=========================================="

cd /var/www/smartmenu

# 1. Mode maintenance
echo "[1/7] Mode maintenance..."
docker compose exec app php artisan down --render="errors::503"

# 2. Pull code
echo "[2/7] Mise a jour du code..."
git pull origin main

# 3. Rebuild et relance
echo "[3/7] Rebuild des containers..."
docker compose up -d --build

# 4. Migrations
echo "[4/7] Migrations..."
docker compose exec app php artisan migrate --force

# 5. Cache
echo "[5/7] Optimisation cache..."
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# 6. Workers
echo "[6/7] Redemarrage workers..."
docker compose restart queue scheduler

# 7. Remise en ligne
echo "[7/7] Remise en ligne..."
docker compose exec app php artisan up

echo ""
echo "=========================================="
echo " DEPLOIEMENT TERMINE !"
echo "=========================================="
```

```bash
chmod +x deploy-docker.sh
```

---

# 8. HTTPS AVEC CERTBOT + DOCKER

## 8.1 Methode recommandee : Certbot sur l'hote

La methode la plus simple est d'installer Certbot **sur le VPS directement**
(pas dans un container) et de monter les certificats dans le container Nginx.

```bash
# Installer Certbot sur le VPS
sudo apt install certbot -y

# Arreter Nginx temporairement (Certbot a besoin du port 80)
docker compose stop nginx

# Generer le certificat
sudo certbot certonly --standalone -d votre-domaine.com -d www.votre-domaine.com

# Relancer Nginx
docker compose start nginx
```

## 8.2 Mettre a jour la config Nginx pour HTTPS

Modifiez `docker/nginx/default.conf` :

```nginx
# Redirection HTTP → HTTPS
server {
    listen 80;
    server_name votre-domaine.com www.votre-domaine.com;
    return 301 https://$host$request_uri;
}

# HTTPS
server {
    listen 443 ssl;
    server_name votre-domaine.com www.votre-domaine.com;

    # Certificats SSL
    ssl_certificate /etc/letsencrypt/live/votre-domaine.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votre-domaine.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    root /var/www/html/public;
    index index.php;

    client_max_body_size 20M;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    gzip on;
    gzip_comp_level 5;
    gzip_min_length 256;
    gzip_types application/javascript application/json text/css text/javascript text/plain image/svg+xml;

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
    location ~ \.(env|log|md)$ {
        deny all;
    }
}
```

Mettez a jour le volume Nginx dans `docker-compose.yml` pour monter les certificats :

```yaml
  nginx:
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      - ./public:/var/www/html/public:ro
      - /etc/letsencrypt:/etc/letsencrypt:ro    # Certificats du VPS
```

```bash
docker compose restart nginx
```

## 8.3 Renouvellement automatique

```bash
# Creer un cron pour le renouvellement
sudo crontab -e
```

Ajoutez :

```
# Renouveler le certificat SSL tous les mois
0 3 1 * * certbot renew --pre-hook "cd /var/www/smartmenu && docker compose stop nginx" --post-hook "cd /var/www/smartmenu && docker compose start nginx"
```

---

# 9. BACKUPS AVEC DOCKER

## 9.1 Backup de la base de donnees

```bash
# Backup manuel
docker compose exec mysql mysqldump -u root -p"${DB_ROOT_PASSWORD}" smartmenu | gzip > backup_$(date +%Y%m%d).sql.gz
```

### Script automatique

```bash
nano /home/deploy/backup-docker.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/smartmenu"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup MySQL
docker compose -f /var/www/smartmenu/docker-compose.yml exec -T mysql \
    mysqldump -u root -p"MOT_DE_PASSE_ROOT" smartmenu | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Backup des uploads
docker compose -f /var/www/smartmenu/docker-compose.yml exec -T app \
    tar -czf - storage/app/public > "$BACKUP_DIR/uploads_$DATE.tar.gz"

# Supprimer les backups de plus de 30 jours
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup termine : $DATE"
```

```bash
chmod +x /home/deploy/backup-docker.sh

# Cron : tous les jours a 3h
crontab -e
# Ajoutez :
0 3 * * * /home/deploy/backup-docker.sh >> /var/log/smartmenu-backup.log 2>&1
```

## 9.2 Restaurer un backup

```bash
# Restaurer la base de donnees
gunzip < /var/backups/smartmenu/db_20260213_030000.sql.gz | \
    docker compose exec -T mysql mysql -u root -p"MOT_DE_PASSE_ROOT" smartmenu
```

---

# 10. ERREURS FREQUENTES DES DEBUTANTS

## 10.1 Oublier le .dockerignore

```
PROBLEME : Image de 2 Go, build de 15 minutes
CAUSE : node_modules et .git sont copies dans l'image
SOLUTION : Creer .dockerignore (voir section 5.2)
```

## 10.2 Utiliser localhost au lieu du nom du service

```
PROBLEME : "Connection refused" a MySQL ou Redis
CAUSE : DB_HOST=127.0.0.1 dans .env
SOLUTION : DB_HOST=mysql et REDIS_HOST=redis
           (noms des services dans docker-compose.yml)
```

## 10.3 Perdre les donnees MySQL

```
PROBLEME : docker compose down → donnees perdues
CAUSE : Pas de volume pour MySQL
SOLUTION : volumes: mysql_data:/var/lib/mysql

ATTENTION : "docker compose down -v" supprime les volumes !
Ne JAMAIS utiliser -v sauf si vous voulez tout effacer.
```

## 10.4 Container qui redemarrera en boucle

```bash
# Voir pourquoi le container plante
docker compose logs app

# Causes frequentes :
# 1. Erreur dans .env (cle manquante, DB credentials incorrects)
# 2. Migration echouee
# 3. Extension PHP manquante dans le Dockerfile
```

## 10.5 Mettre les secrets dans le Dockerfile

```dockerfile
# MAUVAIS (le mot de passe est dans l'image, visible par tout le monde)
ENV DB_PASSWORD=monsecret

# BON (les secrets sont dans .env.docker, pas dans l'image)
# Utiliser env_file dans docker-compose.yml
```

## 10.6 Ne pas rebuild apres une modif du Dockerfile

```bash
# MAUVAIS : l'ancienne image est utilisee
docker compose up -d

# BON : force le rebuild
docker compose up -d --build
```

## 10.7 Exposer MySQL/Redis sur Internet

```yaml
# MAUVAIS (MySQL est accessible depuis Internet)
mysql:
  ports:
    - "3306:3306"

# BON (MySQL est accessible UNIQUEMENT par les autres containers)
mysql:
  # Pas de "ports:" = interne uniquement
  networks:
    - smartmenu-network
```

---

# 11. WORKFLOW IDEAL

## 11.1 En developpement (votre PC)

```
1. Vous codez dans VS Code
2. Vous testez avec "php artisan serve" (ou Docker local)
3. Vous committez et pushez sur GitHub
```

## 11.2 Deploiement sur le VPS

```
1. SSH sur le VPS : ssh deploy@203.0.113.50
2. Lancer le script : ./deploy-docker.sh
3. Verifier : docker compose ps
4. Tester : ouvrir https://votre-domaine.com
```

## 11.3 Schema du workflow

```
VOTRE PC                    GITHUB                    VPS
──────────                  ──────                    ───
                git push
Modifier code ──────────► Repository ◄──── git pull
                                              │
                                         docker compose
                                          up -d --build
                                              │
                                              ▼
                                        Application en
                                         production !
```

## 11.4 Commandes a retenir

```bash
# STATUS
docker compose ps                    # Voir les containers
docker compose logs -f app           # Logs en temps reel
docker stats                         # Utilisation CPU/RAM

# GESTION
docker compose up -d --build         # Lancer/mettre a jour
docker compose down                  # Arreter tout
docker compose restart app           # Redemarrer un container

# LARAVEL DANS DOCKER
docker compose exec app php artisan migrate --force
docker compose exec app php artisan tinker
docker compose exec app php artisan config:cache

# DEBUG
docker compose exec app sh           # Shell dans le container
docker compose logs mysql             # Logs MySQL
docker compose exec mysql mysql -u root -p  # Console MySQL

# NETTOYAGE (attention, libere de l'espace)
docker system prune -a               # Supprimer images/containers inutilises
docker volume prune                   # Supprimer volumes orphelins (DANGER)
```

---

# 12. CHECKLIST DEPLOIEMENT DOCKER

```
PREREQUIS VPS :
[ ] Ubuntu 22.04 LTS
[ ] Utilisateur non-root (deploy)
[ ] SSH cle configuree, root desactive
[ ] UFW actif (ports 22, 80, 443)
[ ] Fail2ban installe
[ ] Docker + Docker Compose installes
[ ] deploy dans le groupe docker

FICHIERS DOCKER :
[ ] .dockerignore cree
[ ] Dockerfile cree et teste
[ ] docker/php/local.ini cree
[ ] docker/nginx/default.conf cree
[ ] docker-compose.yml cree
[ ] .env.docker configure (DB_HOST=mysql, REDIS_HOST=redis)

LANCEMENT :
[ ] docker compose up -d --build
[ ] docker compose ps → tous "Up"
[ ] php artisan key:generate
[ ] php artisan migrate --force
[ ] php artisan storage:link
[ ] php artisan config:cache + route:cache + view:cache

DOMAINE ET SSL :
[ ] DNS configure (A record → IP VPS)
[ ] Certificat SSL genere (Certbot)
[ ] Nginx configure pour HTTPS
[ ] APP_URL = https://votre-domaine.com

MAINTENANCE :
[ ] Script deploy-docker.sh cree
[ ] Script backup cree + cron configure
[ ] Renouvellement SSL automatique (cron)
```

---

# 13. DOCKER vs INSTALLATION DIRECTE - RESUME

| Critere | Installation directe | Docker |
|---------|---------------------|--------|
| **Complexite initiale** | Plus simple | Plus complexe |
| **Reproductibilite** | Difficile | Excellente |
| **Isolation** | Aucune | Totale |
| **Performance** | Legere avantage | Overhead minimal |
| **RAM minimum** | 1 Go | 2 Go |
| **Migration serveur** | Tout refaire | Clone rapide |
| **Multi-projets** | Conflits possibles | Aucun conflit |
| **Debug** | Direct | Via docker exec |
| **Mise a jour** | Commande par commande | docker compose up --build |
| **Competences requises** | Linux/sysadmin | Linux + Docker |

> **Verdict** : Si vous avez 2 Go+ de RAM et voulez investir dans
> votre avenir de developpeur, utilisez Docker. Sinon, suivez le
> PRODUCTION-GUIDE.md pour une installation directe.
