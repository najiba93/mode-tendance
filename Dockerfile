# Utilise une image PHP avec Apache
FROM php:8.2-apache

# Installe les extensions nécessaires
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev zlib1g-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_pgsql zip

# Copie le code source
COPY . /var/www/html

# Va dans le dossier du projet
WORKDIR /var/www/html
RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Installe Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Installe les dépendances Symfony
RUN composer install --no-dev --optimize-autoloader --no-scripts


# Active le serveur Apache pour Symfony
EXPOSE 80
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
