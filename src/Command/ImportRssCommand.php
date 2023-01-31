<?php

declare(strict_types=1);

/*
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

/** NOTE: Work in progress  */

namespace App\Command;

use App\Entity\Podcast;
use App\Entity\Season;
use App\Entity\Episode;
use App\Util\TmpFile;
use App\Repository\PodcastRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use FeedIo\FeedIo;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Service\ImageManager;

class ImportRssCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @type Feedio\Feedio
     */
    private $feedio;

    private ?SeasonRepository $seasonRepository = null;
    private ?PodcastRepository $podcastRepository = null;
    private ?EpisodeRepository $episodeRepository = null;
    private ?ImageManager $imageManager = null;


    protected Crawler $crawler;
    protected $client;
    private $podcast;
    private $seasons;
    /**
     * @var Feed | null
     */
    private $feed;
    protected static $defaultName = 'app:import:rss';

    public function __construct(EntityManagerInterface $em, FeedIo $feedio)
    {
        $this->em = $em;
        $this->feedio = $feedio;
        $this->feed = null;
        $this->podcast = null;
        $this->crawler = new Crawler();
        $this->client = HttpClient::create();
        $this->seasons = [];
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->setDescription('Import data');
        $this
            ->addArgument(
                'podcastId',
                InputArgument::REQUIRED,
                'ID of podcast.'
            );
        $this
            ->addOption(
                'overwrite',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Fields to overwrite: Valid options: description, image',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->podcast = $this->podcastRepository->find($input->getArgument('podcastId'));
        if ($input->getOption('overwrite')){
            // Check for valid options.
        }
        if (! $this->podcast){
            writeln('No podcast found');
            return 0;
        }
        $url = $this->podcast->getRss();

        if (! $url){
            writeln('No urls found');
            return 0;
        }
        // now fetch its (fresh) content
        /**
         * @var Feed
         */
       // $this->feed = $this->feedio->read($url)->getFeed();
      //  $this->processPodcast();
        return 1;
    }


    public function processPodcast(){
        $this->setPodcastImage();
        $this->setPodcastDescription();
        // $this->testAudio();
        //$this->processPodcast();
        //$this->processSeasons();
        $this->processEpisodes();
        // $this->processSeasons();
        // $this->processEpisodes();
        // var_dump($this->podcast);
    }


    public function setPodcastImage(){
        if ($this->feed->getLogo()){
            if (count($this->podcast->getImages()) > 0){
                $this->echo("Skipping image, since one is already added...");
                return;
            }
            $this->addImageToEntity($this->feed->getLogo(), $this->podcast);
        }
    }

    public function setPodcastDescription(){
        if ($this->feed->getDescription()){
            if ($this->podcast->getDescription()){
                $this->echo('Skipping description');
                return;
            }
            $this->podcast->setDescription($this->feed->getDescription());
        }
    }




    /**
     * TODO: Process the feed and aggregate seasons
     * @return void
     */
    public function processSeasons(){
        foreach ($this->feed as $item){
            $season = $this->getSeasonFromEpisode($item);
            if ($season && !in_array($season, $this->seasons)){
                $this->seasons[$season] = null;
            }
        }
    }

    /**
     * @var Feed\Item
     * @return void
     */
    public function processEpisodes(){
        $count = count($this->feed);
        foreach($this->feed as $pos=>$item){
            $episodeNumber = $this->getNumberFromEpisode($item);
            $seasonNumber = $this->getSeasonFromEpisode($item);
            $number = ($episodeNumber > 0 ? $episodeNumber : ($count - $pos));
            $episode = new Episode();
            $episode->setTitle($item->getTitle());
            $episode->setDescription($item->getContent());
            $episode->setDate($item->getLastModified());
            $episode->setNumber($number);
            $episode->setRunTime(gmdate('H:i:s', intval($item->getValue('itunes:duration'))));
            $episode->setPodcast($this->podcast);
            foreach($item->getMedias() as $media){
                $type = $media->getType();
                if ($type === 'audio/mpeg'){
                    //$episode = $this->addAudioToEntity($media->getUrl(), $episode);
                }
            }
        }
    }

    /**
     * @param Feed\Item $item
     * @return int
     */
    public function getNumberFromEpisode(Feed\Item $item){
        return intval($item->getValue('itunes:episode'));
    }


    public function addAudioToEntity($audioUrl, $entity, $str = null){
        $description = 'Audio file';
        $upload = $this->upload($audioUrl);
        $audio = new Audio();
        $audio->setFile($upload);
        $audio->setPublic(true);
        $audio->setEntity($entity);
        $audio->setDescription($description);
        $audio->prePersist();
        $this->em->persist($audio);
        $entity->addAudio($audio);
        return $entity;
    }

    public function addImageToEntity($imgUrl, $entity, $str = null){
        $description = 'Image for ' . $this->getEntityDescription($entity, $str);
        echo 'Writing ' . $description;
        $upload = $this->upload($imgUrl);
        $img = new Image();
        $img->setFile($upload);
        $img->setPublic(true);
        $img->setEntity($entity);
        $img->setDescription($description);
        $img->prePersist();
        $this->em->persist($img);
        $entity->addImage($img);
    }

    /**
     * @param $url
     * @return An uploaded file
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function upload($url, $filename = null){
        echo "Fetching {$url}\n";
        $response = $this->client->request('GET', $url);
        $tmpFile = new TmpFile($response->getContent());
        return new UploadedFile($tmpFile->getRealPath(), basename(pathinfo($url, PHP_URL_PATH)), $tmpFile->getMimeType(),null,null,true);
    }

    public function getEntityDescription($entity, $str = null){
        if ($str != null){
            return $str;
        }
        //TODO: PHP8 should be able to check implements
        if (method_exists($entity,'__toString')){
            return (string) $entity;
        }
        return get_class($entity) . ' ' . $entity->getId();
    }


    /* TODO: Remove Tests */


    public function testAudio(){
        $episode = $this->episodeRepository->find('19');
        $episode = $this->addAudioToEntity('https://secretfeministagenda.files.wordpress.com/2017/06/sfa-1-0.mp3', $episode);
        $this->em->persist($episode);
        $this->em->flush();
        $this->em->clear();
    }



    private function echo($msg){
        echo "{$msg}\n";
    }

    /**
     * @required
     */
    public function setSeasonRepository(SeasonRepository $seasonRepository) : void {
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @required
     */
    public function setEpisodeRepository(EpisodeRepository $episodeRepository) : void {
        $this->episodeRepository = $episodeRepository;
    }

    /**
     * @param Feed\Item $item
     * @return int|null
     */
    public function getSeasonFromEpisode(Feed\Item $item) : ?int {
        $season = $item->getValue('itunes:season');
        $int = intval($season);
        if (!$season){
            return null;
        }
        if ($int === 0){
            return null;
        }
        return $int;
    }

    /**
     * @required
     */
    public function setPodcastRepository(PodcastRepository $podcastRepository) : void {
        $this->podcastRepository = $podcastRepository;
    }

    public function setImageManager(ImageManager $imageManager) : void {
        $this->imageManager = $imageManager;
    }

}
