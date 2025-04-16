---
layout: default
title: Exports
parent: Development Documentation
nav_order: 2
---

# AMP Exports

The AMP tool can generate a zip file for several different Metadata formats for achieving and preserving podcast metadata.

## Islandora (SFU specific)

{: .warning }
> The Islandora export currently contains fields specific to [Simon Fraser University's Summit](https://summit.sfu.ca/). Additional development is required to support a generic Islandora installation or a customized institution's Islandora. Leave questions or requests on the [github repository](https://github.com/sfu-dhil/amplify/issues).

### Islandora Export File Structure

The `zip` file contains a `yaml` [Islandora Workbench](https://mjordan.github.io/islandora_workbench_docs/) [configuration file](https://mjordan.github.io/islandora_workbench_docs/configuration/) named something similar to `amp_podcast_XX_config.yaml`. This file contains basic metadata for the entire podcast and should only be edited by the Summit team.

{: .note }
> The config file is missing authentication details for uploading the metadata into Islandora. This must be added by the Islandora Workbench uploader.

The `zip` file also contains a directory named something similar to `amp_podcast_XX_input_files`. This directory will contain a `csv` file called `metadata.csv` with holds all the metadata for the podcast, season, episodes, and files. The folder also has sub-directories called `transcript`, `image`, `audio` containing files of each respective category.

Example:
```
My Podcast - islandora.zip
│   amp_podcast_1_config.yaml
│
└───amp_podcast_1_input_files
    │   metadata.csv
    │
    └───transcript
    │   │   episode_1_transcript.pdf
    │   │   episode_2_transcript.txt
    │   │   ...
    │
    └───image
    │   │   podcast_image.png
    │   │   episode_1_image.jpg
    │   │   ...
    │
    └───audio
        │   episode_1_audio.mp3
        │   episode_2_audio.mp3
        │   ...
```

## MODS

{: .warning }
> MODS exports are currently untested. Reach out to us on github if you have any issues [github repository](https://github.com/sfu-dhil/amplify/issues).

### MODS Export File Structure

The root directory contains the podcast metadata (`MODS.xml`) and sub-directories for each season. Each season sub-directory contains it's metadata (`MODS.xml`) and sub-directories for each episode. Each episode sub-directory contains it's metadata (`MODS.xml`).

In addition each podcast, season, and episode directory has a sub-directory for each of it's images (and a thumbnail `TN.<EXTENSION>` for the first image if it has any). Episodes also contain subdirectories for each audio and transcript file.

Each image, transcript, and audio sub-directory contains its metadata (`MODS.xml`) and the binary file (`OBJ.<EXTENSION>`).


Example:
```
My Podcast - mods.zip
│   MODS.xml
|   TN.png
│
└───img_0
|   |   MODS.xml
|   │   OBJ.png
|
└───img_1
|   |   ...
|
└───S1
|   |   MODS.xml
|   │   TN.png
|   |
|   └───img_0
|   |   |   MODS.xml
|   |   │   OBJ.png
|   |
|   └───img_1
|   |   |   ...
|   |
|   └───S1E1
|   |   |   MODS.xml
|   |   │   TN.png
|   |   |
|   |   └───img_0
|   |   |   |   MODS.xml
|   |   |   │   OBJ.png
|   |   |
|   |   └───img_1
|   |   |   |   ...
|   |   |
|   |   └───transcript
|   |   |   |   MODS.xml
|   |   |   │   OBJ.pdf
|   |   |
|   |   └───transcript_1
|   |   |   |   ...
|   |   |
|   |   └───audio
|   |   |   |   MODS.xml
|   |   |   │   OBJ.pdf
|   |   |
|   |   └───audio_1
|   |       |   ...
|   |
|   └───S1E2
|       |   ...
|
└───S2
    |   ...
```

## BePress

{: .warning }
> BePress exports are currently untested. Reach out to us on github if you have any issues [github repository](https://github.com/sfu-dhil/amplify/issues). The BePress import process is not smooth and requires several additional steps by the team importing that data for it to work.

### BePress Export File Structure

- `podcast_metadata.csv` contains the metadata for the podcast
- `podcast_files_metadata.csv` contains the metadata for the podcast images. `podcast_files.zip` is the actual podcast image files
- `podcast_seasons_metadata.csv` contains the metadata for all the podcast seasons.
- `<Season Slug>_files_metadata.csv` contains the metadata for the season images. `<Season Slug>_files_metadata.csv` is the actual season image files
- `<Season Slug>_episodes_metadata.csv` contains the metadata for all the episodes in a given season.
- `<Episode Slug>_files_metadata.csv` contains the metadata for the episode images, transcript, and audio. `<Episode Slug>_files_metadata.csv` is the actual episode images, transcript, and audio files

Example:
```
My Podcast - bepress.zip
│   podcast_metadata.csv
|   podcast_files_metadata.csv
|   podcast_files.zip
|   podcast_seasons_metadata.csv
|   S1_files_metadata.csv
|   S1_files.zip
|   S1_episodes_metadata.csv
|   S1E1_files_metadata.csv
|   S1E1_files.zip
|   S1E2_files_metadata.csv
|   S1E2_files.zip
|   ...
|   SX_files_metadata.csv
|   SX_files.zip
|   SX_episodes_metadata.csv
|   SXEY_files_metadata.csv
|   SXEY_files.zip
```