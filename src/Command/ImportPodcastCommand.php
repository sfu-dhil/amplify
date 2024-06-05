<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Episode;
use App\Entity\Import;
use App\Entity\Podcast;
use App\Entity\Season;
use App\Entity\Share;
use App\Repository\ImportRepository;
use App\Repository\PodcastRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Pdf;
use Nines\MediaBundle\Service\AudioManager;
use Nines\MediaBundle\Service\ImageManager;
use Nines\MediaBundle\Service\PdfManager;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Repository\UserRepository;
use SimplePie\Item as SimplePieItem;
use SimplePie\SimplePie;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Intl\Languages;

#[AsCommand(name: 'app:import:podcast')]
class ImportPodcastCommand extends Command {
    private const NS_GOOGLE_PLAY = 'http://www.google.com/schemas/play-podcasts/1.0';

    private const NS_PODCAST = 'https://podcastindex.org/namespace/1.0';

    public function __construct(
        private EntityManagerInterface $em,
        private PodcastRepository $podcastRepository,
        private ImportRepository $importRepository,
        private UserRepository $userRepository,
        private AudioManager $audioManager,
        private ImageManager $imageManager,
        private PdfManager $pdfManager,
        private Filesystem $filesystem,
        private Client $client,
        private HtmlSanitizerInterface $importContentSanitizer,
        private ?SimplePie $feed = null,
        private ?int $totalSteps = null,
        private ?string $rssUrl = null,
        private ?Podcast $podcast = null,
        private ?User $user = null,
        private ?Import $import = null,
        private ?OutputInterface $output = null,
        private array $seasons = [],
        private array $seasonEpisodeCounter = [],
        private array $episodes = [],
        private array $mediaRequests = [],
        private bool $isNew = false,
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

    private function getFeedTag(string $namespace, string $name) : ?array {
        $tags = $this->feed->get_channel_tags($namespace, $name);
        if ($tags && count($tags)) {
            return $tags[0];
        }

        return null;
    }

    private function getFeedTagValue(string $namespace, string $name) : ?string {
        $tag = $this->getFeedTag($namespace, $name);
        if ($tag && array_key_exists('data', $tag)) {
            return $this->feed->sanitize($tag['data'], SimplePie::CONSTRUCT_TEXT);
        }

        return null;
    }

    private function getItemTag(SimplePieItem $item, string $namespace, string $name) : ?array {
        $tags = $item->get_item_tags($namespace, $name);
        if ($tags && count($tags)) {
            return $tags[0];
        }

        return null;
    }

    private function getItemTagValue(SimplePieItem $item, string $namespace, string $name) : ?string {
        $tag = $this->getItemTag($item, $namespace, $name);
        if ($tag && array_key_exists('data', $tag)) {
            return $this->feed->sanitize($tag['data'], SimplePie::CONSTRUCT_TEXT);
        }

        return null;
    }

    /**
     * Update all retrievable Podcast fields
     * There aren't ways to update license and publisher
     * contributor could only be updated with 1 owner, 1 author, and possibly 1 editor.
     */
    private function processPodcast() : void {
        $this->updateMessage('Processing Podcast metadata');

        if (null === $this->podcast->getGuid() || '' === $this->podcast->getGuid()) {
            $guid = $this->getFeedTagValue(self::NS_PODCAST, 'guid');
            if ($guid) {
                $this->podcast->setGuid($guid);
            }
        }

        if (null === $this->podcast->getTitle() || '' === $this->podcast->getTitle()) {
            $title = $this->feed->get_title();
            if ($title) {
                $this->podcast->setTitle(mb_strimwidth(html_entity_decode($title), 0, 252, '...'));
            }
        }

        if (null === $this->podcast->getSubTitle() || '' === $this->podcast->getSubTitle()) {
            $subtitle = $this->getFeedTagValue(SimplePie::NAMESPACE_ITUNES, 'subtitle');
            if ($subtitle) {
                $this->podcast->setSubTitle(mb_strimwidth(html_entity_decode($subtitle), 0, 252, '...'));
            }
        }

        if (null === $this->podcast->getExplicit()) {
            $explicit = $this->getFeedTagValue(SimplePie::NAMESPACE_ITUNES, 'explicit') ?? $this->getFeedTagValue(self::NS_GOOGLE_PLAY, 'explicit');
            $this->podcast->setExplicit('yes' === $explicit);
        }

        if (null === $this->podcast->getDescription() || '' === $this->podcast->getDescription()) {
            $description = $this->feed->get_description();
            if ($description) {
                $this->podcast->setDescription($description);
            }
        }

        if (null === $this->podcast->getCopyright() || '' === $this->podcast->getCopyright()) {
            $copyright = $this->feed->get_copyright();
            if ($copyright) {
                $this->podcast->setCopyright($copyright);
            }
        }

        if (null === $this->podcast->getWebsite() || '' === $this->podcast->getWebsite()) {
            $website = $this->feed->get_link();
            if ($website) {
                $this->podcast->setWebsite($website);
            }
        }

        if (null === $this->podcast->getRss() || '' === $this->podcast->getRss()) {
            $this->podcast->setRss($this->rssUrl);
        }

        // skip `license`
        // doesn't seem to be part of RSS feeds

        if (null === $this->podcast->getLanguageCode() || '' === $this->podcast->getLanguageCode()) {
            $languageCode = $this->feed->get_language();
            if ($languageCode && Languages::exists($languageCode)) {
                $this->podcast->setLanguageCode(mb_substr($languageCode, 0, 2));
            }
        }

        // skip `publisher`
        // doesn't seem to be part of RSS feeds

        $categories = array_merge(
            $this->feed->get_channel_tags(SimplePie::NAMESPACE_ITUNES, 'category') ?? [],
            $this->feed->get_channel_tags(self::NS_GOOGLE_PLAY, 'category') ?? [],
        );
        if ($categories && count($categories)) {
            foreach ($categories as $categoryData) {
                $name = $this->feed->sanitize($categoryData['attribs']['']['text'], SimplePie::CONSTRUCT_TEXT);
                $this->podcast->addCategory(html_entity_decode($name));

                if (isset($categoryData['child']) && is_array($categoryData['child'])) {
                    foreach ($categoryData['child'][SimplePie::NAMESPACE_ITUNES]['category'] as $subCategoryData) {
                        $subName = $this->feed->sanitize($subCategoryData['attribs']['']['text'], SimplePie::CONSTRUCT_TEXT);
                        $this->podcast->addCategory(html_entity_decode("{$name} - {$subName}"));
                    }
                }
            }
        }

        $keywordsString = $this->getFeedTagValue(SimplePie::NAMESPACE_ITUNES, 'keywords');
        if ($keywordsString) {
            $keywords = explode(',', $keywordsString);
            foreach ($keywords as $keyword) {
                $this->podcast->addKeyword(trim($keyword));
            }
        }

        $this->em->persist($this->podcast);
        $this->em->flush();

        if ($this->import) {
            $this->podcast->addImport($this->import);
            $this->em->persist($this->import);
            $this->em->flush();
        }

        // add initial share if userId is present on new podcast
        if ($this->isNew && $this->user) {
            $share = new Share();
            $share->setUser($this->user);
            $this->podcast->addShare($share);
            $this->em->persist($share);
            $this->em->flush();
        }

        // include `contributor` + `contributor_role`?
        // we can get author, owner at the podcast level but nothing else

        $imageUrl = $this->feed->get_image_url();
        if ($imageUrl) {
            $this->addPodcastMediaFetchRequest($this->podcast, $imageUrl);
        }
    }

    /**
     * Create all missing seasons
     * Podcasts RSS feeds have limited Season information so at most we can generate missing seasons
     * with extremely limited populated fields.
     */
    private function processSeasons() : void {
        $this->updateMessage('Processing Season metadata');

        foreach ($this->feed->get_items() as $item) {
            $seasonNumber = (int) ($this->getItemTagValue($item, SimplePie::NAMESPACE_ITUNES, 'season') ?? 1);
            $season = $this->seasons[$seasonNumber] ?? null;
            $this->seasonEpisodeCounter[$seasonNumber] = [
                'full' => 0,
                'bonus' => 0,
                'trailer' => 0,
            ];

            if (null === $season) {
                $this->output->writeln("Generating stub for Season {$seasonNumber}");
                $season = new Season();
                $season->setNumber($seasonNumber);
                $season->setTitle("Season {$seasonNumber}");
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
    private function processEpisodes() : void {
        $this->updateMessage('Processing Episode metadata');
        foreach (array_reverse($this->feed->get_items()) as $item) {
            $guid = $item->get_id();
            $this->output->writeln("Processing Episode guid {$guid}");
            $episode = $this->episodes[$guid] ?? null;

            if (null === $episode) {
                $episode = new Episode();
                $episode->setGuid($guid);
                $episode->setTitle('');
                $episode->setDescription('');
                $episode->setRunTime('');

                $episode->setPodcast($this->podcast);
                $this->podcast->addEpisode($episode);
                $this->episodes[$guid] = $episode;
            }

            $episodeType = trim(mb_strtolower($this->getItemTagValue($item, SimplePie::NAMESPACE_ITUNES, 'episodeType') ?? ''));
            if ( ! in_array($episodeType, ['full', 'bonus', 'trailer'], true)) {
                $episodeType = 'full';
            }
            if (null === $episode->getEpisodeType()) {
                $episode->setEpisodeType($episodeType);
            }

            $seasonNumber = (int) ($this->getItemTagValue($item, SimplePie::NAMESPACE_ITUNES, 'season') ?? 1);
            $season = $this->seasons[$seasonNumber];
            if (null === $episode->getSeason()) {
                $episode->setSeason($season);
            }

            $episodeNumber = $this->getItemTagValue($item, SimplePie::NAMESPACE_ITUNES, 'episode');
            if (null !== $episodeNumber) {
                $this->seasonEpisodeCounter[$seasonNumber][$episodeType] = (int) $episodeNumber;
            } else {
                $episodeNumber = ++$this->seasonEpisodeCounter[$seasonNumber][$episodeType];
            }
            if (null === $episode->getNumber()) {
                $episode->setNumber((float) $episodeNumber);
            }

            $this->output->writeln("- Season {$episode->getSeason()->getNumber()} Episode {$episode->getNumber()}");

            if (null === $episode->getExplicit()) {
                $explicit = $this->getItemTagValue($item, SimplePie::NAMESPACE_ITUNES, 'explicit') ?? $this->getItemTagValue($item, self::NS_GOOGLE_PLAY, 'explicit');
                $episode->setExplicit('yes' === $explicit);
            }

            if (null === $episode->getDate() || '' === $episode->getDate()) {
                $publishedDate = $item->get_date('Y-m-d H:i:s');
                if ($publishedDate) {
                    $publishedDate = new DateTimeImmutable($publishedDate);
                    $episode->setDate($publishedDate);
                } else {
                    $publishedDate = new DateTimeImmutable('now');
                    $episode->setDate($publishedDate);
                }
            }

            if (null === $episode->getRunTime() || '' === $episode->getRunTime()) {
                $duration = $this->getItemTagValue($item, SimplePie::NAMESPACE_ITUNES, 'duration');
                if ($duration) {
                    if (preg_match('/^\\d{2}:\\d{2}:\\d{2}$/i', $duration)) {
                        $episode->setRunTime($duration);
                    } elseif (preg_match('/^\\d+$/i', $duration)) {
                        $episode->setRunTime(gmdate('H:i:s', (int) $duration));
                    }
                }
            }

            if (null === $episode->getTitle() || '' === $episode->getTitle()) {
                $title = $item->get_title();
                if ($title) {
                    $episode->setTitle(mb_strimwidth(html_entity_decode($title), 0, 252, '...'));
                }
            }

            if (null === $episode->getSubTitle() || '' === $episode->getSubTitle()) {
                $subtitle = $this->getItemTagValue($item, SimplePie::NAMESPACE_ITUNES, 'subtitle');
                if ($subtitle) {
                    $episode->setSubTitle(mb_strimwidth(html_entity_decode($subtitle), 0, 252, '...'));
                }
            }

            // skip `bibliography`
            // not part of RSS feed

            // Try to pull plaintext transcript if available and not already set
            $transcriptUrls = $item->get_item_tags(self::NS_PODCAST, 'transcript');
            if ($transcriptUrls && ! $episode->getTranscript()) {
                if ($transcriptUrls && count($transcriptUrls)) {
                    foreach ($transcriptUrls as $transcriptUrlData) {
                        $transcriptUrl = $transcriptUrlData['attribs']['']['url'];
                        $transcriptType = $transcriptUrlData['attribs']['']['type'];

                        if ('text/html' === $transcriptType) {
                            try {
                                $response = $this->client->get($transcriptUrl);
                            } catch (Exception $e) {
                                $this->output->writeln("Transcript error message: {$e->getMessage()}");
                            }
                            if (200 === $response->getStatusCode()) {
                                $episode->setTranscript($response->getBody()->getContents());
                            }
                        }
                    }
                }
            }

            if (null === $episode->getDescription() || '' === $episode->getDescription()) {
                $description = $item->get_content();
                if ($description) {
                    $episode->setDescription($this->importContentSanitizer->sanitize($description));
                }
            }

            $keywordsString = $this->getItemTagValue($item, SimplePie::NAMESPACE_ITUNES, 'keywords');
            if ($keywordsString) {
                $keywords = explode(',', $keywordsString);
                foreach ($keywords as $keyword) {
                    $episode->addKeyword(trim($keyword));
                }
            }

            // skip `permissions`
            // not part of RSS feed

            $this->em->persist($episode);
            $this->em->flush();

            $imageTags = $item->get_item_tags(SimplePie::NAMESPACE_ITUNES, 'image') ?? [];
            foreach ($imageTags as $imageTag) {
                if ($imageTag && array_key_exists('attribs', $imageTag) && array_key_exists('', $imageTag['attribs']) && array_key_exists('href', $imageTag['attribs'][''])) {
                    $this->addEpisodeMediaFetchRequest($episode, $imageTag['attribs']['']['href']);
                }
            }

            foreach ($item->get_enclosures() as $enclosure) {
                if ($enclosure && $enclosure->get_link()) {
                    $this->addEpisodeMediaFetchRequest($episode, $enclosure->get_link());
                }
            }
        }
    }

    /**
     * download media for podcast & episodes concurrently.
     */
    private function processMedia() : void {
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
            'concurrency' => 15,
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
                        $image = new Image();
                        $image->setFile($upload);
                        $image->setEntity($entity);
                        $image->setDescription('');
                        $image->setSourceUrl($url);
                        $image->prePersist();

                        $this->em->persist($image);
                        $entity->addImage($image);
                        $entity->updateStatus();
                        $this->em->flush();
                    } elseif (null === $image->getSourceUrl()) {
                        $image->setSourceUrl($url);
                        $this->em->persist($image);
                        $this->em->flush();
                    }
                } elseif ('application/pdf' === $mimetype && $entity instanceof Episode) {
                    $pdf = $entity->getPdfByChecksum($checksum);
                    if (null === $pdf) {
                        $pdf = new Pdf();
                        $pdf->setFile($upload);
                        $pdf->setEntity($entity);
                        $pdf->setDescription('');
                        $pdf->setSourceUrl($url);
                        $pdf->prePersist();

                        $this->em->persist($pdf);
                        $entity->addPdf($pdf);
                        $entity->updateStatus();
                        $this->em->flush();
                    } elseif (null === $pdf->getSourceUrl()) {
                        $pdf->setSourceUrl($url);
                        $this->em->persist($pdf);
                        $this->em->flush();
                    }
                // some audio files are application/octet-stream for whatever reason
                } elseif ((str_starts_with($mimetype, 'audio/') || 'application/octet-stream' === $mimetype) && $entity instanceof Episode) {
                    $audio = $entity->getAudioByChecksum($checksum);
                    if (null === $audio) {
                        $audio = new Audio();
                        $audio->setFile($upload);
                        $audio->setEntity($entity);
                        $audio->setDescription('');
                        $audio->setSourceUrl($url);
                        $audio->prePersist();

                        $this->em->persist($audio);
                        $entity->addAudio($audio);
                        $entity->updateStatus();
                        $this->em->flush();
                    } elseif (null === $audio->getSourceUrl()) {
                        $audio->setSourceUrl($url);
                        $this->em->persist($audio);
                        $this->em->flush();
                    }
                } else {
                    $this->output->writeln("Invalid Mimetype for mimetype: {$mimetype} filename: {$filename}");
                }
            }
            $this->output->writeln("Finished server side processing of {$url}");
            $this->updateProgress(++$stepsCompletedCount);

            $completed++;
            $total = count($successRequests);
            $this->updateMessage("Final server processing ({$completed}/{$total})");
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
        $this->addArgument(
            'userId',
            InputArgument::OPTIONAL,
            'ID of user.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $this->output = $output;

        $this->rssUrl = $input->getArgument('url');
        $podcastId = $input->getArgument('podcastId');
        $importId = $input->getArgument('importId');
        $userId = $input->getArgument('userId');

        if ( ! $this->rssUrl) {
            $this->output->writeln('No RSS url found.');

            return 0;
        }

        $this->podcast = $podcastId ? $this->podcastRepository->find($podcastId) : new Podcast();
        if ($podcastId && ! $this->podcast) {
            $this->output->writeln('No podcast found.');

            return 0;
        }
        $this->isNew = ! $podcastId;

        $this->import = $importId ? $this->importRepository->find($importId) : null;
        if ($importId && ! $this->import) {
            $this->output->writeln('No import found.');

            return 0;
        }

        $this->user = $userId ? $this->userRepository->find($userId) : null;
        if ($userId && ! $this->user) {
            $this->output->writeln('No user found.');

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

            $this->mediaRequests = [];
            $this->seasons = [];
            $this->seasonEpisodeCounter = [];
            foreach ($this->podcast->getSeasons() as $season) {
                if (null !== $season->getNumber()) {
                    $this->seasons[$season->getNumber()] = $season;
                }
            }
            $this->episodes = [];
            foreach ($this->podcast->getEpisodes() as $episode) {
                if ($episode->getGuid()) {
                    $this->episodes[$episode->getGuid()] = $episode;
                }
            }

            $this->feed = new SimplePie();
            $this->feed->set_raw_data($response->getBody()->getContents());
            $this->feed->init();

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
            $this->output->writeln("Message: {$e->getMessage()}");
            $this->output->writeln("Trace: {$e->getTraceAsString()}");
            $this->updateMessage('An unexpected error occurred.');

            return 0;
        }
    }
}
