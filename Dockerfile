FROM node:20.3 AS amplify-prod-assets
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

FROM dhilsfu/symfony-base:php-8.2-apache AS amplify

ENV GIT_REPO=https://github.com/sfu-dhil/amplify

# basic deps installer (no script/plugings)
COPY --chown=www-data:www-data composer.json composer.lock /var/www/html/
RUN composer install --no-scripts

# copy project files and install all symfony deps
COPY --chown=www-data:www-data . /var/www/html
RUN composer install

# copy webpacked js and libs
COPY --chown=www-data:www-data --from=amplify-prod-assets /app/node_modules /var/www/html/public/node_modules