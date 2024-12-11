<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\ContributorRole;
use App\Entity\Episode;
use App\Entity\Podcast;
use App\Entity\Season;
use League\Csv\Writer;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Pdf;

class IslandoraExport extends ExportService {
    private function getFirstAudio(Episode $episode) : ?Audio {
        foreach ($episode->getAudios() as $audio) {
            if (null !== $audio?->getFile()) {
                return $audio;
            }
        }

        return null;
    }

    private function getFormattedKeywords(array $keywords) : array {
        return array_map('ucfirst', array_map('strtolower', $keywords));
    }

    protected function safeFileNameFilter(string $str) : string {
        // Remove any whitespaces (replace with underscores)
        return mb_ereg_replace('\s+', '_', parent::safeFileNameFilter($str));
    }

    private function getSafeFileName(string $fullFilename, string $extension, array &$filenames, bool $updateArray = true) : string {
        $filename = $this->safeFileNameFilter(pathinfo($fullFilename, PATHINFO_FILENAME));
        if (array_key_exists("{$filename}.{$extension}", $filenames)) {
            $count = $filenames["{$filename}.{$extension}"] + 1;
            if ($updateArray) {
                $filenames["{$filename}.{$extension}"] = $count;
            }
            $filename = "{$filename} ({$count})";
        } else if ($updateArray) {
            $filenames["{$filename}.{$extension}"] = 1;
        }

        return "{$filename}.{$extension}";
    }

    private function getPodcastWebsites(Podcast $podcast) : string {
        $websites = [];

        if ($podcast->getWebsite()) {
            $websites[] = "{$podcast->getWebsite()}%%{$podcast->getTitle()}";
        }
        if ($podcast->getRss()) {
            $websites[] = $podcast->getRss();
        }
        $publisher = $podcast->getPublisher();
        if ($publisher?->getWebsite() && $publisher?->getName()) {
            $websites[] = "{$publisher->getWebsite()}%%{$publisher->getName()}";
        }

        return implode('|', $websites);
    }

    private function getSeasonWebsites(Season $season) : string {
        $websites = [];

        $publisher = $season->getPublisher();
        if ($publisher?->getWebsite() && $publisher?->getName()) {
            $websites[] = "{$publisher->getWebsite()}%%{$publisher->getName()}";
        }

        return implode('|', $websites);
    }

    private function addRecordDefaults(array $record) : array {
        $result = [];
        foreach ($this->getCsvMap() as $column => $default) {
            $result[$column] = array_key_exists($column, $record) ? html_entity_decode($this->exportContentSanitizer->sanitize($record[$column] ?? '')) : $default;
            if (in_array($column, ['field_description', 'field_sfu_permissions'], true)) {
                $result[$column] = $this->stripTags($result[$column]);
            }
        }

        return $result;
    }

    private function fixIslandoraRoles(array $roles) : array {
        $islandoraRoles = $this->parameterBagInterface->get('islandora_roles');
        $defaultRole = ContributorRole::tryFrom($this->parameterBagInterface->get('islandora_default_role'));

        if (is_array($islandoraRoles) && count($islandoraRoles) > 0 && $defaultRole) {
            $fixedRoles = [];
            foreach ($roles as $role) {
                $roleToAdd = in_array($role->value, $islandoraRoles, true) ? $role : $defaultRole;
                $fixedRoles[$roleToAdd->value] = $roleToAdd;
            }

            return array_values($fixedRoles);
        }

        return $roles;
    }

    private function getLinkedAgents(array $contributions) : string {
        $linked_agents = [];
        foreach ($contributions as $contribution) {
            $person = $contribution['person'];
            foreach ($this->fixIslandoraRoles($contribution['roles']) as $role) {
                $linked_agents[] = "relators:{$role->value}:person:{$person->getSortableName()}";
            }
        }

        return implode('|', $linked_agents);
    }

    private function getPodcastPublisherText(Podcast $podcast) : ?string {
        if ($podcast->getPublisher()) {
            return $podcast->getPublisher()->getName();
        }

        return null;
    }

