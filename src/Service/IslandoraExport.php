<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Language;
use App\Entity\Podcast;
use App\Entity\Season;
use League\Csv\Writer;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Pdf;
use Soundasleep\Html2Text;

class IslandoraExport extends ExportService {
    private int $internalId = 0;

    private function generateInternalId() : string {
        return sprintf('%03d', ++$this->internalId);
    }

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

    private function getLangCode(?Language $language) : ?string {
        $fullCode = $language?->getName();
        if ($fullCode) {
            return mb_substr($fullCode, 0, 2);
        }

        return null;
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
            $result[$column] = array_key_exists($column, $record) ? Html2Text::convert($record[$column] ?? '', ['ignore_errors' => true]) : $default;
        }

        return $result;
    }

    private function generatePodcastRecord(string $id, Podcast $podcast, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => $id,
            'field_model' => 'Collection',
            'field_resource_type' => 'Collection',
            'thumbnail' => $thumbnail,
            'title' => $podcast->getTitle(),
            'langcode' => $this->getLangCode($podcast->getLanguage()),

            'field_alternative_title' => $podcast->getSubTitle(),
            'field_description_long' => $podcast->getDescription(),
            'field_related_websites' => $this->getPodcastWebsites($podcast),
            'field_extent' => implode('|', [
                '1 podcast',
                count($podcast->getSeasons()) . ' season(s)',
                count($podcast->getEpisodes()) . ' episode(s)',
                count($podcast->getImages()) . ' image file(s)',
            ]),
        ]);
    }

    private function generateSeasonRecord(string $id, string $parentId, Season $season, int $weight, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => $id,
            'parent_id' => $parentId,
            'field_weight' => "{$weight}",
            'field_model' => 'Collection',
            'field_resource_type' => 'Collection',
            'thumbnail' => $thumbnail,
            'title' => $season->getTitle(),
            'langcode' => $this->getLangCode($season->getPodcast()->getLanguage()),

            'field_alternative_title' => $season->getSubTitle(),
            'field_description_long' => $season->getDescription(),
            'field_related_websites' => $this->getSeasonWebsites($season),
            'field_extent' => implode('|', [
                '1 podcast season',
                count($season->getEpisodes()) . ' episode(s)',
                count($season->getImages()) . ' image file(s)',
            ]),
        ]);
    }

    private function generateEpisodeRecord(string $id, string $parentId, Episode $episode, int $weight, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => $id,
            'parent_id' => $parentId,
            'field_weight' => "{$weight}",
            'field_model' => 'Compound Object',
            'field_resource_type' => 'Collection',
            'thumbnail' => $thumbnail,
            'title' => $episode->getTitle(),
            'langcode' => $this->getLangCode($episode->getLanguage()),

            'field_alternative_title' => $episode->getSubTitle(),
            'field_description_long' => $episode->getDescription(),
            'field_extent' => implode('|', [
                '1 podcast episode',
                count($episode->getAudios()) . ' audio file(s)',
                count($episode->getImages()) . ' image file(s)',
                count($episode->getPdfs()) . ' pdf file(s)',
                "runtime {$episode->getRunTime()}",
            ]),
        ]);
    }

    private function generateAudioRecord(string $id, string $parentId, string $relativeFile, Episode $episode, Audio $audio, int $weight, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => $id,
            'parent_id' => $parentId,
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Audio',
            'field_resource_type' => 'Sound',
            'thumbnail' => $thumbnail,
            'title' => $audio->getOriginalName(),
            'langcode' => $this->getLangCode($episode->getLanguage()),

            'field_description_long' => $audio->getDescription(),
            // 'field_display_hints' => '',
            'field_related_websites' => $audio->getSourceUrl(),
            'field_extent' => implode('|', [
                '1 audio file',
                "filesize {$audio->getFileSize()}",
                "runtime {$episode->getRunTime()}",
            ]),
        ]);
    }

    private function generateImageRecord(string $id, string $parentId, string $relativeFile, Image $image, int $weight, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => $id,
            'parent_id' => $parentId,
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Image',
            'field_resource_type' => 'Still Image',
            'thumbnail' => $thumbnail,
            'title' => $image->getOriginalName(),

            'field_description_long' => $image->getDescription(),
            // 'field_display_hints' => '',
            'field_related_websites' => $image->getSourceUrl(),
            'field_extent' => implode('|', [
                '1 image',
                "filesize {$image->getFileSize()}",
                "dimensions {$image->getImageWidth()}px x {$image->getImageHeight()}px",
            ]),
        ]);
    }

    private function generateTranscriptRecord(string $id, string $parentId, string $relativeFile, Episode $episode, Pdf $pdf, int $weight, ?string $thumbnail) : array {
        return $this->addRecordDefaults([
            'id' => $id,
            'parent_id' => $parentId,
            'file' => $relativeFile,
            'field_weight' => "{$weight}",
            'field_model' => 'Digital Document',
            'field_resource_type' => 'Text',
            'thumbnail' => $thumbnail,
            'title' => $pdf->getOriginalName(),
            'langcode' => $this->getLangCode($episode->getLanguage()),

            'field_description_long' => $pdf->getDescription(),
            // 'field_display_hints' => '',
            'field_related_websites' => $pdf->getSourceUrl(),
            'field_extent' => implode('|', [
                '1 pdf file',
                "filesize {$pdf->getFileSize()}",
            ]),
        ]);
    }

    private function getCsvMap() : array {
        // column heading => default value
        return [
            'id' => '', // internal to workbench, doesn't matter but needs to be unique and consistent (ex: 001)
            'parent_id' => '', // points to internal workbench id of parent item (ex: 001)
            'file' => '', // required relative path
            'field_weight' => '',
            'field_model' => '',
            'field_resource_type' => '',
            'thumbnail' => '',
            'title' => '',
            'langcode' => 'en', // 2 letters language code

            'field_alternative_title' => '',
            'field_description_long' => '',
            'field_display_hints' => '',
            'field_related_websites' => '',
            'field_extent' => '',

            // 'field_genre' => '',

            // 'field_identifier' => '',
            // 'field_local_identifier' => '',
            // 'field_classification' => '',
            // 'field_dewey_classification' => '',
            // 'field_lcc_classification' => '',
            // 'field_edtf_date_created' => '',
            // 'field_edtf_date_issued' => '',
            // 'field_date_captured' => '',
            // 'field_subject' => '',
            // 'field_subject_general' => '',
            // 'field_subjects_name' => '',
            // 'field_rights' => '',
            // 'field_physical_form' => '',
        ];
    }

    protected function generate() : void {
        $this->internalId = 0;

        $this->updateMessage('Starting islandora export.');

        $this->filesystem->dumpFile("{$this->exportTmpRootDir}/amplify_podcast_{$this->podcast->getId()}_config.yaml", $this->twig->render('export/format/islandora/config.yaml.twig', [
            'podcast' => $this->podcast,
        ]));
        $inputDir = "{$this->exportTmpRootDir}/amplify_podcast_{$this->podcast->getId()}_input_files";
        $this->filesystem->mkdir($inputDir, 0o777);

        $audioThumb = 'audio_thumbnail.png';
        $this->filesystem->copy($this->parameterBagInterface->get('project_root_dir') . '/templates/export/format/islandora/audio_thumbnail.png', "{$inputDir}/{$audioThumb}");

        $csv = Writer::createFromPath("{$inputDir}/metadata.csv", 'w+');
        // $csv->setEscape('');
        $csv->setEnclosure('"');
        $csv->setDelimiter(',');
        $header = array_keys($this->getCsvMap());
        $csv->insertOne($header);

        $podcastId = $this->generateInternalId();
        $podcastWeight = 0;
        $podcastThumbnail = $this->getFirstThumbnail($this->podcast->getImages());
        $csv->insertOne($this->generatePodcastRecord($podcastId, $this->podcast, $podcastThumbnail));

        foreach ($this->podcast->getImages() as $image) {
            if ( ! $image?->getFile()) {
                continue;
            }
            $relativeFile = "image_{$image->getId()}.{$image->getExtension()}";
            $this->filesystem->copy($image->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");
            $relativeThumbFile = "image_{$image->getId()}_tn.png";
            $this->filesystem->copy($image->getThumbFile()->getRealPath(), "{$inputDir}/{$relativeThumbFile}");

            $csv->insertOne($this->generateImageRecord($this->generateInternalId(), $podcastId, $relativeFile, $image, ++$podcastWeight, $relativeThumbFile));
        }

        $currentEpisode = 0;
        foreach ($this->podcast->getSeasons() as $season) {

            $seasonId = $this->generateInternalId();
            $seasonWeight = 0;
            $seasonThumbnail = $this->getFirstThumbnail($season->getImages());
            $csv->insertOne($this->generateSeasonRecord($seasonId, $podcastId, $season, ++$podcastWeight, $seasonThumbnail));

            foreach ($season->getImages() as $image) {
                if ( ! $image?->getFile()) {
                    continue;
                }
                $relativeFile = "image_{$image->getId()}.{$image->getExtension()}";
                $this->filesystem->copy($image->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");
                $relativeThumbFile = "image_{$image->getId()}_tn.png";
                $this->filesystem->copy($image->getThumbFile()->getRealPath(), "{$inputDir}/{$relativeThumbFile}");

                $csv->insertOne($this->generateImageRecord($this->generateInternalId(), $seasonId, $relativeFile, $image, ++$seasonWeight, $relativeThumbFile));
            }

            foreach ($season->getEpisodes() as $episode) {
                $currentEpisode++;
                $this->updateMessage("Generating metadata for {$episode->getSlug()} ({$currentEpisode}/{$this->totalEpisodes})");

                $episodeId = $this->generateInternalId();
                $episodeWeight = 0;
                $episodeThumbnail = $this->getFirstThumbnail($episode->getImages());
                $csv->insertOne($this->generateEpisodeRecord($episodeId, $seasonId, $episode, ++$seasonWeight, $episodeThumbnail));

                foreach ($episode->getAudios() as $audio) {
                    if ( ! $audio?->getFile()) {
                        continue;
                    }
                    $relativeFile = "audio_{$audio->getId()}.{$audio->getExtension()}";
                    $this->filesystem->copy($audio->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");

                    $csv->insertOne($this->generateAudioRecord($this->generateInternalId(), $episodeId, $relativeFile, $episode, $audio, ++$episodeWeight, $audioThumb));
                }

                foreach ($episode->getImages() as $image) {
                    if ( ! $image?->getFile()) {
                        continue;
                    }
                    $relativeFile = "image_{$image->getId()}.{$image->getExtension()}";
                    $this->filesystem->copy($image->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");
                    $relativeThumbFile = "image_{$image->getId()}_tn.png";
                    $this->filesystem->copy($image->getThumbFile()->getRealPath(), "{$inputDir}/{$relativeThumbFile}");

                    $csv->insertOne($this->generateImageRecord($this->generateInternalId(), $episodeId, $relativeFile, $image, ++$episodeWeight, $relativeThumbFile));
                }

                foreach ($episode->getPdfs() as $pdf) {
                    if ( ! $pdf?->getFile()) {
                        continue;
                    }
                    $relativeFile = "transcript_{$pdf->getId()}.pdf";
                    $this->filesystem->copy($pdf->getFile()->getRealPath(), "{$inputDir}/{$relativeFile}");
                    $relativeThumbFile = "transcript_{$pdf->getId()}_tn.png";
                    $this->filesystem->copy($pdf->getThumbFile()->getRealPath(), "{$inputDir}/{$relativeThumbFile}");

                    $csv->insertOne($this->generateTranscriptRecord($this->generateInternalId(), $episodeId, $relativeFile, $episode, $pdf, ++$episodeWeight, $relativeThumbFile));
                }

                $this->updateProgress(++$this->stepsCompleted);
            }
        }
    }
}
