<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Podcast;
use App\Entity\Season;

class ModsExport extends ExportService {
    protected function processPodcast(Podcast $podcast, string $podcastDir) : void {
        // /MODS.xml
        $this->filesystem->dumpFile("{$podcastDir}/MODS.xml", $this->twig->render('export/format/mods/podcast.xml.twig', [
            'podcast' => $podcast,
            'contributions' => $this->getPodcastContributorPersonAndRoles($podcast),
        ]));

        // /TN.<ext> /img_<n>/MODS.xml /img_<n>/OBJ.<ext>
        foreach ($podcast->getImages() as $index => $image) {
            if ( ! $image?->getFile()) {
                continue;
            }

            $podcastImageDir = "{$podcastDir}/img_{$index}";
            $this->filesystem->mkdir($podcastImageDir, 0o777);
            $this->filesystem->dumpFile("{$podcastImageDir}/MODS.xml", $this->twig->render('export/format/mods/podcast_image.xml.twig', [
                'podcast' => $podcast,
                'image' => $image,
                'contributions' => $this->getPodcastContributorPersonAndRoles($podcast),
            ]));
            $this->filesystem->copy($image->getFile()->getRealPath(), "{$podcastImageDir}/OBJ.{$image->getExtension()}");
            if (0 === $index) {
                $this->filesystem->copy($image->getFile()->getRealPath(), "{$podcastDir}/TN.{$image->getExtension()}");
            }
        }
    }

    protected function processSeason(Season $season, string $seasonDir) : void {
        // /MODS.xml
        $this->filesystem->dumpFile("{$seasonDir}/MODS.xml", $this->twig->render('export/format/mods/season.xml.twig', [
            'season' => $season,
            'contributions' => $this->getSeasonContributorPersonAndRoles($season),
        ]));

        // /TN.<ext> /img_<n>/MODS.xml /img_<n>/OBJ.<ext>
        foreach ($season->getImages() as $index => $image) {
            if ( ! $image?->getFile()) {
                continue;
            }

            $seasonImageDir = "{$seasonDir}/img_{$index}";
            $this->filesystem->mkdir($seasonImageDir, 0o777);
            $this->filesystem->dumpFile("{$seasonImageDir}/MODS.xml", $this->twig->render('export/format/mods/season_image.xml.twig', [
                'season' => $season,
                'image' => $image,
                'contributions' => $this->getSeasonContributorPersonAndRoles($season),
            ]));
            $this->filesystem->copy($image->getFile()->getRealPath(), "{$seasonImageDir}/OBJ.{$image->getExtension()}");
            if (0 === $index) {
                $this->filesystem->copy($image->getFile()->getRealPath(), "{$seasonDir}/TN.{$image->getExtension()}");
            }
        }
    }

    protected function processEpisode(Episode $episode, string $episodeDir) : void {
        // /MODS.xml
        $this->filesystem->dumpFile("{$episodeDir}/MODS.xml", $this->twig->render('export/format/mods/episode.xml.twig', [
            'episode' => $episode,
            'contributions' => $this->getEpisodeContributorPersonAndRoles($episode),
        ]));
        if ($episode->getTranscript()) {
            $text = $this->exportContentSanitizer->sanitize($episode->getTranscript() ?? '');
            $this->filesystem->dumpFile("{$episodeDir}/FULL_TEXT.txt", wordwrap($text));
        }

        // /audio/MODS.xml /audio/OBJ.<ext>
        foreach ($episode->getAudios() as $index => $audio) {
            if ( ! $audio?->getFile()) {
                continue;
            }

            $episodeAudioDir = 0 === $index ? "{$episodeDir}/audio" : "{$episodeDir}/audio_{$index}";
            $this->filesystem->mkdir($episodeAudioDir, 0o777);
            $this->filesystem->dumpFile("{$episodeAudioDir}/MODS.xml", $this->twig->render('export/format/mods/episode_audio.xml.twig', [
                'episode' => $episode,
                'audio' => $audio,
                'contributions' => $this->getEpisodeContributorPersonAndRoles($episode),
            ]));
            $this->filesystem->copy($audio->getFile()->getRealPath(), "{$episodeAudioDir}/OBJ.{$audio->getExtension()}");
        }

        // /transcript/MODS.xml /transcript/OBJ.pdf /transcript/FULL_TEXT.txt (if episode transcript available)
        // /transcript_<n>/MODS.xml /transcript_<n>/OBJ.pdf for pdfs after the first
        foreach ($episode->getPdfs() as $index => $pdf) {
            if ( ! $pdf?->getFile()) {
                continue;
            }

            $episodeTranscriptDir = 0 === $index ? "{$episodeDir}/transcript" : "{$episodeDir}/transcript_{$index}";
            $this->filesystem->mkdir($episodeTranscriptDir, 0o777);
            $this->filesystem->dumpFile("{$episodeTranscriptDir}/MODS.xml", $this->twig->render('export/format/mods/episode_transcript.xml.twig', [
                'episode' => $episode,
                'pdf' => $pdf,
                'contributions' => $this->getEpisodeContributorPersonAndRoles($episode),
            ]));
            $this->filesystem->copy($pdf->getFile()->getRealPath(), "{$episodeTranscriptDir}/OBJ.pdf");
            if (0 === $index && $episode->getTranscript()) {
                $text = $this->exportContentSanitizer->sanitize($episode->getTranscript() ?? '');
                $this->filesystem->dumpFile("{$episodeTranscriptDir}/FULL_TEXT.txt", wordwrap($text));
            }
        }

        // /TN.<ext> /img_<n>/MODS.xml /img_<n>/OBJ.<ext>
        foreach ($episode->getImages() as $index => $image) {
            if ( ! $image?->getFile()) {
                continue;
            }

            $episodeImageDir = "{$episodeDir}/img_{$index}";
            $this->filesystem->mkdir($episodeImageDir, 0o777);
            $this->filesystem->dumpFile("{$episodeImageDir}/MODS.xml", $this->twig->render('export/format/mods/episode_image.xml.twig', [
                'episode' => $episode,
                'image' => $image,
                'contributions' => $this->getEpisodeContributorPersonAndRoles($episode),
            ]));
            $this->filesystem->copy($image->getFile()->getRealPath(), "{$episodeImageDir}/OBJ.{$image->getExtension()}");
            if (0 === $index) {
                $this->filesystem->copy($image->getFile()->getRealPath(), "{$episodeDir}/TN.{$image->getExtension()}");
            }
        }
    }

    protected function generate() : void {
        $this->updateMessage('Starting Mods export.');
        $currentEpisode = 0;

        $this->processPodcast($this->podcast, $this->exportTmpRootDir);

        foreach ($this->podcast->getSeasons() as $season) {
            $seasonDir = "{$this->exportTmpRootDir}/{$season->getSlug()}";
            $this->filesystem->mkdir($seasonDir, 0o777);

            $this->processSeason($season, $seasonDir);

            foreach ($season->getEpisodes() as $episode) {
                $currentEpisode++;
                $this->updateMessage("Generating metadata for {$episode->getSlug()} ({$currentEpisode}/{$this->totalEpisodes})");

                $episodeDir = "{$seasonDir}/{$episode->getSlug()}";
                $this->filesystem->mkdir($episodeDir, 0o777);

                $this->processEpisode($episode, $episodeDir);

                $this->updateProgress(++$this->stepsCompleted);
            }
        }
    }
}
