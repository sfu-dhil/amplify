<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Entity\Episode;
use App\Entity\Podcast;
use App\Entity\Season;
use App\Repository\PodcastRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Nines\MediaBundle\Entity\StoredFileInterface;
use Nines\UtilBundle\TestCase\CommandTestCase;

class ImportPodcastCommandTest extends CommandTestCase {
    private string $rssFeed = <<<'EOD'
        <?xml version="1.0" encoding="UTF-8" standalone="no"?>
        <rss xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:georss="http://www.georss.org/georss" xmlns:googleplay="http://www.google.com/schemas/play-podcasts/1.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:media="http://search.yahoo.com/mrss/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" version="2.0">
            <channel>
                <title>title stub</title>
                <atom:link href="https://podcast.com/feed/" rel="self" type="application/rss+xml"/>
                <link>https://podcast.com</link>
                <description>description stub</description>
                <lastBuildDate>Wed, 12 Oct 2022 21:44:55 +0000</lastBuildDate>
                <language>en</language>
                <sy:updatePeriod>hourly	</sy:updatePeriod>
                <sy:updateFrequency>1	</sy:updateFrequency>
                <generator>http://wordpress.com/</generator>
                <atom:link href="https://podcast.com/osd.xml" rel="search" title="title stub" type="application/opensearchdescription+xml"/>
                <atom:link href="https://podcast.com/?pushpress=hub" rel="hub"/>
                <itunes:subtitle>itunes subtitle stub</itunes:subtitle>
                <itunes:summary>itunes summary stub</itunes:summary>
                <googleplay:description>google description stub</googleplay:description>
                <itunes:author>itunes author stub</itunes:author>
                <googleplay:author>google author stub</googleplay:author>
                <copyright>copyright stub</copyright>
                <itunes:explicit>yes</itunes:explicit>
                <googleplay:explicit>yes</googleplay:explicit>
                <itunes:image href="https://podcast.com/image.jpg"/>
                <itunes:keywords>itunes,keywords,stub</itunes:keywords>
                <itunes:category text="Society &amp; Culture">
                    <itunes:category text="Philosophy"/>
                </itunes:category>
                <itunes:owner>
                    <itunes:email>itunes.email@stub.com</itunes:email>
                    <itunes:name>itune name stub</itunes:name>
                </itunes:owner>
                <item>
                    <title>episode 2 title stub</title>
                    <link>https://podcast.com/2/</link>
                    <comments>https://podcast.com/2/#respond</comments>
                    <pubDate>Wed, 13 Oct 2022 21:43:07 +0000</pubDate>
                    <category>Podcast</category>
                    <guid isPermaLink="false">https://podcast.com/?p=2</guid>
                    <description>episode 2 description stub</description>
                    <content:encoded>episode 2 content stub</content:encoded>
                    <wfw:commentRss>https://podcast.com/2/</wfw:commentRss>
                    <slash:comments>0</slash:comments>
                    <enclosure length="134763877" type="audio/mpeg" url="https://podcast.com/2/audio.mp3"/>
                    <itunes:duration>4209</itunes:duration>
                    <itunes:author>episode 2 itunes author stub</itunes:author>
                    <googleplay:author>episode 2 google author stub</googleplay:author>
                    <itunes:explicit>yes</itunes:explicit>
                    <googleplay:explicit>yes</googleplay:explicit>
                    <itunes:summary>episode 2 itunes summary stub</itunes:summary>
                    <googleplay:description>episode 2 google description stub</googleplay:description>
                    <itunes:subtitle>episode 2 itunes subtitle stub</itunes:subtitle>
                    <media:content medium="image" url="https://podcast.com/gravatar?s=96&amp;d=identicon&amp;r=G">
                        <media:title type="html">title stub</media:title>
                    </media:content>
                    <media:content medium="image" url="https://podcast.com/2/image.jpg?w=1024"/>
                    <media:content url="https://podcast.com/2/transcript.pdf"/>
                    <dc:creator>creator stub</dc:creator>
                    <itunes:keywords>itunes,keywords,stub</itunes:keywords>
                </item>
                <item>
                    <title>episode 1 title stub</title>
                    <link>https://podcast.com/1/</link>
                    <comments>https://podcast.com/1/#respond</comments>
                    <pubDate>Wed, 12 Oct 2022 21:43:07 +0000</pubDate>
                    <category>Podcast</category>
                    <guid isPermaLink="false">https://podcast.com/?p=1</guid>
                    <description>episode 1 description stub</description>
                    <content:encoded>episode 1 content stub</content:encoded>
                    <wfw:commentRss>https://podcast.com/1/</wfw:commentRss>
                    <slash:comments>0</slash:comments>
                    <enclosure length="134763877" type="audio/mpeg" url="https://podcast.com/1/audio.mp3"/>
                    <itunes:duration>4210</itunes:duration>
                    <itunes:author>episode 1 itunes author stub</itunes:author>
                    <googleplay:author>episode 1 google author stub</googleplay:author>
                    <itunes:explicit>yes</itunes:explicit>
                    <googleplay:explicit>yes</googleplay:explicit>
                    <itunes:summary>episode 1 itunes summary stub</itunes:summary>
                    <googleplay:description>episode 1 google description stub</googleplay:description>
                    <itunes:subtitle>episode 1 itunes subtitle stub</itunes:subtitle>
                    <media:thumbnail url="https://podcast.com/1/image.jpg?w=256"/>
                    <media:content medium="image" url="https://podcast.com/gravatar?s=96&amp;d=identicon&amp;r=G" />
                    <media:content medium="image" url="https://podcast.com/1/image.jpg?w=1024"/>
                    <media:content medium="audio" url="https://podcast.com/1/audio.mp3"/>
                    <dc:creator>creator stub</dc:creator>
                    <itunes:keywords>itunes,keywords,stub</itunes:keywords>
                </item>
            </channel>
        </rss>
        EOD;