    private function getSeasonPublisherText(Season $season) : ?string {
        if ($season->getPublisher()) {
            return $season->getPublisher()->getName();
        }
        if ($season->getPodcast()->getPublisher()) {
            return $season->getPodcast()->getPublisher()->getName();
        }

        return null;
    }

    private function generatePodcastRecord(Podcast $podcast) : array {
        $extent = ['1 podcast'];
        if (count($podcast->getSeasons()) > 0) {
            $extent[] = count($podcast->getSeasons()) . ' season(s)';
        }
        if (count($podcast->getEpisodes()) > 0) {
            $extent[] = count($podcast->getEpisodes()) . ' episode(s)';
        }
        if (count($podcast->getImages()) > 0) {
            $extent[] = count($podcast->getImages()) . ' podcast image file(s)';
        }

        return $this->addRecordDefaults([
            'id' => "amp:podcast:{$podcast->getId()}",
            'field_model' => 'Collection',
            'field_resource_type' => 'Audio',
            'title' => $podcast->getTitle(),
            'field_alternative_title' => $podcast->getSubTitle(),
            'field_identifier' => $podcast->getGuid(),

            'field_description' => $podcast->getDescription(),
            'field_external_links' => $this->getPodcastWebsites($podcast),
            'field_physical_form' => 'Electronic|Sound recordings',
            'field_extent' => implode('|', $extent),
            'field_tags' => implode('|', $this->getFormattedKeywords(array_filter($podcast->getKeywords()))),
            'field_table_of_contents' => $this->twig->render('export/format/islandora/podcast_toc.html.twig', [
                'podcast' => $podcast,
            ]),
            'field_linked_agent' => $this->getLinkedAgents($this->getPodcastContributorPersonAndRoles($podcast)),
            'field_sfu_bibcite_title' => $podcast->getTitle(),
            'field_sfu_bibcite_publishe' => $this->getPodcastPublisherText($podcast),
        ]);
    }

    private function generateSeasonRecord(Season $season, int $weight) : array {
        $extent = ['1 podcast season'];
        if (count($season->getEpisodes()) > 0) {
            $extent[] = count($season->getEpisodes()) . ' episode(s)';
        }
        if (count($season->getImages()) > 0) {
            $extent[] = count($season->getImages()) . ' season image file(s)';
        }

        return $this->addRecordDefaults([
            'id' => "amp:season:{$season->getId()}",
            'parent_id' => "amp:podcast:{$season->getPodcast()->getId()}",
            'field_weight' => "{$weight}",
            'field_model' => 'Collection',
            'field_resource_type' => 'Audio',
            'title' => $season->getTitle(),
            'field_alternative_title' => $season->getSubTitle(),

            'field_description' => $season->getDescription(),
            'field_external_links' => $this->getSeasonWebsites($season),
            'field_physical_form' => 'Electronic|Sound recordings',
            'field_extent' => implode('|', $extent),
            'field_tags' => implode('|', $this->getFormattedKeywords(array_filter($season->getPodcast()->getKeywords()))),
            'field_table_of_contents' => $this->twig->render('export/format/islandora/season_toc.html.twig', [
                'season' => $season,
            ]),
            'field_linked_agent' => $this->getLinkedAgents($this->getSeasonContributorPersonAndRoles($season)),
            'field_sfu_bibcite_title' => $season->getTitle(),
            'field_sfu_bibcite_publishe' => $this->getSeasonPublisherText($season),
        ]);
    }

