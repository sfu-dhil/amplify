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


## Manual Export & Import

To export a season with the structure of an Islandora Batch, run
this console command:

    docker exec -it amplify_app ./bin/console app:export:batch <season id> <directory>

Example

    docker exec -it amplify_app bash -c './bin/console app:export:batch 1 $(pwd)/data/exports/1/'

This will create a directory and one subdirectory for each episode with the metadata and data streams in the correct locations and format. The example below is for one episode.

    data/exports/1/S01E01:
    -rw-r--r--  1 root root 4.1K Feb 15 17:54 MODS.xml
    -rw-r--r--  1 root root 7.2M Feb 15 17:54 TN.jpeg
    drwxr-xr-x  4 root root  128 Feb 15 17:54 audio
    drwxr-xr-x  4 root root  128 Feb 15 17:54 img_0
    -rw-r--r--  1 root root  218 Feb 15 17:54 structure.xml
    drwxr-xr-x  5 root root  160 Feb 15 17:54 transcript

    data/exports/1/S01E01/audio:
    -rw-r--r-- 1 root root 4.0K Feb 15 17:54 MODS.xml
    -rw-r--r-- 1 root root  38M Feb 15 17:54 OBJ.mp3

    data/exports/1/S01E01/img_0:
    -rw-r--r-- 1 root root  483 Feb 15 17:54 MODS.xml
    -rw-r--r-- 1 root root 7.2M Feb 15 17:54 OBJ.jpeg

    data/exports/1/S01E01/transcript:
    -rw-rw-rw- 1 root root  26K Feb 15 17:54 FULL_TEXT.txt
    -rw-r--r-- 1 root root  486 Feb 15 17:54 MODS.xml
    -rw-r--r-- 1 root root 157K Feb 15 17:54 OBJ.pdf

> The above example would be found in `.data/app/data/exports/1/` in your local file system

Create a collection on the Islandora server and set the content model to
one of the audio formats.

Upload the directory to an Islandora server somewhere, then prepare the batch
for import:

    drush -v --user=admin --uri=http://localhost islandora_book_batch_preprocess \
      --content_models=islandora:sp-audioCModel --parent=audio:sfa \
      --target=/home/vagrant/sfa --type=directory --namespace=sfa

> The command above uses the word "book" even though this isn't a book import. The book
> importer understands more complex directory structures, which is why we need it.
> Specifying the content models overrides the bookish nature of the command to
> do the right thing for audio.

Then run the batch import process. This may take a long time, especially if the
batch will be generating derivatives.

    drush -v --user=admin --uri=http://localhost islandora_batch_ingest

### References

- [How to Batch Ingest Files](https://wiki.lyrasis.org/display/ISLANDORA/How+to+Batch+Ingest+Files)
- [Batch Ingest Module](https://wiki.lyrasis.org/display/ISLANDORA/Islandora+Batch)
- [How to Batch Ingest with Thumbnails](https://jira.lyrasis.org/browse/ISLANDORA-1157?focusedCommentId=58603&page=com.atlassian.jira.plugin.system.issuetabpanels%3Acomment-tabpanel#comment-58603)
- [Islandora Book Batch](https://wiki.lyrasis.org/display/ISLANDORA7111/Islandora+Book+Batch)
- [Audio Solution Pack](https://wiki.lyrasis.org/display/ISLANDORA/Audio+Solution+Pack)