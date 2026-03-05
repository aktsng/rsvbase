FROM php:8.4-fpm

# タイムゾーン設定
ENV TZ=Asia/Tokyo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# システム依存パッケージ
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libmariadb-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP設定
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Composerインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node.js 20 LTS インストール
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 作業ディレクトリ
WORKDIR /var/www/html

# 非rootユーザー
RUN groupadd -g 1000 appuser && useradd -u 1000 -g appuser -m appuser
RUN chown -R appuser:appuser /var/www/html

USER appuser

EXPOSE 9000
CMD ["php-fpm"]
