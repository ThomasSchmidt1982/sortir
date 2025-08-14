# Image officielle PHP 8.2 + Apache
FROM php:8.2-apache

# Copier le contenu de /public vers le dossier web d'Apache
COPY public/ /var/www/html/

# Copier aussi le reste du projet (pour autoload, config, etc.)
COPY . /var/www/

# Installer extensions PHP nécessaires pour MariaDB/MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activer mod_rewrite (utile si tu as un .htaccess)
RUN a2enmod rewrite

# Ajuster les permissions (évite les erreurs de droits)
RUN chown -R www-data:www-data /var/www/html

# Apache va écouter sur le port que Render définit via la variable $PORT
# On met à jour la config pour que ça fonctionne sur Render
RUN sed -i "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Commande de démarrage
CMD ["apache2-foreground"]
