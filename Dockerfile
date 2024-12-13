# Usa a imagem oficial do PHP com suporte ao Laravel
FROM php:8.3-fpm

# Instala dependências necessárias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo_pgsql zip

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instala o Redis
RUN pecl install redis && docker-php-ext-enable redis

# Copia os arquivos do projeto para dentro do container
COPY . /var/www/html

# Define permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Define o comando padrão
CMD php artisan serve --host=0.0.0.0 --port=8000
