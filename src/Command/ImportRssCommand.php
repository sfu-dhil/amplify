<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Category;
use App\Entity\Episode;
use App\Entity\Language;
use App\Entity\Podcast;
use App\Entity\Season;
use App\Repository\CategoryRepository;
use App\Repository\LanguageRepository;
use App\Repository\PodcastRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\HttpClient\TraceableHttpClient;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:import:rss')]
class ImportRssCommand extends Command {
    private EntityManagerInterface $em;

    private TraceableHttpClient $client;

    private ?OutputInterface $output = null;

    private ?RssParser $parser;

    private ?RssPodcast $rssPodcast = null;

    private ?RssEpisodes $rssEpisodes = null;

    private ?PodcastRepository $podcastRepository = null;

    private ?LanguageRepository $languageRepository = null;

    private ?CategoryRepository $categoryRepository = null;

    private ?AudioManager $audioManager = null;

    private ?ImageManager $imageManager = null;

    private ?PdfManager $pdfManager = null;

    private Filesystem $filesystem;

    private ?Podcast $podcast = null;

    private array $seasons = [];

    private array $episodes = [];

    private array $mediaRequests = [];

    public function __construct(EntityManagerInterface $em, HttpClientInterface $client) {
        $this->em = $em;
        $this->client = $client;

        $this->parser = new RssParser();

        $this->filesystem = new Filesystem();

        parent::__construct(null);
    }

    private function cleanupTempFiles() : void {
        $this->output->writeln('Cleaning up ' . sys_get_temp_dir());
        $tempFiles = glob(sys_get_temp_dir() . "/import_podcast_{$this->podcast->getId()}_*");
        $this->filesystem->remove($tempFiles);
    }

    private function addPodcastMediaFetchRequest(Podcast $podcast, string $url) : void {
        // check if sourceUrl already exists for podcast
        if ($podcast->hasImageBySourceUrl($url)) {
            return;
        }

        if ( ! array_key_exists($url, $this->mediaRequests)) {
            $this->mediaRequests[$url] = [];
        }
        // make sure podcast is unique for resource
        if ( ! in_array($podcast, $this->mediaRequests[$url], true)) {
            $this->mediaRequests[$url][] = $podcast;
        }
    }

    private function addEpisodeMediaFetchRequest(Episode $episode, string $url) : void {
        // check if sourceUrl already exists for episode
        if ($episode->hasAudioBySourceUrl($url) || $episode->hasImageBySourceUrl($url) || $episode->hasPdfBySourceUrl($url)) {
            return;
        }

        if ( ! array_key_exists($url, $this->mediaRequests)) {
            $this->mediaRequests[$url] = [];
        }
        // make sure episode is unique for resource
        if ( ! in_array($episode, $this->mediaRequests[$url], true)) {
            $this->mediaRequests[$url][] = $episode;
        }
    }

    private function addImageToEntity(ImageContainerInterface $entity, UploadedFile $upload, string $sourceUrl) : void {
        $image = new Image();
        $image->setFile($upload);
        $image->setPublic(true);
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
        $audio->setPublic(true);
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
        $pdf->setPublic(true);
        $pdf->setEntity($entity);
        $pdf->setDescription('');
        $pdf->setSourceUrl($sourceUrl);
        $pdf->prePersist();

        $this->em->persist($pdf);
        $entity->addPdf($pdf);
        $this->em->flush();
    }

    /**
     * @param int $default default season number
     */
    private function getSeasonNumber(RssEpisode $rssEpisode, int $default) : int {
        $seasonNumber = (int) $rssEpisode->getSeason();

        return 0 === $seasonNumber ? $default : $seasonNumber;
    }

    /**
     * @param int $default default episode number
     */
    private function getEpisodeNumber(RssEpisode $rssEpisode, int $default) : int {
        $episodeNumber = (int) $rssEpisode->getEpisodeNumber();

        return 0 === $episodeNumber ? $default : $episodeNumber;
    }

