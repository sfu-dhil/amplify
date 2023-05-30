# Amplify Podcast Network

[Amplify][amplify] is a PHP application written using the
[Symfony Framework][symfony]. It is a digital tool for collecting podcast
episodes and describing the metadata for them.

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- A copy of the `amplify-schema.sql` and `amplify-data.sql` database files. If you are not sure what these are or where to get them, you should contact the [Digital Humanities Innovation Lab](mailto:dhil@sfu.ca) for access. These files should be placed in the root folder.
- A copy of the blog images. These should be placed directly into the `.data/app/blog_images/` directory (start the application for the first time if you don't see the directory).
- A copy of the data (audio/image/pdf) files. These should be placed directly into the `.data/data/audio`,  `.data/data/image`,  `.data/data/pdf` directory (start the application for the first time if you don't see the data directory).

## Initialize the Application

First you must setup the database for the first time

    docker compose up -d db
    # wait 30 after the command has fully completed
    docker exec -it amplify_db bash -c "mysql -u amplify -ppassword amplify < /amplify-schema.sql"
    docker exec -it amplify_db bash -c "mysql -u amplify -ppassword amplify < /amplify-data.sql"

Next you must start the whole application

    docker compose up -d --build

Amplify will now be available at `http://localhost:8080/`

### Create your admin user credentials

    docker exec -it amplify_app ./bin/console nines:user:create <your@email.address> '<your full name>' '<affiliation>'
    docker exec -it amplify_app ./bin/console nines:user:password <your@email.address> <password>
    docker exec -it amplify_app ./bin/console nines:user:promote <your@email.address> ROLE_ADMIN
    docker exec -it amplify_app ./bin/console nines:user:activate <your@email.address>

example:

    docker exec -it amplify_app ./bin/console nines:user:create test@test.com 'Test User' 'DHIL'
    docker exec -it amplify_app ./bin/console nines:user:password test@test.com test_password
    docker exec -it amplify_app ./bin/console nines:user:promote test@test.com ROLE_ADMIN
    docker exec -it amplify_app ./bin/console nines:user:activate test@test.com

## General Usage

### Starting the Application

    docker compose up -d

### Stopping the Application

    docker compose down

### Rebuilding the Application (after upstream or js/php package changes)

    docker compose up -d --build

### Viewing logs (each container)

    docker logs -f amplify_app
    docker logs -f amplify_db
    docker logs -f amplify_mail

### Accessing the Application

    http://localhost:8080/

### Accessing the Database

Command line:

    docker exec -it amplify_db mysql -u amplify -ppassword amplify

Through a database management tool:
- Host:`127.0.0.1`
- Port: `13306`
- Username: `amplify`
- Password: `password`

### Accessing Mailhog (catches emails sent by the app)

    http://localhost:8025/

### Database Migrations

Migrate up to latest

    docker exec -it amplify_app make migrate

## Updating Application Dependencies

### Yarn (javascript)

First setup an image to build the yarn deps in

    docker build -t amplify_yarn_helper --target amplify-prod-assets .

Then run the following as needed

    # add new package
    docker run -it --rm -v $(pwd)/public:/app amplify_yarn_helper yarn add [package]

    # update a package
    docker run -it --rm -v $(pwd)/public:/app amplify_yarn_helper yarn upgrade [package]

    # update all packages
    docker run -it --rm -v $(pwd)/public:/app amplify_yarn_helper yarn upgrade

Note: If you are having problems starting/building the application due to javascript dependencies issues you can also run a standalone node container to help resolve them

    docker run -it --rm -v $(pwd)/public:/app -w /app node:19.5 bash

    [check Dockerfile for the 'apt-get update' code piece of amplify-prod-assets]

    yarn ...

After you update a dependency make sure to rebuild the images

    docker compose down
    docker compose up -d

### Composer (php)

    # add new package
    docker exec -it amplify_app composer require [vendor/package]

    # add new dev package
    docker exec -it amplify_app composer require --dev [vendor/package]

    # update a package
    docker exec -it amplify_app composer update [vendor/package]

    # update all packages
    docker exec -it amplify_app composer update

Note: If you are having problems starting/building the application due to php dependencies issues you can also run a standalone php container to help resolve them

    docker run -it -v $(pwd):/var/www/html -w /var/www/html php:7.4-apache bash

    [check Dockerfile for the 'apt-get update' code piece of amplify]

    composer ...

After you update a dependency make sure to rebuild the images

    docker compose down
    docker compose up -d

## Tests

First make sure the application and database are started with `docker compose up -d`

### Unit Tests

    docker exec -it amplify_app make test

### Generate Code Coverage

    docker exec -it amplify_app make test.cover
    make test.cover.view

If the coverage file doesn't open automatically you can manually open it `coverage/index.html`

## Misc

### PHP Code standards

See standards errors

    docker exec -it amplify_app make lint-all
    docker exec -it amplify_app make symlint

    # or
    docker exec -it amplify_app make stan
    docker exec -it amplify_app make twiglint
    docker exec -it amplify_app make twigcs
    docker exec -it amplify_app make yamllint
    docker exec -it amplify_app make symlint


Automatically fix some standards errors

    docker exec -it amplify_app make fix.all

### Debug helpers

    docker exec -it amplify_app make dump.autowire
    docker exec -it amplify_app make dump.container
    docker exec -it amplify_app make dump.env
    docker exec -it amplify_app make dump.params
    docker exec -it amplify_app make dump.router
    docker exec -it amplify_app make dump.twig


## Manual Imports

    docker exec -it amplify_app ./bin/console app:import:podcast <url> <podcastId> <importId>

`url`: (Required) The rss url for the podcast.

`podcastId` (Optional) The id of an existing podcast. If no podcast id is provided, then the import process will create a new podcast.

`importId` (Optional) The id of an existing import. This is mainly used by the application to relay import status to end users. It is only optionally required when manually running the import.

Examples

    docker exec -it amplify_app ./bin/console app:import:podcast http://feeds.feedburner.com/SecretFeministAgenda

    docker exec -it amplify_app ./bin/console app:import:podcast http://feeds.feedburner.com/SecretFeministAgenda 1

    docker exec -it amplify_app ./bin/console app:import:podcast http://feeds.feedburner.com/SecretFeministAgenda '' 1

    docker exec -it amplify_app ./bin/console app:import:podcast http://feeds.feedburner.com/SecretFeministAgenda 1 1

## Manual Exports

    docker exec -it amplify_app ./bin/console app:export:podcast <podcastId> <format> <exportId>

`podcastId` (Required) The id of the podcast to export.

`format` (Required) The format to export the podcast into (One of `islandora`, `mods`, or `bepress`).

`exportId` (Optional) The id of the export. If no export id is provided, then it will be generated by the export process.

Note: this will generate a zip file of the export within the project located a `data/<env>/exports/<podcastId>/<exportId>.zip`

Examples

    docker exec -it amplify_app ./bin/console app:export:podcast 1 islandora

    docker exec -it amplify_app ./bin/console app:export:podcast 1 islandora 1

# Using Islandora Exports

Islandora exports can be imported via the [Islandora Workbench](https://github.com/mjordan/islandora_workbench) for Islandora 2.0 or higher.

    1. [Install Islandora Workbench](https://mjordan.github.io/islandora_workbench_docs/installation/) on your machine
        - `git clone https://github.com/mjordan/islandora_workbench.git`
        - `cd islandora_workbench`
        - `python3 setup.py install --user`
    1. Place the zip file in the ...
    1. Setup the config file yml file (an example is provided in the zip)
    1. run the command ...
