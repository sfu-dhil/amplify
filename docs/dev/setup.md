---
layout: default
title: Setup
parent: Development Documentation
nav_order: 1
---

# Source Code & Usage Docs

The AMP source code can be found on github [https://github.com/sfu-dhil/amplify](https://github.com/sfu-dhil/amplify).



# Docker

AMP requires two running containers, one for the main web application and a second for handling delayed jobs (Podcast RSS import and Podcast export).

The `/var/www/html/data/prod` directory should be mounted outside the container for file persistence.

The `/var/www/html/var/log` directory should optionally be mounted outside the container for log persistence.

## Docker Swarm Example

The following is a minimal docker compose file example for production use using docker swarm mode with the assumption that ssl termination is handled somewhere upstream. This includes an additional mariadb container specifically for the AMP but you can easily use a database shared by multiple applications.

```yaml
services:
  db:
    image: mariadb:10.11
    volumes:
      # persistence
      - /docker-swarm-persistence/mariadb:/var/lib/mysql
    environment:
      MARIADB_ROOT_PASSWORD_FILE: /run/secrets/db_root_password
    secrets:
      - db_root_password
    configs:
      - source: custom_cnf
        target: /etc/mysql/conf.d/custom.cnf
    deploy:
      replicas: 1
      update_config:
        order: stop-first
        delay: 10s
      restart_policy:
        condition: on-failure
  app: &app
    image: ghcr.io/sfu-dhil/amplify:vX.X.X
    ports:
      - "80:80"
    volumes:
      # persistence
      - /docker-swarm-persistence/amplify-data:/var/www/html/data/prod
      - /docker-swarm-persistence/amplify-logs:/var/www/html/var/log
    configs:
      - source: env_file
        target: /var/www/html/.env
      - source: apache_conf
        target: /etc/apache2/sites-enabled/000-default.conf
        # www-data
        uid: '33'
        # www-data
        gid: '33'
        mode: 775
    deploy:
      replicas: 1
      update_config:
        order: start-first
        delay: 10s
  worker:
    <<: *app
    command: php bin/console messenger:consume async scheduled -v
    ports: []
    deploy:
      replicas: 2
      update_config:
        order: start-first
        delay: 1m30s
secrets:
  db_root_password:
    file: /docker-swarm-secrets/amplify/db_root_password
configs:
  custom_cnf:
    file: /docker-swarm-config/amplify/custom.cnf
  env_file:
    file: /docker-swarm-config/amplify/.env
  apache_conf:
    file: /docker-swarm-config/amplify/apache.conf
```

`/docker-swarm-secrets/amplify/db_root_password` should contain the mariadb root password secret

`/docker-swarm-config/amplify/custom.cnf` should contain mariadb config similar to:

```config
[mysqld]
lower_case_table_names = 2
sql_mode = "STRICT_ALL_TABLES,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_AUTO_VALUE_ON_ZERO,NO_ENGINE_SUBSTITUTION"
character-set-server = utf8
collation-server = utf8_unicode_ci
skip-character-set-client-handshake
```

`/docker-swarm-config/amplify/.env` should contain amp production environment variables:

```config
APP_SECRET=<SOME SECRET STRING>
APP_ENV=prod
DATABASE_URL=mysql://amplify:<MARIA_DB_AMPLIFY_PASSWORD>@db:3306/amplify?serverVersion=mariadb-10.11.0
ROUTE_PROTOCOL=https
ROUTE_HOST=<HOSTNAME ex: amp.dhil.lib.sfu.ca>
ROUTE_BASE=
MATOMO_ENABLED=false
TRUSTED_PROXIES=127.0.0.1,REMOTE_ADDR
```

`/docker-swarm-config/amplify/apache.conf` should contain production level apache configuration

```apache
ServerName <HOSTNAME ex: amp.dhil.lib.sfu.ca>
<VirtualHost *:80>
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride None
        Order Allow,Deny
        Allow from All

        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php [QSA,L]

            RequestHeader set X-Forwarded-Proto "https"
            RequestHeader set X-Forwarded-Port 443
        </IfModule>

        <IfModule mod_xsendfile.c>
            XSendFile on
            XSendFilePath /var/www/html/data

            <IfModule mod_headers.c>
                RequestHeader set X-Sendfile-Type X-Sendfile
            </IfModule>
        </IfModule>
    </Directory>

    SetEnvIf Request_URI "^/health.php$" dontlog
    CustomLog ${APACHE_LOG_DIR}/access.log combined env=!dontlog
</VirtualHost>
```

## Additional Considerations

- Don't forget to create an application specific MariaDB user and database (example username `amplify` with admin permissions on `amplify` database)
- Docker swarm isn't good for multiple applications using the same ports but [Traefik](https://traefik.io/traefik/) is an excellent proxy with docker swarm support to get around this.


# Standalone PHP Server

See the Dockerfile for both the [docker-symfony-base](https://github.com/sfu-dhil/docker-symfony-base/blob/main/Dockerfile) and [amplify](https://github.com/sfu-dhil/amplify/blob/main/Dockerfile) images for an idea of the application dependencies and setup instructions.

In addition to `php`, `composer`, `apache` and `mariadb`, you will need:
- `node` for javascript deps
- `ruby` for building documentation
- `ImageMagick` for image processing
- `git` for generating source code versioning for UI
- `zip` for exports