    public function podcastStub(Podcast $podcast) : array {
        $images = [];
        foreach ($podcast->getImages() as $image) {
            $images[] = $this->fileStub($image);
        }
        $seasons = [];
        foreach ($podcast->getSeasons() as $season) {
            $seasons[] = $this->seasonStub($season);
        }
        $episodes = [];
        foreach ($podcast->getEpisodes() as $episode) {
            $episodes[] = $this->episodeStub($episode);
        }

        return [
            'title' => $podcast->getTitle(),
            'subTitle' => $podcast->getSubTitle(),
            'explicit' => $podcast->getExplicit(),
            'description' => $podcast->getDescription(),
            'copyright' => $podcast->getCopyright(),
            'website' => $podcast->getWebsite(),
            'languageCode' => $podcast->getLanguageCode(),
            'categories' => $podcast->getCategories(),
            'images' => $images,
            'seasons' => $seasons,
            'episodes' => $episodes,
        ];
    }

    public function seasonStub(Season $season) : array {
        return [
            'number' => $season->getNumber(),
            'title' => $season->getTitle(),
            'subTitle' => $season->getSubTitle(),
            'description' => $season->getDescription(),
        ];
    }

    public function episodeStub(Episode $episode) : array {
        $audios = [];
        foreach ($episode->getAudios() as $audio) {
            $audios[] = $this->fileStub($audio);
        }
        $images = [];
        foreach ($episode->getImages() as $image) {
            $images[] = $this->fileStub($image);
        }
        $pdfs = [];
        foreach ($episode->getPdfs() as $pdf) {
            $pdfs[] = $this->fileStub($pdf);
        }

        return [
            'guid' => $episode->getGuid(),
            'number' => $episode->getNumber(),
            'date' => $episode->getDate()->format('c'),
            'runTime' => $episode->getRunTime(),
            'title' => $episode->getTitle(),
            'subTitle' => $episode->getSubTitle(),
            'explicit' => $episode->getExplicit(),
            'description' => $episode->getDescription(),
            'season' => $this->seasonStub($episode->getSeason()),
            'audios' => $audios,
            'images' => $images,
            'pdfs' => $pdfs,
        ];
    }

    public function fileStub(StoredFileInterface $file) : array {
        return [
            'originalName' => $file->getOriginalName(),
            'mimeType' => $file->getMimeType(),
            'checksum' => $file->getChecksum(),
            'sourceUrl' => $file->getSourceUrl(),
        ];
    }

