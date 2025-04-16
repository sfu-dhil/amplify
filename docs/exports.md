---
layout: default
title: Exports
nav_order: 4
---

# AMP Exports

{: .note }
> See the [How do I Export a podcast](../how_do_I/podcast_export.html) page for steps to take to generate the AMP podcast exports.

The AMP tool can generate a zip file for several different Metadata formats for achieving and preserving podcast metadata.

## Islandora (SFU specific)

{: .warning }
> The Islandora export currently contains fields specific to [Simon Fraser University's Summit](https://summit.sfu.ca/). Additional development is required to support a generic Islandora installation or a customized institution's Islandora. Leave questions or requests on the [github repository](https://github.com/sfu-dhil/amplify/issues).

Please contact the DHIL team at [dhil@sfu.ca](mailto:dhil@sfu.ca) when your podcast's metadata is ready. We will start the process to get it deposited into [Summit](https://summit.sfu.ca/). Please be aware that the process may take some time as the metadata needs to be double checked by before being deposited.

## MODS

{: .warning }
> MODS exports are currently untested. Reach out to us on github if you have any issues [github repository](https://github.com/sfu-dhil/amplify/issues).

The [MODS](http://www.loc.gov/standards/mods/) (Metadata Object Description Schema) schema is a more generic metadata solution that should work in a variety of digital preservation tools. It would be best to check with the team managing your institution's digital preservation tool to see if they support importing MODS files and how to best provide them with the data.

## BePress

{: .important }
Steps to import in BePress are still a work in progress. It requires [Batch uploading](https://digitalcommons.elsevier.com/managing-submissions-publishing/batch-upload-export-and-revise) the files and then editing the excel with the uploaded file urls (ex: upload `podcast_files.zip`, then edit `podcast_files_metadata.csv`, then batch upload `podcast_files_metadata.csv`).

{: .warning }
> BePress exports are currently untested. Reach out to us on github if you have any issues [github repository](https://github.com/sfu-dhil/amplify/issues). The BePress import process is not smooth and requires several additional steps by the team importing that data for it to work.
