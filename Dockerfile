FROM node:19.8 AS amplify-prod-assets
WORKDIR /app

RUN apt-get update \
    && apt-get install -y git \
    && npm upgrade -g npm \
    && npm upgrade -g yarn \
    && rm -rf /var/lib/apt/lists/*

# build js deps
COPY public/package.json public/yarn.lock /app/

RUN yarn --production \
    && yarn cache clean


FROM php:8.2-apache AS amplify
WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libxslt1-dev \
        git \
        libmagickwand-dev \
        libzip-dev \
        zip  \
        unzip \
        ghostscript \
        libicu-dev \
        libapache2-mod-xsendfile \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && a2enmod rewrite headers \
    && docker-php-ext-configure intl \
    && docker-php-ext-install xsl pdo pdo_mysql zip intl \
    && pecl install imagick pcov \
    && docker-php-ext-enable imagick pcov \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# default service settings
COPY docker/app/docker-entrypoint.sh /docker-entrypoint.sh
COPY docker/app/apache.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/app/php.ini /usr/local/etc/php/conf.d/amplify.ini
COPY docker/app/image-policy.xml /etc/ImageMagick-6/policy.xml

# basic deps installer (no script/plugings)
COPY composer.json composer.lock /var/www/html/
RUN composer install --no-scripts

# copy project files and install all symfony deps
COPY . /var/www/html
RUN composer install

# copy webpacked js and libs
COPY --from=amplify-prod-assets /app/node_modules /var/www/html/public/node_modules

CMD ["/docker-entrypoint.sh"]