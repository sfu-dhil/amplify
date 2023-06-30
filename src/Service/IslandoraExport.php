<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Podcast;
use App\Entity\Season;
use League\Csv\Writer;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Pdf;

class IslandoraExport extends ExportService {
    private function getFirstThumbnail(array $images) : ?string {
        $thumbnail = null;
        foreach ($images as $image) {
            if (null !== $image?->getFile()) {
                $thumbnail = "image_{$image->getId()}_tn.png";

                break;
            }
        }

        return $thumbnail;
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
        }

        return $result;
    }

    private function getLinkedAgents(array $contributions) : string {
        $linked_agents = [];
        foreach ($contributions as $contribution) {
            $person = $contribution['person'];
            foreach ($contribution['roles'] as $role) {
                if ($role->getRelatorTerm()) {
                    $linked_agents[]= "relators:{$role->getRelatorTerm()}:person:{$person->getSortableName()}";
                }
            }
        }
        return implode('|', $linked_agents);
    }

    private function generatePodcastRecord(Podcast $podcast, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => "amplify:podcast:{$podcast->getId()}",
            'field_model' => 'Collection',
            'field_resource_type' => 'Collection',
            'thumbnail' => $thumbnail,
            'title' => $podcast->getTitle(),
            'field_alternative_title' => $podcast->getSubTitle(),
            'field_display_title' => $podcast->getTitle(),
            'field_identifier' => $podcast->getGuid(),

            'field_abstract' => $podcast->getDescription(),
            'field_description' => mb_strimwidth($podcast->getDescription() ?? '', 0, 252, '...'),
            'field_description_long' => $podcast->getDescription(),
            'field_related_websites' => $this->getPodcastWebsites($podcast),
            'field_physical_form' => 'Electronic|Sound recordings',
            'field_extent' => implode('|', [
                '1 podcast',
                count($podcast->getSeasons()) . ' season(s)',
                count($podcast->getEpisodes()) . ' episode(s)',
                count($podcast->getImages()) . ' image file(s)',
            ]),
            'field_date_captured' => $podcast->getUpdated()->format('Y-m-d'),
            'field_subject' => implode('|', array_filter($podcast->getKeywords())),
            'field_table_of_contents' => $this->twig->render('export/format/islandora/podcast_toc.html.twig', [
                'podcast' => $podcast,
            ]),
            'field_linked_agent' => $this->getLinkedAgents($this->getPodcastContributorPersonAndRoles($podcast)),
        ]);
    }

    private function generateSeasonRecord(Season $season, int $weight, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => "amplify:season:{$season->getId()}",
            'parent_id' => "amplify:podcast:{$season->getPodcast()->getId()}",
            'field_weight' => "{$weight}",
            'field_model' => 'Collection',
            'field_resource_type' => 'Collection',
            'thumbnail' => $thumbnail,
            'title' => $season->getTitle(),
            'field_alternative_title' => $season->getSubTitle(),
            'field_display_title' => $season->getTitle(),

            'field_abstract' => $season->getDescription(),
            'field_description' => mb_strimwidth($season->getDescription() ?? '', 0, 252, '...'),
            'field_description_long' => $season->getDescription(),
            'field_related_websites' => $this->getSeasonWebsites($season),
            'field_physical_form' => 'Electronic|Sound recordings',
            'field_extent' => implode('|', [
                '1 podcast season',
                count($season->getEpisodes()) . ' episode(s)',
                count($season->getImages()) . ' image file(s)',
            ]),
            'field_date_captured' => $season->getUpdated()->format('Y-m-d'),
            'field_subject' => implode('|', array_filter($season->getPodcast()->getKeywords())),
            'field_table_of_contents' => $this->twig->render('export/format/islandora/season_toc.html.twig', [
                'season' => $season,
            ]),
            'field_linked_agent' => $this->getLinkedAgents($this->getSeasonContributorPersonAndRoles($season)),
        ]);
    }

    private function generateEpisodeRecord(Episode $episode, int $weight, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => "amplify:episode:{$episode->getId()}",
            'parent_id' => "amplify:season:{$episode->getSeason()->getId()}",
            'field_weight' => "{$weight}",
            'field_model' => 'Collection',
            'field_resource_type' => 'Collection',
            'thumbnail' => $thumbnail,
            'title' => $episode->getTitle(),
            'field_alternative_title' => $episode->getSubTitle(),
            'field_display_title' => $episode->getTitle(),
            'field_identifier' => $episode->getGuid(),

            'field_abstract' => $episode->getDescription(),
            'field_description' => mb_strimwidth($episode->getDescription() ?? '', 0, 252, '...'),
            'field_description_long' => $episode->getDescription(),
            'field_note' => implode('|', array_filter([
                $episode->getPermissions() ?? '',
                $episode->getBibliography() ?? '',
            ])),
            'field_physical_form' => 'Electronic|Sound recordings',
            'field_extent' => implode('|', [
                '1 podcast episode',
                count($episode->getAudios()) . ' audio file(s)',
                count($episode->getImages()) . ' image file(s)',
                count($episode->getPdfs()) . ' pdf file(s)',
                "runtime {$episode->getRunTime()}",
            ]),
            'field_edtf_date_issued' => $episode->getDate()->format('Y-m-d'),
            'field_date_captured' => $episode->getUpdated()->format('Y-m-d'),
            'field_subject' => implode('|', array_filter($episode->getKeywords())),
            'field_linked_agent' => $this->getLinkedAgents($this->getEpisodeContributorPersonAndRoles($episode)),
        ]);
    }

    private function generateEpisodeAudioRecord(string $relativeFile, Episode $episode, Audio $audio, int $weight) : array {
        return $this->addRecordDefaults([
            'id' => "amplify:audio:{$audio->getId()}",
            'parent_id' => "amplify:episode:{$episode->getId()}",
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Audio',
            'field_resource_type' => 'Sound',
            'title' => $audio->getOriginalName(),
            'field_display_title' => $audio->getOriginalName(),
            'field_identifier' => $audio->getSourceUrl(),

            'field_abstract' => $audio->getDescription(),
            'field_description' => mb_strimwidth($audio->getDescription() ?? '', 0, 252, '...'),
            'field_description_long' => $audio->getDescription(),
            'field_note' => $audio->getLicense(),
            'field_related_websites' => $audio->getSourceUrl(),
            'field_physical_form' => 'Electronic|Sound recordings',
            'field_extent' => implode('|', [
                '1 audio file',
                "filesize {$audio->getFileSize()}",
                "runtime {$episode->getRunTime()}",
            ]),
            'field_edtf_date_issued' => $episode->getDate()->format('Y-m-d'),
            'field_date_captured' => $audio->getUpdated()->format('Y-m-d'),
            'field_subject' => implode('|', array_filter($episode->getKeywords())),
            'field_linked_agent' => $this->getLinkedAgents($this->getEpisodeContributorPersonAndRoles($episode)),
        ]);
    }

    private function generateGenericImageRecord(string $relativeFile, string $parentId, Image $image, int $weight, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => "amplify:image:{$image->getId()}",
            'parent_id' => $parentId,
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Image',
            'field_resource_type' => 'Still Image',
            'thumbnail' => $thumbnail,
            'title' => $image->getOriginalName(),
            'field_display_title' => $image->getOriginalName(),
            'field_identifier' => $image->getSourceUrl(),

            'field_abstract' => $image->getDescription(),
            'field_description' => mb_strimwidth($image->getDescription() ?? '', 0, 252, '...'),
            'field_description_long' => $image->getDescription(),
            'field_note' => $image->getLicense(),
            'field_related_websites' => $image->getSourceUrl(),
            'field_physical_form' => 'Electronic|Pictures',
            'field_extent' => implode('|', [
                '1 image',
                "filesize {$image->getFileSize()}",
                "dimensions {$image->getImageWidth()}px x {$image->getImageHeight()}px",
            ]),
            'field_date_captured' => $image->getUpdated()->format('Y-m-d'),
        ]);
    }

    private function generateEpisodeImageRecord(string $relativeFile, Episode $episode, Image $image, int $weight, ?string $thumbnail) : array {
        $record = $this->generateGenericImageRecord($relativeFile, "amplify:episode:{$episode->getId()}", $image, $weight, $thumbnail);
        $record['field_subject'] = implode('|', array_filter($episode->getKeywords()));
        $record['field_edtf_date_issued'] = $episode->getDate()->format('Y-m-d');
        $record['field_linked_agent'] = $this->getLinkedAgents($this->getEpisodeContributorPersonAndRoles($episode));

        return $record;
    }

    private function generateEpisodeTranscriptRecord(string $relativeFile, Episode $episode, Pdf $pdf, int $weight, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => "amplify:transcript:{$pdf->getId()}",
            'parent_id' => "amplify:episode:{$episode->getId()}",
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Digital Document',
            'field_resource_type' => 'Text',
            'thumbnail' => $thumbnail,
            'title' => $pdf->getOriginalName(),
            'field_display_title' => $pdf->getOriginalName(),
            'field_identifier' => $pdf->getSourceUrl(),

            'field_abstract' => $pdf->getDescription(),
            'field_description' => mb_strimwidth($pdf->getDescription() ?? '', 0, 252, '...'),
            'field_description_long' => $pdf->getDescription(),
            'field_note' => $pdf->getLicense(),
            'field_related_websites' => $pdf->getSourceUrl(),
            'field_physical_form' => 'Electronic|Text corpora',
            'field_extent' => implode('|', [
                '1 pdf file',
                "filesize {$pdf->getFileSize()}",
            ]),
            'field_edtf_date_issued' => $episode->getDate()->format('Y-m-d'),
            'field_date_captured' => $pdf->getUpdated()->format('Y-m-d'),
            'field_subject' => implode('|', array_filter($episode->getKeywords())),
        ]);
    }

    private function getCsvMap() : array {
        // column heading => default value
        return [
            'id' => '', // internal to workbench, doesn't matter but needs to be unique and consistent (ex: `amplify:podcast:1`)
            'parent_id' => '', // points to internal workbench id of parent item (ex: `amplify:podcast:1`)
            'file' => '', // required relative path
            'field_weight' => '',
            'field_model' => '',
            'field_resource_type' => '',
            'thumbnail' => '',
            'title' => '',
            'field_alternative_title' => '',
            'field_display_title' => '',
            'field_identifier' => '',

            'field_abstract' => '',
            'field_description' => '',
            'field_description_long' => '',
            'field_note' => '',
            // 'field_display_hints' => '',
            'field_related_websites' => '',
            'field_physical_form' => '',
            'field_extent' => '',
            'field_edtf_date_issued' => '',
            'field_date_captured' => '',
            'field_table_of_contents' => '',
            'field_subject' => '',
            'field_linked_agent' => '',
        ];
    }

    protected function generate() : void {
        $this->updateMessage('Starting islandora export.');

        $this->filesystem->dumpFile("{$this->exportTmpRootDir}/amplify_podcast_{$this->podcast->getId()}_config.yaml", $this->twig->render('export/format/islandora/config.yaml.twig', [
            'podcast' => $this->podcast,
        ]));
        $inputDir = "{$this->exportTmpRootDir}/amplify_podcast_{$this->podcast->getId()}_input_files";
        $this->filesystem->mkdir($inputDir, 0o777);

        $csv = Writer::createFromPath("{$inputDir}/metadata.csv", 'w+');
        // $csv->setEscape('');
        $csv->setEnclosure('"');
        $csv->setDelimiter(',');
        $header = array_keys($this->getCsvMap());
        $csv->insertOne($header);

        $podcastWeight = 0;
        $podcastThumbnail = $this->getFirstThumbnail($this->podcast->getImages());
        $csv->insertOne($this->generatePodcastRecord($this->podcast, $podcastThumbnail));

        foreach ($this->podcast->getImages() as $image) {
            if ( ! $image?->getFile()) {
                continue;
            }
            $relativeFile = "image_{$image->getId()}.{$image->getExtension()}";
            $this->filesystem->copy($image->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");
            $relativeThumbFile = "image_{$image->getId()}_tn.png";
            $this->filesystem->copy($image->getThumbFile()->getRealPath(), "{$inputDir}/{$relativeThumbFile}");

            $csv->insertOne($this->generateGenericImageRecord($relativeFile, "amplify:podcast:{$this->podcast->getId()}", $image, ++$podcastWeight, $relativeThumbFile));
        }

        $currentEpisode = 0;
        foreach ($this->podcast->getSeasons() as $season) {
            $seasonWeight = 0;
            $seasonThumbnail = $this->getFirstThumbnail($season->getImages());
            $csv->insertOne($this->generateSeasonRecord($season, ++$podcastWeight, $seasonThumbnail));

            foreach ($season->getImages() as $image) {
                if ( ! $image?->getFile()) {
                    continue;
                }
                $relativeFile = "image_{$image->getId()}.{$image->getExtension()}";
                $this->filesystem->copy($image->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");
                $relativeThumbFile = "image_{$image->getId()}_tn.png";
                $this->filesystem->copy($image->getThumbFile()->getRealPath(), "{$inputDir}/{$relativeThumbFile}");

                $csv->insertOne($this->generateGenericImageRecord($relativeFile, "amplify:season:{$season->getId()}", $image, ++$seasonWeight, $relativeThumbFile));
            }

            foreach ($season->getEpisodes() as $episode) {
                $currentEpisode++;
                $this->updateMessage("Generating metadata for {$episode->getSlug()} ({$currentEpisode}/{$this->totalEpisodes})");

                $episodeWeight = 0;
                $episodeThumbnail = $this->getFirstThumbnail($episode->getImages());
                $csv->insertOne($this->generateEpisodeRecord($episode, ++$seasonWeight, $episodeThumbnail));

                foreach ($episode->getAudios() as $audio) {
                    if ( ! $audio?->getFile()) {
                        continue;
                    }
                    $relativeFile = "audio_{$audio->getId()}.{$audio->getExtension()}";
                    $this->filesystem->copy($audio->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");

                    $csv->insertOne($this->generateEpisodeAudioRecord($relativeFile, $episode, $audio, ++$episodeWeight));
                }

                foreach ($episode->getImages() as $image) {
                    if ( ! $image?->getFile()) {
                        continue;
                    }
                    $relativeFile = "image_{$image->getId()}.{$image->getExtension()}";
                    $this->filesystem->copy($image->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");
                    $relativeThumbFile = "image_{$image->getId()}_tn.png";
                    $this->filesystem->copy($image->getThumbFile()->getRealPath(), "{$inputDir}/{$relativeThumbFile}");

                    $csv->insertOne($this->generateEpisodeImageRecord($relativeFile, $episode, $image, ++$episodeWeight, $relativeThumbFile));
                }

                foreach ($episode->getPdfs() as $pdf) {
                    if ( ! $pdf?->getFile()) {
                        continue;
                    }
                    $relativeFile = "transcript_{$pdf->getId()}.pdf";
                    $this->filesystem->copy($pdf->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");
                    $relativeThumbFile = "transcript_{$pdf->getId()}_tn.png";
                    $this->filesystem->copy($pdf->getThumbFile()->getRealPath(), "{$inputDir}/{$relativeThumbFile}");

                    $csv->insertOne($this->generateEpisodeTranscriptRecord($relativeFile, $episode, $pdf, ++$episodeWeight, $relativeThumbFile));
                }

                $this->updateProgress(++$this->stepsCompleted);
            }
        }
    }
}
