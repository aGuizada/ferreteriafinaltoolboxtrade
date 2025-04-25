FROM php:7.4-fpm

# 1. Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    gnupg

# 2. Instalar NVM y Node.js 14.21.3 (como root)
ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 14.21.3

RUN mkdir -p "$NVM_DIR" && \
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.5/install.sh | bash && \
    . "$NVM_DIR/nvm.sh" && \
    nvm install $NODE_VERSION && \
    nvm alias default $NODE_VERSION && \
    nvm use default

# 3. Configurar PATH para Node.js (globalmente)
ENV PATH "$NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH"

# 4. Limpiar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 5. Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql zip exif pcntl
RUN docker-php-ext-install gd

# 6. Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 7. Crear usuario del sistema
RUN useradd -G www-data,root -u 1000 -d /home/laravel laravel
RUN mkdir -p /home/laravel/.composer && \
    chown -R laravel:laravel /home/laravel

# 8. Establecer directorio de trabajo
WORKDIR /var/www

# 9. Copiar aplicación
COPY . /var/www
COPY --chown=laravel:laravel . /var/www

# 10. Verificar instalaciones (opcional)
RUN node -v && npm -v && php -v && composer --version

# 11. Cambiar al usuario laravel
USER laravel

# 12. Exponer puerto e iniciar PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]