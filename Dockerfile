FROM php:8.2-apache

# Habilitar módulos útiles de Apache
RUN a2enmod rewrite headers

# Extensiones PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todo el repo dentro de /var/www/html/backEndYUPAY
COPY . /var/www/html/backEndYUPAY/

# Hacer que Apache sirva desde /var/www/html/backEndYUPAY (DocumentRoot)
RUN sed -ri -e 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/backEndYUPAY#' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's#<Directory /var/www/>#<Directory /var/www/html/backEndYUPAY/>#' /etc/apache2/apache2.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# curl para el HEALTHCHECK
RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*

# Salud básica
HEALTHCHECK --interval=30s --timeout=3s CMD curl -fsS http://localhost/ || exit 1
