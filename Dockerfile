# Utiliser l'image PHP officielle avec Apache
FROM php:8.2-apache

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libonig-dev curl \
    && docker-php-ext-install intl pdo_mysql zip opcache

# Activer mod_rewrite pour Apache (utile pour Symfony)
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installer Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Copier les fichiers de l'application dans le conteneur
WORKDIR /var/www
COPY . /var/www

# Donner les permissions à Apache
RUN chown -R www-data:www-data /var/www

# Autoriser Composer à s'exécuter en tant que root et exécuter les scripts
ENV COMPOSER_ALLOW_SUPERUSER=1

# Installer les dépendances PHP via Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Supprimer le dossier html par défaut et pointer vers /var/www/public
RUN rm -rf /var/www/html && ln -s /var/www/public /var/www/html

# Configurer Apache pour Symfony
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Exposer le port
EXPOSE 80

# Lancer Apache
CMD ["apache2-foreground"]