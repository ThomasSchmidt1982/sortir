# Image PHP + Apache
FROM php:8.2-apache

RUN apt-get update && apt-get install -y unzip git curl

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installer Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Copier tout le projet
COPY . /var/www/

RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www

RUN composer install --no-dev --optimize-autoloader

RUN rm -rf /var/www/html && ln -s /var/www/public /var/www/html

RUN a2enmod rewrite

RUN sed -i "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

CMD ["apache2-foreground"]