    public function testExecuteExistingPodcast() : void {
        $podcastRepository = self::getContainer()->get(PodcastRepository::class);
        $podcast = $podcastRepository->find(4);

        $initImage = [
            'originalName' => '3632486652_b432f7b283_c.jpg',
            'mimeType' => 'image/jpeg',
            'checksum' => 'c009533aca4c9a309e85606dbc69f8ad',
            'sourceUrl' => null,
        ];
        $expectedSeasonStub = [
            'number' => 1,
            'title' => 'Season 1',
            'subTitle' => null,
            'description' => '',
        ];
        $sharedGravatarImage = [
            'originalName' => 'gravatar',
            'mimeType' => 'image/jpeg',
            'checksum' => '3642c573c4329edfeee928fd28951b27',
            'sourceUrl' => 'https://podcast.com/gravatar?s=96&amp;d=identicon&amp;r=G',
        ];
        $expectedPodcast = [
            'title' => 'Title 3',
            'subTitle' => 'SubTitle 3',
            'explicit' => false,
            'description' => '<p>This is paragraph 3</p>',
            'copyright' => '<p>This is paragraph 3</p>',
            'website' => '<p>This is paragraph 3</p>',
            'languageCode' => 'en',
            'categories' => [
                'Society & Culture',
                'Society & Culture - Philosophy',
            ],
            'images' => [
                $initImage,
                [
                    'originalName' => 'image.jpg',
                    'mimeType' => 'image/jpeg',
                    'checksum' => 'e0c06454648674ebc21315efba645060',
                    'sourceUrl' => 'https://podcast.com/image.jpg',
                ],
            ],
            'seasons' => [$expectedSeasonStub],
            'episodes' => [
                [
                    'guid' => 'https://podcast.com/?p=1',
                    'number' => 1.0,
                    'date' => '2022-10-12T14:43:07-07:00',
                    'runTime' => '01:10:10',
                    'title' => 'episode 1 title stub',
                    'subTitle' => 'episode 1 itunes subtitle stub',
                    'explicit' => true,
                    'description' => 'episode 1 content stub',
                    'season' => $expectedSeasonStub,
                    'audios' => [
                        [
                            'originalName' => 'audio.mp3',
                            'mimeType' => 'audio/mpeg',
                            'checksum' => '3867b514cbaeb2fa5503da76cb9d1328',
                            'sourceUrl' => 'https://podcast.com/1/audio.mp3',
                        ],
                    ],
                    'images' => [
                        $sharedGravatarImage,
                        [
                            'originalName' => 'image.jpg',
                            'mimeType' => 'image/jpeg',
                            'checksum' => '1d86f8f0a2d4f99c2eaaab1ce9fb3b08',
                            'sourceUrl' => 'https://podcast.com/1/image.jpg?w=1024',
                        ],
                    ],
                    'pdfs' => [],
                ],
                [
                    'guid' => 'https://podcast.com/?p=2',
                    'number' => 2.0,
                    'date' => '2022-10-13T14:43:07-07:00',
                    'runTime' => '01:10:09',
                    'title' => 'episode 2 title stub',
                    'subTitle' => 'episode 2 itunes subtitle stub',
                    'explicit' => true,
                    'description' => 'episode 2 content stub',
                    'season' => $expectedSeasonStub,
                    'audios' => [
                        [
                            'originalName' => 'audio.mp3',
                            'mimeType' => 'audio/mpeg',
                            'checksum' => '3867b514cbaeb2fa5503da76cb9d1328',
                            'sourceUrl' => 'https://podcast.com/2/audio.mp3',
                        ],
                    ],
                    'images' => [
                        $sharedGravatarImage,
                        [
                            'originalName' => 'image.jpg',
                            'mimeType' => 'image/jpeg',
                            'checksum' => '1d86f8f0a2d4f99c2eaaab1ce9fb3b08',
                            'sourceUrl' => 'https://podcast.com/2/image.jpg?w=1024',
                        ],
                    ],
                    'pdfs' => [
                        [
                            'originalName' => 'transcript.pdf',
                            'mimeType' => 'application/pdf',
                            'checksum' => '4cd66a41f4b0e3f44a9c0739b87f1943',
                            'sourceUrl' => 'https://podcast.com/2/transcript.pdf',
                        ],
                    ],
                ],
            ],
        ];

        // init state
        $this->assertEquals($this->podcastStub($podcast), [
            'title' => 'Title 3',
            'subTitle' => 'SubTitle 3',
            'explicit' => false,
            'description' => '<p>This is paragraph 3</p>',
            'copyright' => '<p>This is paragraph 3</p>',
            'website' => '<p>This is paragraph 3</p>',
            'languageCode' => null,
            'categories' => [],
            'images' => [$initImage],
            'seasons' => [],
            'episodes' => [],
        ]);

        $mock = new MockHandler();
        $mock->append(
            new Response(200, [], $this->rssFeed),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/image/28213926366_4430448ff7_c.jpg')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/image/30191231240_4010f114ba_c.jpg')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/image/33519978964_c025c0da71_c.jpg')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/audio/94934__bletort__taegum-1.mp3')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/image/33519978964_c025c0da71_c.jpg')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/pdf/holmes_1.pdf')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/audio/94934__bletort__taegum-1.mp3')),
        );

        $handlerStack = HandlerStack::create($mock);
        static::$kernel->getContainer()->set(Client::class, new Client(['handler' => $handlerStack]));

        $this->execute('app:import:podcast', [
            'url' => 'https://rss.com/3',
            'podcastId' => 4,
        ]);
        $podcast = $podcastRepository->find(4);
        $this->assertEquals($this->podcastStub($podcast), $expectedPodcast);

        // running the import again will not add duplicate resources
        $mock->reset();
        $mock->append(
            new Response(200, [], $this->rssFeed),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/image/28213926366_4430448ff7_c.jpg')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/image/30191231240_4010f114ba_c.jpg')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/image/33519978964_c025c0da71_c.jpg')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/audio/94934__bletort__taegum-1.mp3')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/image/33519978964_c025c0da71_c.jpg')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/pdf/holmes_1.pdf')),
            new Response(200, [], file_get_contents(dirname(__FILE__, 2) . '/data/audio/94934__bletort__taegum-1.mp3')),
        );
        $this->execute('app:import:podcast', [
            'url' => 'https://rss.com/3',
            'podcastId' => 4,
        ]);
        $podcast = $podcastRepository->find(4);
        $this->assertEquals($this->podcastStub($podcast), $expectedPodcast);
    }

    protected function setUp() : void {
        parent::setUp();
    }
}