    /**
     * @return Category
     */
    private function getCategory(string $name) {
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

    protected function configure() : void {
        $this->setDescription('Import data');
        $this
            ->addArgument(
                'podcastId',
                InputArgument::REQUIRED,
                'ID of podcast.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $this->output = $output;
        $this->podcast = $this->podcastRepository->find($input->getArgument('podcastId'));

        if ( ! $this->podcast) {
            $this->output->writeln('No podcast found');

            return 0;
        }

        $url = $this->podcast->getRss();
        if ( ! $url) {
            $this->output->writeln('No RSS url found');

            return 0;
        }
        $this->output->writeln("Fetching RSS feed from {$url}");
        $response = $this->client->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            $this->output->writeln("Could not read RSS url. Code {$response->getStatusCode()} Message: {$response->getContent()}");

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

        $this->rssPodcast = $this->parser->setContent($response->getContent())->run();
        $this->rssEpisodes = $this->rssPodcast->getEpisodes();

        $this->output->writeln('Import Started');
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
        $this->output->writeln("Import Complete ({$timeInMinutes} Minutes)");

        return 1;
    }

    /**
     * Update all retrievable Podcast fields
     * There aren't ways to update license and publisher
     * contributor could only be updated with 1 owner, 1 author, and possibly 1 editor.
     */
    public function processPodcast() : void {
        $this->output->writeln('Processing Podcast');

        $title = $this->rssPodcast->getTitle();
        if ($title) {
            $this->podcast->setTitle(mb_strimwidth($title, 0, 252, '...'));
        }

        $subtitle = $this->rssPodcast->getSubtitle();
        if ($subtitle) {
            $this->podcast->setSubTitle(mb_strimwidth($subtitle, 0, 252, '...'));
        }

        $explicit = $this->rssPodcast->getExplicit();
        if ($explicit) {
            $this->podcast->setExplicit('yes' === $explicit);
        }

        $description = $this->rssPodcast->getDescription();
        if ($description) {
            $this->podcast->setDescription($description);
        }

        $copyright = $this->rssPodcast->getCopyright();
        if ($copyright) {
            $this->podcast->setCopyright($copyright);
        }

        $website = $this->rssPodcast->getLink();
        if ($website) {
            $this->podcast->setWebsite($website);
        }

        // skip `rss`
        // since we need it to import in the first place

        // skip `license`
        // doesn't seem to be part of RSS feeds

        $artwork = $this->rssPodcast->getArtwork();
        if ($artwork) {
            $this->addPodcastMediaFetchRequest($this->podcast, $artwork->getUri());
        }

        $image = $this->rssPodcast->getImage();
        if ($image) {
            $this->addPodcastMediaFetchRequest($this->podcast, $image->getUri());
        }

        $languageCode = $this->rssPodcast->getLanguage();
        if ($languageCode) {
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

        // include `contributor` + `contributor_role`?
        // we can get author, owner at the podcast level but nothing else
    }

    /**
     * Create all missing seasons
     * Podcasts RSS feeds have limited Season information so at most we can generate missing seasons
     * with extremely limited populated fields.
     */
    public function processSeasons() : void {
        $this->output->writeln('Processing Seasons');

        foreach ($this->rssEpisodes as $rssEpisode) {
            $seasonNumber = $this->getSeasonNumber($rssEpisode, 1);
            $season = $this->seasons[$seasonNumber] ?? null;

            if (null === $season) {
                $this->output->writeln("Generating stub for Season {$seasonNumber}");
                $season = new Season();
                $season->setNumber($seasonNumber);
                $season->setPreserved(false);
                $season->setTitle("{$this->podcast->getTitle()} Season {$seasonNumber}");
                $season->setSubTitle(null);
                $season->setDescription('');
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
        $this->output->writeln('Processing Episodes');
        $totalEpisodes = count($this->rssEpisodes);

        foreach ($this->rssEpisodes as $index => $rssEpisode) {
            $guid = $rssEpisode->getGuid();
            $this->output->writeln("Processing Episode guid {$guid}");
            $episode = $this->episodes[$guid] ?? null;

            if (null === $episode) {
                $episode = new Episode();
                $episode->setGuid($guid);
                $episode->setPreserved(false);

                $episode->setPodcast($this->podcast);
                $this->podcast->addEpisode($episode);
                $this->episodes[$guid] = $episode;
            }
            $season = $this->seasons[$this->getSeasonNumber($rssEpisode, 1)];
            $episode->setSeason($season);

            $episode->setNumber($this->getEpisodeNumber($rssEpisode, $totalEpisodes - $index));
            $this->output->writeln("- Season {$episode->getSeason()->getNumber()} Episode {$episode->getNumber()}");

            $publishedDate = $rssEpisode->getPublishedDate();
            if ($publishedDate) {
                $episode->setDate($publishedDate);
            }

            $duration = $rssEpisode->getDuration();
            if ($duration) {
                $episode->setRunTime(gmdate('H:i:s', (int) $duration));
            }

            $title = $rssEpisode->getTitle();
            if ($title) {
                $episode->setTitle(mb_strimwidth($title, 0, 252, '...'));
            }

            $subtitle = $rssEpisode->getSubtitle();
            if ($subtitle) {
                $episode->setSubTitle(mb_strimwidth($subtitle, 0, 252, '...'));
            }

            // skip `bibliography`
            // not part of RSS feed

            // skip `transcript`
            // not part of RSS feed

            $description = $rssEpisode->getDescription();
            if ($description) {
                $episode->setDescription($description);
            }

            // skip `subjects`
            // not part of RSS feed

            // default language to the podcast language if not already set
            if (null === $episode->getLanguage()) {
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
        $this->output->writeln('Processing Media Files');

        // download in batches of 100 files to prevent memory errors
        foreach (array_chunk($this->mediaRequests, 100, true) as $mediaRequestChunk) {
            $responses = [];

            // start batch of requests
            foreach ($mediaRequestChunk as $url => $entities) {
                $filename = basename(parse_url($url, PHP_URL_PATH));
                $tempFilePath = $this->filesystem->tempnam(sys_get_temp_dir(), "import_podcast_{$this->podcast->getId()}_") . "_{$filename}";

                $responses[] = $this->client->request('GET', $url, [
                    'user_data' => [
                        'entities' => $entities,
                        'filename' => $filename,
                        'tempFilePath' => $tempFilePath,
                    ],
                ]);
            }

            // stream chunks for batch of requests
            foreach ($this->client->stream($responses) as $response => $chunk) {
                // handle status or timeout errors immediately
                if ($chunk->isFirst()) {
                    if (200 !== $response->getStatusCode()) {
                        $response->cancel();
                        $url = $response->getInfo('url');
                        $this->output->writeln("Download client error {$response->getStatusCode()} {$url}");

                        continue;
                    }
                } elseif ($chunk->isTimeout()) {
                    $this->output->writeln("Download Timeout {$url}");
                    $response->cancel();

                    continue;
                }

                // deconstruct the user_data
                list(
                    'entities' => $entities,
                    'filename' => $filename,
                    'tempFilePath' => $tempFilePath,
                ) = $response->getInfo('user_data');
                $url = $response->getInfo('url');

                $entitleList = '';
                foreach ($entities as $entity) {
                    $entityName = ($entity instanceof Podcast) ? 'Podcast' : 'Episode';
                    $entitleList .= "{$entityName}:{$entity->getId()} ";
                }

                try {
                    if ($chunk->isFirst()) {
                        $this->output->writeln("Download started {$url} for Entities: {$entitleList}");
                        $this->filesystem->touch($tempFilePath);
                    }

                    $this->filesystem->appendToFile($tempFilePath, $chunk->getContent());
                    if ($chunk->isLast()) {
                        $this->output->writeln("Download Completed {$url} for Entities: {$entitleList}");

                        $mimetype = mime_content_type($tempFilePath);
                        $checksum = md5_file($tempFilePath);
                        foreach ($entities as $index => $entity) {
                            $upload = new UploadedFile($tempFilePath, $filename, $mimetype, null, true);
                            if ($entity instanceof Podcast) {
                                if (str_starts_with($mimetype, 'image/') && ! $entity->hasImageByChecksum($checksum)) {
                                    $this->addImageToEntity($entity, $upload, $url);
                                }
                            } elseif ($entity instanceof Episode) {
                                if (str_starts_with($mimetype, 'audio/') && ! $entity->hasAudioByChecksum($checksum)) {
                                    $this->addAudioToEntity($entity, $upload, $url);
                                } elseif (str_starts_with($mimetype, 'image/') && ! $entity->hasImageByChecksum($checksum)) {
                                    $this->addImageToEntity($entity, $upload, $url);
                                } elseif ('application/pdf' === $mimetype && ! $entity->hasPdfByChecksum($checksum)) {
                                    $this->addPdfToEntity($entity, $upload, $url);
                                }
                            }
                        }
                    }
                } catch (TransportExceptionInterface $e) {
                    $this->output->writeln("Download transport error {$url} for Entities: {$entitleList}\nError: {$e->getMessage()}\n");
                }
            }
        }
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setPodcastRepository(PodcastRepository $podcastRepository) : void {
        $this->podcastRepository = $podcastRepository;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setLanguageRepository(LanguageRepository $languageRepository) : void {
        $this->languageRepository = $languageRepository;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setCategoryRepository(CategoryRepository $categoryRepository) : void {
        $this->categoryRepository = $categoryRepository;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setAudioManager(AudioManager $audioManager) : void {
        $this->audioManager = $audioManager;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setImageManager(ImageManager $imageManager) : void {
        $this->imageManager = $imageManager;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setPdfManager(PdfManager $pdfManager) : void {
        $this->pdfManager = $pdfManager;
    }
}
