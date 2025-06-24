FROM php:8.3-apache

# Installer les dépendances système et les extensions PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer un utilisateur non-root
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN groupadd -g ${GROUP_ID} appuser && \
    useradd -u ${USER_ID} -g appuser -m -s /bin/bash appuser

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . /var/www/html

# Créer un fichier .htaccess pour Symfony
RUN echo "<IfModule mod_rewrite.c>\n\
    RewriteEngine On\n\
    RewriteBase /public\n\
    RewriteCond %{REQUEST_FILENAME} !-f\n\
    RewriteCond %{REQUEST_FILENAME} !-d\n\
    RewriteRule ^(.*)$ index.php [QSA,L]\n\
</IfModule>" > /var/www/html/public/.htaccess

# Créer les répertoires de cache et de log si nécessaire et définir les permissions
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log && \
    chown -R appuser:appuser /var/www/html && \
    chmod -R 775 /var/www/html/var/cache /var/www/html/var/log

# Passer à l'utilisateur non-root
USER appuser

# Autoriser Composer à exécuter en tant que superutilisateur (solution temporaire)
ENV COMPOSER_ALLOW_SUPERUSER=1

# Installer les dépendances Symfony
RUN composer install --no-dev --optimize-autoloader

# Repasser à root pour Apache
USER root

# Configurer Apache pour utiliser public/ comme racine
RUN echo "<VirtualHost *:80>\n\
    ServerName mode-tendance.onrender.com\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog \${APACHE_LOG_DIR}/error.log\n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Activer le module de réécriture pour Symfony
RUN a2enmod rewrite

# Exposer le port 80
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]