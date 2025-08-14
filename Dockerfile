# Image PHP + Apache
FROM php:8.2-apache

# Installer unzip et git (Composer en a souvent besoin)
RUN apt-get update && apt-get install -y unzip git

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier tout le projet (pas juste public)
COPY . /var/www/

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Lancer composer install (dans /var/www)
WORKDIR /var/www
RUN composer install --no-dev --optimize-autoloader

# Copier le contenu de /public dans le dossier Apache
RUN rm -rf /var/www/html && ln -s /var/www/public /var/www/html

# Activer mod_rewrite
RUN a2enmod rewrite

# Adapter Apache pour Render
RUN sed -i "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Lancer Apache
CMD ["apache2-foreground"]