    private function generateEpisodeRecord(Episode $episode, int $weight, ?Audio $audio, ?string $relativeFile) : array {
        $extent = ['1 podcast episode'];
        if (count($episode->getAudios()) > 0) {
            $extent[] = count($episode->getAudios()) . ' audio file(s)';
        }
        if (count($episode->getImages()) > 0) {
            $extent[] = count($episode->getImages()) . ' image file(s)';
        }
        if (count($episode->getPdfs()) > 0 || $episode->getTranscript()) {
            $extent[] = count($episode->getPdfs()) + ($episode->getTranscript() ? 1 : 0) . ' transcript file(s)';
        }
        if ($audio) {
            $extent[] = "filesize {$audio->getFileSize()} Bytes";
        }
        $extent[] = "runtime {$episode->getRunTime()}";

        return $this->addRecordDefaults([
            'id' => "amp:episode:{$episode->getId()}",
            'parent_id' => "amp:season:{$episode->getSeason()->getId()}",
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Digital Document',
            'field_resource_type' => 'Audio',
            'title' => $episode->getTitle(),
            'field_alternative_title' => $episode->getSubTitle(),
            'field_identifier' => $episode->getGuid(),

            'field_description' => $episode->getDescription(),
            'field_note' => implode('|', array_filter([
                $episode->getBibliography() ?? '',
            ])),
            'field_physical_form' => 'Electronic|Sound recordings',
            'field_extent' => implode('|', $extent),
            'field_edtf_date_issued' => $episode->getDate()->format('Y-m-d'),
            'field_tags' => implode('|', $this->getFormattedKeywords(array_filter($episode->getKeywords()))),
            'field_linked_agent' => $this->getLinkedAgents($this->getEpisodeContributorPersonAndRoles($episode)),
            'field_sfu_permissions' => $episode->getPermissions(),
            'field_sfu_bibcite_title' => $episode->getTitle(),
            'field_sfu_bibcite_etdf_date' => $episode->getDate()->format('Y-m-d'),
            'field_sfu_bibcite_publishe' => $this->getSeasonPublisherText($episode->getSeason()),
        ]);
    }

    private function generateEpisodeAudioRecord(string $relativeFile, Episode $episode, Audio $audio, int $weight) : array {
        $filename = basename($relativeFile);

        return $this->addRecordDefaults([
            'id' => "amp:audio:{$audio->getId()}",
            'parent_id' => "amp:episode:{$episode->getId()}",
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Audio',
            'field_resource_type' => 'Audio',
            'title' => "Supplementary audio file ({$filename})",
            'field_identifier' => $audio->getSourceUrl(),

            'field_external_links' => $audio->getSourceUrl(),
            'field_physical_form' => 'Electronic|Sound recordings',
            'field_extent' => implode('|', [
                '1 audio file',
                "filesize {$audio->getFileSize()} Bytes",
                "runtime {$episode->getRunTime()}",
            ]),
            'field_edtf_date_issued' => $episode->getDate()->format('Y-m-d'),
            'field_tags' => implode('|', $this->getFormattedKeywords(array_filter($episode->getKeywords()))),
        ]);
    }

    private function generateGenericImageRecord(string $relativeFile, string $parentId, Image $image, int $weight) : array {
        $filename = basename($relativeFile);

        return $this->addRecordDefaults([
            'id' => "amp:image:{$image->getId()}",
            'parent_id' => $parentId,
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Image',
            'field_resource_type' => 'Still Image',
            'title' => "Supplementary image file ({$filename})",
            'field_identifier' => $image->getSourceUrl(),

            'field_description' => $image->getDescription(),
            'field_external_links' => $image->getSourceUrl(),
            'field_physical_form' => 'Electronic|Pictures',
            'field_extent' => implode('|', [
                '1 image',
                "filesize {$image->getFileSize()} Bytes",
                "dimensions {$image->getImageWidth()}px x {$image->getImageHeight()}px",
            ]),
        ]);
    }

    private function generateEpisodeImageRecord(string $relativeFile, Episode $episode, Image $image, int $weight) : array {
        $record = $this->generateGenericImageRecord($relativeFile, "amp:episode:{$episode->getId()}", $image, $weight);
        $record['field_tags'] = implode('|', $this->getFormattedKeywords(array_filter($episode->getKeywords())));
        $record['field_edtf_date_issued'] = $episode->getDate()->format('Y-m-d');

        return $record;
    }

