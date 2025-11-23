# Étape 1 : Image de base avec PHP 8.4 FPM
FROM php:8.4-fpm

# Étape 2 : Installation des dépendances système
RUN apt-get update && apt-get install -y \
    curl \
    wget \
    git \
    unzip \
    zip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libssl-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Étape 3 : Installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    zip \
    bcmath \
    opcache \
    xml \
    gd \
    sockets

# Étape 4 : Installation de Xdebug UNIQUEMENT si env=local
ARG APP_ENV=production
RUN if [ "$APP_ENV" = "local" ]; then \
    pecl install xdebug && docker-php-ext-enable xdebug; \
    fi

# Étape 5 : Configuration PHP (commune à tous les envs)
RUN { \
    echo 'memory_limit = 256M'; \
    echo 'upload_max_filesize = 64M'; \
    echo 'post_max_size = 64M'; \
    echo 'max_execution_time = 300'; \
    echo 'date.timezone = Africa/Douala'; \
} > /usr/local/etc/php/conf.d/custom.ini

# Étape 6 : Configuration OPcache (seulement en production)
RUN if [ "$APP_ENV" != "local" ]; then \
    { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=60'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini; \
    fi

# Étape 7 : Configuration Xdebug (seulement en local)
RUN if [ "$APP_ENV" = "local" ]; then \
    { \
    echo 'xdebug.mode=develop,debug'; \
    echo 'xdebug.start_with_request=yes'; \
    echo 'xdebug.client_host=host.docker.internal'; \
    echo 'xdebug.client_port=9003'; \
    } > /usr/local/etc/php/conf.d/xdebug.ini; \
    fi

# Étape 8 : Installation de Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Étape 9 : Création de l'utilisateur application
RUN groupadd -g 1000 www-app \
    && useradd -u 1000 -ms /bin/bash -g www-app www-app

# Étape 10 : Configuration du répertoire de travail
WORKDIR /var/www/html

# Étape 11 : Copie des fichiers de l'application
COPY --chown=www-app:www-app . .

# Étape 12 : Installation des dépendances Composer
RUN if [ "$APP_ENV" = "production" ]; then \
    composer install --no-dev --no-interaction --no-progress --optimize-autoloader; \
    else \
    composer install --no-interaction --no-progress --optimize-autoloader; \
    fi

# Étape 13 : Configuration des permissions
RUN chown -R www-app:www-app /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Étape 14 : Passage à l'utilisateur non-root
USER www-app

# Étape 15 : Exposition du port PHP-FPM
EXPOSE 9000

# Étape 16 : Commande de démarrage
CMD ["php-fpm"]
