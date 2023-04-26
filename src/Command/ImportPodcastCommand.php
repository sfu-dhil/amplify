<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Category;
use App\Entity\Episode;
use App\Entity\Import;
use App\Entity\Language;
use App\Entity\Podcast;
use App\Entity\Season;
use App\Repository\CategoryRepository;
use App\Repository\ImportRepository;
use App\Repository\LanguageRepository;
use App\Repository\PodcastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use Lukaswhite\PodcastFeedParser\Episode as RssEpisode;
use Lukaswhite\PodcastFeedParser\Episodes as RssEpisodes;
use Lukaswhite\PodcastFeedParser\Parser as RssParser;
use Lukaswhite\PodcastFeedParser\Podcast as RssPodcast;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\AudioContainerInterface;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Entity\Pdf;
use Nines\MediaBundle\Entity\PdfContainerInterface;
use Nines\MediaBundle\Service\AudioManager;
use Nines\MediaBundle\Service\ImageManager;
use Nines\MediaBundle\Service\PdfManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(name: 'app:import:podcast')]
class ImportPodcastCommand extends Command {
    public function __construct(
        private EntityManagerInterface $em,
        private PodcastRepository $podcastRepository,
        private ImportRepository $importRepository,
        private LanguageRepository $languageRepository,
        private CategoryRepository $categoryRepository,
        private AudioManager $audioManager,
        private ImageManager $imageManager,
        private PdfManager $pdfManager,
        private Filesystem $filesystem,
        private Client $client,
        private RssParser $parser = new RssParser(),
        private ?int $totalSteps = null,
        private ?string $rssUrl = null,
        private ?Podcast $podcast = null,
        private ?Import $import = null,
        private ?RssPodcast $rssPodcast = null,
        private ?RssEpisodes $rssEpisodes = null,
        private ?OutputInterface $output = null,
        private array $seasons = [],
        private array $episodes = [],
        private array $mediaRequests = [],
    ) {
        parent::__construct();
    }

    private function cleanupTempFiles() : void {
        $this->output->writeln('Cleaning up ' . sys_get_temp_dir());
        $tempFiles = glob(sys_get_temp_dir() . "/import_podcast_{$this->podcast->getId()}_*");
        $this->filesystem->remove($tempFiles);
    }

    private function addPodcastMediaFetchRequest(Podcast $podcast, string $url) : void {
        // check if sourceUrl already exists for podcast
        if ((bool) $podcast->getImageBySourceUrl($url)) {
            return;
        }

        if ( ! array_key_exists($url, $this->mediaRequests)) {
            $filename = basename(parse_url($url, PHP_URL_PATH));
            $tempFilePath = $this->filesystem->tempnam(sys_get_temp_dir(), "import_podcast_{$this->podcast->getId()}_", "_{$filename}");

            $this->mediaRequests[$url] = [
                'entities' => [],
                'filename' => $filename,
                'tempFilePath' => $tempFilePath,
            ];
        }
        // make sure podcast is unique for resource
        if ( ! in_array($podcast, $this->mediaRequests[$url], true)) {
            $this->mediaRequests[$url]['entities'][] = $podcast;
        }
    }

    private function addEpisodeMediaFetchRequest(Episode $episode, string $url) : void {
        // check if sourceUrl already exists for episode
        if ((bool) $episode->getAudioBySourceUrl($url) || (bool) $episode->getImageBySourceUrl($url) || (bool) $episode->getPdfBySourceUrl($url)) {
            return;
        }

        if ( ! array_key_exists($url, $this->mediaRequests)) {
            $filename = basename(parse_url($url, PHP_URL_PATH));
            $tempFilePath = $this->filesystem->tempnam(sys_get_temp_dir(), "import_podcast_{$this->podcast->getId()}_", "_{$filename}");

            $this->mediaRequests[$url] = [
                'entities' => [],
                'filename' => $filename,
                'tempFilePath' => $tempFilePath,
            ];
        }
        // make sure episode is unique for resource
        if ( ! in_array($episode, $this->mediaRequests[$url], true)) {
            $this->mediaRequests[$url]['entities'][] = $episode;
        }
    }

