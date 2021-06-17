# amplify
Amplify Podcast Network

A digital tool for collecting podcast episodes and describing the metadata for
them.

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
      --target=/home/vagrant/sfa --type=directory
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