    private function generateEpisodeTranscriptRecord(string $relativeFile, Episode $episode, Pdf $pdf, int $weight) : array {
        $filename = basename($relativeFile);

        return $this->addRecordDefaults([
            'id' => "amp:transcript:{$pdf->getId()}",
            'parent_id' => "amp:episode:{$episode->getId()}",
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Binary',
            'field_resource_type' => 'Text',
            'title' => "Supplementary transcript file ({$filename})",
            'field_identifier' => $pdf->getSourceUrl(),

            'field_external_links' => $pdf->getSourceUrl(),
            'field_physical_form' => 'Electronic|Text corpora',
            'field_extent' => implode('|', [
                '1 transcript file',
                "filesize {$pdf->getFileSize()} Bytes",
            ]),
            'field_edtf_date_issued' => $episode->getDate()->format('Y-m-d'),
            'field_tags' => implode('|', $this->getFormattedKeywords(array_filter($episode->getKeywords()))),
        ]);
    }

    private function generateEpisodeTranscriptTxtRecord(string $relativeFile, Episode $episode, int $weight) : array {
        $filename = basename($relativeFile);

        return $this->addRecordDefaults([
            'id' => "amp:transcript:txt:{$episode->getId()}",
            'parent_id' => "amp:episode:{$episode->getId()}",
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Binary',
            'field_resource_type' => 'Text',
            'title' => "Supplementary transcript file ({$filename})",

            'field_physical_form' => 'Electronic|Text corpora',
            'field_extent' => implode('|', [
                '1 transcript file',
            ]),
            'field_edtf_date_issued' => $episode->getDate()->format('Y-m-d'),
            'field_tags' => implode('|', array_filter($episode->getKeywords())),
        ]);
    }

    private function getCsvMap() : array {
        // column heading => default value
        return [
            'id' => '', // internal to workbench, doesn't matter but needs to be unique and consistent (ex: `amp:podcast:1`)
            'field_member_of' => '', // required when using parent_id (even if blank). inserted into each csv row to allow manual edits on the Podcast
            'parent_id' => '', // points to internal workbench id of parent item (ex: `amp:podcast:1`)
            'file' => '', // required relative path
            'field_weight' => '',
            'field_model' => '',
            'field_resource_type' => '',
            'title' => '',
            'field_alternative_title' => '',
            'field_identifier' => '',

            'field_description' => '',
            'field_note' => '',
            'field_external_links' => '',
            'field_physical_form' => '',
            'field_extent' => '',
            'field_edtf_date_issued' => '',
            'field_table_of_contents' => '',
            'field_tags' => '',
            'field_linked_agent' => '',
            'field_sfu_permissions' => '',
            'field_sfu_bibcite_title' => '',
            'field_sfu_bibcite_etdf_date' => '',
            'field_sfu_bibcite_publishe' => '',
        ];
    }

