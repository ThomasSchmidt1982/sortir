# Image PHP + Apache
FROM php:8.2-apache

# Installer unzip et git (nécessaire pour composer install)
RUN apt-get update && apt-get install -y unzip git

# Installer Composer (copié depuis l'image officielle composer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier tout le projet dans /var/www
COPY . /var/www/

# Installer extensions PHP nécessaires pour MariaDB/MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Se placer dans le dossier du projet
WORKDIR /var/www

# Installer les dépendances PHP via Composer
RUN composer install --no-dev --optimize-autoloader

# Supprimer le dossier html par défaut et pointer vers /var/www/public
RUN rm -rf /var/www/html && ln -s /var/www/public /var/www/html

# Activer mod_rewrite
RUN a2enmod rewrite

# Adapter Apache pour Render
RUN sed -i "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Lancer Apache
CMD ["apache2-foreground"]