    private function addImageToEntity(ImageContainerInterface $entity, UploadedFile $upload, string $sourceUrl) : void {
        $image = new Image();
        $image->setFile($upload);
        $image->setEntity($entity);
        $image->setDescription('');
        $image->setSourceUrl($sourceUrl);
        $image->prePersist();

        $this->em->persist($image);
        $entity->addImage($image);
        $this->em->flush();
    }

    private function addAudioToEntity(AudioContainerInterface $entity, UploadedFile $upload, string $sourceUrl) : void {
        $audio = new Audio();
        $audio->setFile($upload);
        $audio->setEntity($entity);
        $audio->setDescription('');
        $audio->setSourceUrl($sourceUrl);
        $audio->prePersist();

        $this->em->persist($audio);
        $entity->addAudio($audio);
        $this->em->flush();
    }

    private function addPdfToEntity(PdfContainerInterface $entity, UploadedFile $upload, string $sourceUrl) : void {
        $pdf = new Pdf();
        $pdf->setFile($upload);
        $pdf->setEntity($entity);
        $pdf->setDescription('');
        $pdf->setSourceUrl($sourceUrl);
        $pdf->prePersist();

        $this->em->persist($pdf);
        $entity->addPdf($pdf);
        $this->em->flush();
    }

    private function getSeasonNumber(RssEpisode $rssEpisode, int $default) : int {
        $seasonNumber = (int) $rssEpisode->getSeason();

        return 0 === $seasonNumber ? $default : $seasonNumber;
    }

    private function getEpisodeNumber(RssEpisode $rssEpisode, int $default) : int {
        $episodeNumber = (int) $rssEpisode->getEpisodeNumber();

        return 0 === $episodeNumber ? $default : $episodeNumber;
    }

    private function getCategory(string $name) : Category {
        $category = $this->categoryRepository->findOneBy([
            'label' => $name,
        ]);
        if ( ! $category) {
            $category = new Category();
            $category->setLabel($name);
            $this->em->persist($category);
            $this->em->flush();
        }

        return $category;
    }

    private function updateMessage(string $message) : void {
        $this->output->writeln($message);
        if ($this->import) {
            $this->import->setMessage($message);
            $this->em->persist($this->import);
            $this->em->flush();
        }
    }

    private function updateProgress(int $step) : void {
        if ($this->import) {
            $this->import->setProgress((int) ($step * 100 / $this->totalSteps));
            $this->em->persist($this->import);
            $this->em->flush();
        }
    }

