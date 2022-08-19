# Amplify Podcast Network

[Amplify][amplify] is a PHP application written using the
[Symfony Framework][symfony]. It is a digital tool for collecting podcast
episodes and describing the metadata for them.

## Requirements

We have tried to keep the requirements minimal. How you install these 
requirements is up to you, but we have [provided some recommendations][setup]

 - Apache >= 2.4
 - PHP >= 7.4
 - Composer >= 2.0
 - MariaDB >= 10.8[^1]
 - Yarn >= 1.22

## Installation

1. Fork and clone the project from [GitHub][github-amplify].
2. Install the git submodules. `git submodule update --init` is a good way to do this
3. Install composer dependencies with `composer install`.
4. Install yarn dependencies with `yarn install`.
4. Create a MariaDB database and user.
    
   ```sql
    DROP DATABASE IF EXISTS amplify;
    CREATE DATABASE amplify;
    DROP USER IF EXISTS amplify@localhost;
    CREATE USER amplify@localhost IDENTIFIED BY 'abc123';
    GRANT ALL ON amplify.* TO amplify@localhost;
    ```
5. Copy .env to .env.local and edit configuration to suite your needs.
6. Either 1) create the schema and load fixture data, or 2) load a MySQLDump file
if one has been provided.
   1. ```bash
        php ./bin/console doctrine:schema:create --quiet
        php ./bin/console doctrine:fixtures:load --group=dev --purger=fk_purger
      ``` 
   2. ```bash
        mysql amplify < amplify.sql
      ``` 

7. Visit http://localhost/amplify
8. happy coding!

Some of the steps above are made easier with the included [MakeFiles](etc/README.md)
which are in a git submodule. If you missed step 2 above they will be missing.

## Export & Import

To export a season with the structure of an Islandora Batch, run
this console command:

```bash
$ ./bin/console app:export:batch <season id> <directory>
```

This will create a directory and one subdirectory for each episode with the 
metadata and datastreams in the correct locations and format. The example below
is for two episodes.

```
ps/S01E01:
-rw-rw-rw-  1 michael  staff   3.4K Jun 17 12:20 MODS.xml
-rw-rw-rw-  1 michael  staff    38M Jun 17 12:20 OBJ.mp3
-rw-rw-rw-  1 michael  staff   7.2M Jun 17 12:20 TN.jpeg
-rw-rw-rw-  1 michael  staff    26K Jun 17 12:20 TRANSCRIPT.txt

ps/S01E02:
-rw-rw-rw-  1 michael  staff   3.7K Jun 17 12:20 MODS.xml
-rw-rw-rw-  1 michael  staff    53M Jun 17 12:20 OBJ.mp3
-rw-rw-rw-  1 michael  staff    90K Jun 17 12:20 TN.jpeg
-rw-rw-rw-  1 michael  staff    34K Jun 17 12:20 TRANSCRIPT.txt
```

Create a collection on the Islandora server and set the content model to
one of the audio formats.

Upload the directory to an Islandora server somewhere, then prepare the batch 
for import:

```bash
$ drush -v --user=admin --uri=http://localhost islandora_book_batch_preprocess \
      --content_models=islandora:sp-audioCModel --parent=audio:sfa \
      --target=/home/vagrant/sfa --type=directory --namespace=sfa
```

> The command above uses the word "book" even though this isn't a book import. The book
> importer understands more complex directory structures, which is why we need it.
> Specifying the content models overrides the bookish nature of the command to
> do the right thing for audio.

Then run the batch import process. This may take a long time, especially if the
batch will be generating derivatives.

```bash
$ drush -v --user=admin --uri=http://localhost islandora_batch_ingest
```

### References

- [How to Batch Ingest Files](https://wiki.lyrasis.org/display/ISLANDORA/How+to+Batch+Ingest+Files)
- [Batch Ingest Module](https://wiki.lyrasis.org/display/ISLANDORA/Islandora+Batch)  
- [How to Batch Ingest with Thumbnails](https://jira.lyrasis.org/browse/ISLANDORA-1157?focusedCommentId=58603&page=com.atlassian.jira.plugin.system.issuetabpanels%3Acomment-tabpanel#comment-58603)
- [Islandora Book Batch](https://wiki.lyrasis.org/display/ISLANDORA7111/Islandora+Book+Batch)
- [Audio Solution Pack](https://wiki.lyrasis.org/display/ISLANDORA/Audio+Solution+Pack)

[amplify]: https://dhil.lib.sfu.ca/amplify
[symfony]: https://symfony.com
[github-amplify]: https://github.com/sfu-dhil/amplify
[composer]: https://getcomposer.org/doc/00-intro.md
[setup]: https://sfu-dhil.github.io/dhil-docs/dev/

[^1]: A similar version of MySQL should also work, but will not be supported.