    protected function generate() : void {
        $this->updateMessage('Starting islandora export.');

        $this->filesystem->dumpFile("{$this->exportTmpRootDir}/amp_podcast_{$this->podcast->getId()}_config.yaml", $this->twig->render('export/format/islandora/config.yaml.twig', [
            'podcast' => $this->podcast,
        ]));
        // $this->filesystem->dumpFile("{$this->exportTmpRootDir}/amp_podcast_{$this->podcast->getId()}_config.yaml", $this->twig->render('export/format/islandora/config_test.yaml.twig', [
        //     'podcast' => $this->podcast,
        // ]));
        $inputDir = "{$this->exportTmpRootDir}/amp_podcast_{$this->podcast->getId()}_input_files";
        $this->filesystem->mkdir($inputDir, 0o777);

        $this->filesystem->mkdir("{$inputDir}/audio", 0o777);
        $audioFilenames = [];

        $this->filesystem->mkdir("{$inputDir}/image", 0o777);
        $imageFilenames = [];

        $this->filesystem->mkdir("{$inputDir}/transcript", 0o777);
        $transcriptFilenames = [];

        $csv = Writer::createFromPath("{$inputDir}/metadata.csv", 'w+');
        // $csv->setEscape('');
        $csv->setEnclosure('"');
        $csv->setDelimiter(',');
        $header = array_keys($this->getCsvMap());
        $csv->insertOne($header);

        $podcastWeight = 0;
        $csv->insertOne($this->generatePodcastRecord($this->podcast));

        foreach ($this->podcast->getImages() as $image) {
            if ( ! $image?->getFile()) {
                continue;
            }
            $relativeFile = 'image/' . $this->getSafeFileName($image->getOriginalName(), $image->getExtension(), $imageFilenames);
            $this->filesystem->copy($image->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");

            $csv->insertOne($this->generateGenericImageRecord($relativeFile, "amp:podcast:{$this->podcast->getId()}", $image, ++$podcastWeight));
        }

        $currentEpisode = 0;
        foreach ($this->podcast->getSeasons() as $season) {
            $seasonWeight = 0;
            $csv->insertOne($this->generateSeasonRecord($season, ++$podcastWeight));

            foreach ($season->getImages() as $image) {
                if ( ! $image?->getFile()) {
                    continue;
                }
                $relativeFile = 'image/' . $this->getSafeFileName($image->getOriginalName(), $image->getExtension(), $imageFilenames);
                $this->filesystem->copy($image->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");

                $csv->insertOne($this->generateGenericImageRecord($relativeFile, "amp:season:{$season->getId()}", $image, ++$seasonWeight));
            }

            foreach ($season->getEpisodes() as $episode) {
                $currentEpisode++;
                $this->updateMessage("Generating metadata for {$episode->getSlug()} ({$currentEpisode}/{$this->totalEpisodes})");

                $episodeWeight = 0;
                $episodeAudio = $this->getFirstAudio($episode);
                $relativeFile = $episodeAudio ? 'audio/' . $this->getSafeFileName($episodeAudio->getOriginalName(), $episodeAudio->getExtension(), $audioFilenames, false) : null;
                $csv->insertOne($this->generateEpisodeRecord($episode, ++$seasonWeight, $episodeAudio, $relativeFile));

                foreach ($episode->getAudios() as $audio) {
                    if ( ! $audio?->getFile()) {
                        continue;
                    }
                    $relativeFile = 'audio/' . $this->getSafeFileName($audio->getOriginalName(), $audio->getExtension(), $audioFilenames);
                    $this->filesystem->copy($audio->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");

                    $csv->insertOne($this->generateEpisodeAudioRecord($relativeFile, $episode, $audio, ++$episodeWeight));
                }

                foreach ($episode->getImages() as $image) {
                    if ( ! $image?->getFile()) {
                        continue;
                    }
                    $relativeFile = 'image/' . $this->getSafeFileName($image->getOriginalName(), $image->getExtension(), $imageFilenames);
                    $this->filesystem->copy($image->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");

                    $csv->insertOne($this->generateEpisodeImageRecord($relativeFile, $episode, $image, ++$episodeWeight));
                }

                foreach ($episode->getPdfs() as $pdf) {
                    if ( ! $pdf?->getFile()) {
                        continue;
                    }
                    $relativeFile = 'transcript/' . $this->getSafeFileName($pdf->getOriginalName(), $pdf->getExtension(), $transcriptFilenames);
                    $this->filesystem->copy($pdf->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");

                    $csv->insertOne($this->generateEpisodeTranscriptRecord($relativeFile, $episode, $pdf, ++$episodeWeight));
                }

                if ($episode->getTranscript()) {
                    $relativeFile = 'transcript/' . $this->getSafeFileName("{$episode->getTitle()}.txt", 'txt', $transcriptFilenames);
                    $this->filesystem->dumpFile("{$inputDir}/{$relativeFile}", $this->stripTags($episode->getTranscript()));

                    $csv->insertOne($this->generateEpisodeTranscriptTxtRecord($relativeFile, $episode, ++$episodeWeight));
                }

                $this->updateProgress(++$this->stepsCompleted);
            }
        }
    }
}