    protected function configure() : void {
        $this->setDescription('Import Podcast from RSS feed');
        $this->addArgument(
            'url',
            InputArgument::REQUIRED,
            'RSS feed url.'
        );
        $this->addArgument(
            'podcastId',
            InputArgument::OPTIONAL,
            'ID of podcast.'
        );
        $this->addArgument(
            'importId',
            InputArgument::OPTIONAL,
            'ID of import.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $this->output = $output;

        $this->rssUrl = $input->getArgument('url');
        $podcastId = $input->getArgument('podcastId');
        $importId = $input->getArgument('importId');

        if ( ! $this->rssUrl) {
            $this->output->writeln('No RSS url found.');

            return 0;
        }

        $this->podcast = $podcastId ? $this->podcastRepository->find($podcastId) : new Podcast();
        if ($podcastId && ! $this->podcast) {
            $this->output->writeln('No podcast found.');

            return 0;
        }

        $this->import = $importId ? $this->importRepository->find($importId) : null;
        if ($importId && ! $this->import) {
            $this->output->writeln('No import found.');

            return 0;
        }

        try {
            $this->updateMessage("Fetching RSS feed from {$this->rssUrl}");

            try {
                $response = $this->client->get($this->rssUrl);
            } catch (ConnectException $e) {
                $this->updateMessage('Could not connect to RSS feed url.');
                $this->output->writeln("Message: {$e->getMessage()}");

                return 0;
            } catch (RequestException $e) {
                $this->updateMessage("Error accessing RSS feed. Error: {$e->getMessage()}");

                return 0;
            }

            if (200 !== $response->getStatusCode()) {
                $this->updateMessage("Could not read RSS feed. Code {$response->getStatusCode()} Message: {$response->getBody()->getContents()}");

                return 0;
            }

            $this->seasons = [];
            foreach ($this->podcast->getSeasons() as $season) {
                if ($season->getNumber() > 0) {
                    $this->seasons[$season->getNumber()] = $season;
                }
            }
            $this->episodes = [];
            foreach ($this->podcast->getEpisodes() as $episode) {
                if ($episode->getGuid()) {
                    $this->episodes[$episode->getGuid()] = $episode;
                }
            }

            $this->rssPodcast = $this->parser->setContent($response->getBody()->getContents())->run();
            $this->rssEpisodes = $this->rssPodcast->getEpisodes();

            $this->updateMessage('Import started');
            $this->audioManager->setCopy(true);
            $this->imageManager->setCopy(true);
            $this->pdfManager->setCopy(true);

            $startTime = microtime(true);
            $this->processPodcast();
            $this->processSeasons();
            $this->processEpisodes();
            $this->processMedia();
            $this->cleanupTempFiles();

            $executionTime = microtime(true) - $startTime;
            $timeInMinutes = number_format($executionTime / 60.0, 2);
            $this->updateMessage("Import completed in {$timeInMinutes} minutes");

            return 1;
        } catch (Exception $e) {
            $this->updateMessage('An unexpected error occurred.');
            $this->output->writeln("Message: {$e->getMessage()}");
            $this->output->writeln("Trace: {$e->getTraceAsString()}");

            return 0;
        }
    }

    /**
     * Update all retrievable Podcast fields
     * There aren't ways to update license and publisher
     * contributor could only be updated with 1 owner, 1 author, and possibly 1 editor.
     */
    public function processPodcast() : void {
        $this->updateMessage('Processing Podcast metadata');

        $title = $this->rssPodcast->getTitle();
        if ($title && ! $this->podcast->getTitle()) {
            $this->podcast->setTitle(mb_strimwidth($title, 0, 252, '...'));
        }

        $subtitle = $this->rssPodcast->getSubtitle();
        if ($subtitle && ! $this->podcast->getSubTitle()) {
            $this->podcast->setSubTitle(mb_strimwidth($subtitle, 0, 252, '...'));
        }

        $explicit = $this->rssPodcast->getExplicit();
        if (is_null($explicit)) {
            $this->podcast->setExplicit('yes' === $explicit);
        }

        $description = $this->rssPodcast->getDescription();
        if ($description && ! $this->podcast->getDescription()) {
            $this->podcast->setDescription($description);
        }

        $copyright = $this->rssPodcast->getCopyright();
        if ($copyright && ! $this->podcast->getCopyright()) {
            $this->podcast->setCopyright($copyright);
        }

        $website = $this->rssPodcast->getLink();
        if ($website && ! $this->podcast->getWebsite()) {
            $this->podcast->setWebsite($website);
        }

        if ( ! $this->podcast->getRss()) {
            $this->podcast->setRss($this->rssUrl);
        }

        // skip `license`
        // doesn't seem to be part of RSS feeds

        $languageCode = $this->rssPodcast->getLanguage();
        if ($languageCode && ! $this->podcast->getLanguage()) {
            $language = $this->languageRepository->findOneBy([
                'name' => $languageCode,
            ]);
            if ( ! $language) {
                $language = new Language();
                $language->setName($languageCode);
                $language->setLabel($languageCode);
                $this->em->persist($language);
                $this->em->flush();
            }
            $this->podcast->setLanguage($language);
        }

        $this->em->persist($this->podcast);
        $this->em->flush();

        // skip `publisher`
        // doesn't seem to be part of RSS feeds

        $categories = $this->rssPodcast->getCategories();
        if ($categories) {
            foreach ($categories as $rssCategory) {
                $name = html_entity_decode($rssCategory->getName());
                $category = $this->getCategory($name);
                $this->podcast->addCategory($category);

                if ($rssCategory->getChildren()) {
                    foreach ($rssCategory->getChildren() as $rssSubcategory) {
                        $subName = html_entity_decode($rssSubcategory->getName());
                        $category = $this->getCategory("{$name} - {$subName}");
                        $this->podcast->addCategory($category);
                    }
                }
            }
            $this->em->persist($this->podcast);
            $this->em->flush();
        }

        if ($this->import) {
            $this->podcast->addImport($this->import);
            $this->em->persist($this->import);
            $this->em->flush();
        }

        // include `contributor` + `contributor_role`?
        // we can get author, owner at the podcast level but nothing else

        $artwork = $this->rssPodcast->getArtwork();
        if ($artwork) {
            $this->addPodcastMediaFetchRequest($this->podcast, $artwork->getUri());
        }

        $image = $this->rssPodcast->getImage();
        if ($image) {
            $this->addPodcastMediaFetchRequest($this->podcast, $image->getUrl());
        }
    }

    /**
     * Create all missing seasons
     * Podcasts RSS feeds have limited Season information so at most we can generate missing seasons
     * with extremely limited populated fields.
     */
    public function processSeasons() : void {
        $this->updateMessage('Processing Season metadata');

        foreach ($this->rssEpisodes as $rssEpisode) {
            $seasonNumber = $this->getSeasonNumber($rssEpisode, 1);
            $season = $this->seasons[$seasonNumber] ?? null;

            if (null === $season) {
                $this->output->writeln("Generating stub for Season {$seasonNumber}");
                $season = new Season();
                $season->setNumber($seasonNumber);
                $season->setTitle("Season {$seasonNumber}");
                $season->setSubTitle(null);
                $season->setDescription("Season {$seasonNumber}");
                $season->setPodcast($this->podcast);
                $this->podcast->addSeason($season);

                $this->em->persist($season);
                $this->seasons[$seasonNumber] = $season;
                $this->em->flush();
            }
        }
    }

    /**
     * Update/Create episodes.
     */
    public function processEpisodes() : void {
        $this->updateMessage('Processing Episode metadata');
        $totalEpisodes = count($this->rssEpisodes);

        foreach ($this->rssEpisodes as $index => $rssEpisode) {
            $guid = $rssEpisode->getGuid();
            $this->output->writeln("Processing Episode guid {$guid}");
            $episode = $this->episodes[$guid] ?? null;

            if (null === $episode) {
                $episode = new Episode();
                $episode->setGuid($guid);

                $episode->setPodcast($this->podcast);
                $this->podcast->addEpisode($episode);
                $this->episodes[$guid] = $episode;
            }
            $season = $this->seasons[$this->getSeasonNumber($rssEpisode, 1)];
            $episode->setSeason($season);

            if (null === $episode->getNumber()) {
                $episode->setNumber($this->getEpisodeNumber($rssEpisode, $totalEpisodes - (int) $index));
                $this->output->writeln("- Season {$episode->getSeason()->getNumber()} Episode {$episode->getNumber()}");
            }

            $publishedDate = $rssEpisode->getPublishedDate();
            if ($publishedDate && ! $episode->getDate()) {
                $episode->setDate($publishedDate);
            }

            $duration = $rssEpisode->getDuration();
            if ($duration && ! $episode->getRunTime()) {
                $episode->setRunTime(gmdate('H:i:s', (int) $duration));
            }

            $title = $rssEpisode->getTitle();
            if ($title && ! $episode->getTitle()) {
                $episode->setTitle(mb_strimwidth($title, 0, 252, '...'));
            }

            $subtitle = $rssEpisode->getSubtitle();
            if ($subtitle && ! $episode->getSubTitle()) {
                $episode->setSubTitle(mb_strimwidth($subtitle, 0, 252, '...'));
            }

            // skip `bibliography`
            // not part of RSS feed

            // skip `transcript`
            // not part of RSS feed

            $description = $rssEpisode->getDescription();
            if ($description && ! $episode->getDescription()) {
                $episode->setDescription($description);
            }

            // skip `subjects`
            // not part of RSS feed

            // default language to the podcast language if not already set
            if (null === $episode->getLanguage() && $this->podcast->getLanguage()) {
                $episode->setLanguage($this->podcast->getLanguage());
            }

            // skip `permissions`
            // not part of RSS feed

            $this->em->persist($episode);
            $this->em->flush();

            $medias = $rssEpisode->getMedias();
            foreach ($medias as $media) {
                $this->addEpisodeMediaFetchRequest($episode, $media->getUri());
            }

            $artwork = $rssEpisode->getArtwork();
            if ($artwork) {
                $this->addEpisodeMediaFetchRequest($episode, $artwork->getUri());
            }
        }
    }

    /**
     * download media for podcast & episodes concurrently.
     */
    public function processMedia() : void {
        $this->updateMessage('Downloading Media Files');

        $urls = array_keys($this->mediaRequests);
        /*
         * 1 step is the previous metadata processing for podcast/seasons/episodes
         * 2 steps for each file (download & server processing).
         *      - Failed downloads automatically count as the second step completed
         *
         */

        $this->totalSteps = (count($urls) * 2) + 1;
        $stepsCompletedCount = 1;
        $this->updateProgress($stepsCompletedCount);
        $requests = function () {
            foreach ($this->mediaRequests as $url => $data) {
                yield function () use ($url, $data) {
                    return $this->client->getAsync($url, [
                        'sink' => $data['tempFilePath'],
                        'headers' => [
                            'Accept-Encoding' => 'gzip, deflate, br',
                        ],
                    ]);
                };
            }
        };

        $successRequests = [];
        $completed = 0;
        $pool = new Pool($this->client, $requests(), [
            'concurrency' => 100,
            'fulfilled' => function (Response $response, $index) use ($urls, &$successRequests, &$completed, &$stepsCompletedCount) : void {
                $url = $urls[$index];
                $successRequests[$url] = $this->mediaRequests[$url];
                $this->output->writeln("Download Completed for {$url}");
                $this->updateProgress(++$stepsCompletedCount);

                $completed++;
                $total = count($urls);
                $this->updateMessage("Downloading Media Files ({$completed}/{$total})");
            },
            'rejected' => function (Exception $reason, $index) use ($urls, &$completed, &$stepsCompletedCount) : void {
                $url = $urls[$index];
                $this->output->writeln("Download client error for {$url} with reason: {$reason->getMessage()}");
                $this->updateProgress($stepsCompletedCount += 2);

                $completed++;
                $total = count($urls);
                $this->updateMessage("Downloading Media Files ({$completed}/{$total})");
            },
        ]);
        $pool->promise()->wait();

        $this->updateMessage('Final server processing.');
        $completed = 0;
        foreach ($successRequests as $url => $data) {
            // deconstruct mediaData
            list(
                'entities' => $entities,
                'filename' => $filename,
                'tempFilePath' => $tempFilePath,
            ) = $data;

            $mimetype = mime_content_type($tempFilePath);
            $checksum = md5_file($tempFilePath);
            foreach ($entities as $entity) {
                $upload = new UploadedFile($tempFilePath, $filename, $mimetype, null, true);

                if (str_starts_with($mimetype, 'image/') && ($entity instanceof Podcast || $entity instanceof Episode)) {
                    $image = $entity->getImageByChecksum($checksum);
                    if (null === $image) {
                        $this->addImageToEntity($entity, $upload, $url);
                    } elseif (null === $image->getSourceUrl()) {
                        $image->setSourceUrl($url);
                        $this->em->persist($image);
                        $this->em->flush();
                    }
                } elseif (str_starts_with($mimetype, 'audio/') && $entity instanceof Episode) {
                    $audio = $entity->getAudioByChecksum($checksum);
                    if (null === $audio) {
                        $this->addAudioToEntity($entity, $upload, $url);
                    } elseif (null === $audio->getSourceUrl()) {
                        $audio->setSourceUrl($url);
                        $this->em->persist($audio);
                        $this->em->flush();
                    }
                } elseif ('application/pdf' === $mimetype && $entity instanceof Episode) {
                    $pdf = $entity->getPdfByChecksum($checksum);
                    if (null === $pdf) {
                        $this->addPdfToEntity($entity, $upload, $url);
                    } elseif (null === $pdf->getSourceUrl()) {
                        $pdf->setSourceUrl($url);
                        $this->em->persist($pdf);
                        $this->em->flush();
                    }
                }
            }
            $this->output->writeln("Finished server side processing of {$url}");
            $this->updateProgress(++$stepsCompletedCount);

            $completed++;
            $total = count($successRequests);
            $this->updateMessage("Final server processing ({$completed}/{$total})");
        }
    }
}
