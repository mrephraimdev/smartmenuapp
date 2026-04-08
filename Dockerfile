# ===========================================
# SmartMenu SaaS - Production Dockerfile
# Multi-stage build for optimized image size
# ===========================================

# Stage 1: Composer dependencies
FROM composer:2.6 AS composer-builder

WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies without dev packages
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# Copy application source
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# ===========================================
# Stage 2: Node.js build for frontend assets
# ===========================================
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install Node dependencies
RUN npm ci --production=false

# Copy source files needed for build
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./

# Build production assets
RUN npm run build

# ===========================================
# Stage 3: Final production image
# ===========================================
FROM php:8.2-fpm-alpine

# Set labels
LABEL maintainer="SmartMenu Team"
LABEL description="SmartMenu SaaS Application"
LABEL version="1.0"

# Install system dependencies
RUN apk add --no-cache \
    # PostgreSQL driver
    postgresql-dev \
    # Image processing
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    # ZIP support
    libzip-dev \
    zip \
    unzip \
    # Git for some packages
    git \
    # Supervisor for process management
    supervisor \
    # Nginx (optional, if not using separate container)
    nginx \
    # Other utilities
    curl \
    icu-dev

# Configure and install PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pdo_mysql \
    pgsql \
    gd \
    zip \
    opcache \
    intl \
    bcmath \
    pcntl

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Configure OPcache for production
RUN { \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=64'; \
    echo 'opcache.max_accelerated_files=30000'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.save_comments=1'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Configure PHP for production
RUN { \
    echo 'memory_limit=256M'; \
    echo 'upload_max_filesize=20M'; \
    echo 'post_max_size=25M'; \
    echo 'max_execution_time=60'; \
    echo 'expose_php=Off'; \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'error_log=/var/log/php/error.log'; \
} > /usr/local/etc/php/conf.d/production.ini

# Create directories and set permissions
RUN mkdir -p /var/log/php /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/log/php

# Set working directory
WORKDIR /var/www/html

# Copy application from composer builder
COPY --from=composer-builder /app/vendor ./vendor

# Copy built assets from node builder
COPY --from=node-builder /app/public/build ./public/build

# Copy application source
COPY --chown=www-data:www-data . .

# Ensure storage and cache directories are writable
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    && chmod -R 775 \
    storage \
    bootstrap/cache

# Copy supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy PHP-FPM configuration
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Create healthcheck script
RUN echo '#!/bin/sh' > /healthcheck.sh \
    && echo 'php -r "echo \"OK\";" || exit 1' >> /healthcheck.sh \
    && chmod +x /healthcheck.sh

# Expose port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD /healthcheck.sh

# Switch to non-root user
USER www-data

# Start PHP-FPM
CMD ["php-fpm"]
