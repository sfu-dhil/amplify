<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */


namespace App\Tests\Command;

use Nines\UtilBundle\TestCase\CommandTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\TraceableHttpClient;

use App\Repository\PodcastRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use App\Entity\Podcast;
use App\Entity\Language;
use App\Entity\Category;
use App\Entity\Season;
use App\Entity\Episode;
use Nines\MediaBundle\Entity\StoredFileInterface;

class ImportRssCommandTest extends CommandTestCase {
    private string $rssFeed = <<<EOD
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
            $images []= $this->fileStub($image);
        }
        $categories = [];
        foreach ($podcast->getCategories() as $category) {
            $categories []= $this->categoryStub($category);
        }
        $seasons = [];
        foreach ($podcast->getSeasons() as $season) {
            $seasons []= $this->seasonStub($season);
        }
        $episodes = [];
        foreach ($podcast->getEpisodes() as $episode) {
            $episodes []= $this->episodeStub($episode);
        }
        return [
            'title' => $podcast->getTitle(),
            'subTitle' => $podcast->getSubTitle(),
            'explicit' => $podcast->getExplicit(),
            'description' => $podcast->getDescription(),
            'copyright' => $podcast->getCopyright(),
            'website' => $podcast->getWebsite(),
            'language' => $this->languageStub($podcast->getLanguage()),
            'categories' => $categories,
            'images' => $images,
            'seasons' => $seasons,
            'episodes' => $episodes,
        ];
    }

    public function languageStub(?Language $language) : ?array {
        if (!$language) { return null; }
        return [
            'name' => $language->getName(),
        ];
    }

    public function categoryStub(Category $category) : array {
        return [
            'label' => $category->getLabel(),
        ];
    }

    public function seasonStub(Season $season) : array {
        return [
            'number' => $season->getNumber(),
            'title' => $season->getTitle(),
            'subTitle' => $season->getSubTitle(),
            'preserved' => $season->getPreserved(),
            'description' => $season->getDescription(),
        ];
    }

    public function episodeStub(Episode $episode) : array {
        $audios = [];
        foreach ($episode->getAudios() as $audio) {
            $audios []= $this->fileStub($audio);
        }
        $images = [];
        foreach ($episode->getImages() as $image) {
            $images []= $this->fileStub($image);
        }
        $pdfs = [];
        foreach ($episode->getPdfs() as $pdf) {
            $pdfs []= $this->fileStub($pdf);
        }
        return [
            'guid' => $episode->getGuid(),
            'number' => $episode->getNumber(),
            'date' => $episode->getDate()->format('c'),
            'runTime' => $episode->getRunTime(),
            'title' => $episode->getTitle(),
            'subTitle' => $episode->getSubTitle(),
            'description' => $episode->getDescription(),
            'season' => $this->seasonStub($episode->getSeason()),
            'language' => $this->languageStub($episode->getLanguage()),
            'audios' => $audios,
            'images' => $images,
            'pdfs' => $pdfs,
        ];
    }

    public function fileStub(StoredFileInterface $file) : array {
        return [
            'originalName' => $file->getOriginalName(),
            'mimeType' => $file->getMimeType(),
            'public' => $file->getPublic(),
            'checksum' => $file->getChecksum(),
            'sourceUrl' => $file->getSourceUrl(),
        ];
    }

    public function testExecute() : void {
        $podcastRepository = self::$container->get(PodcastRepository::class);
        $podcast = $podcastRepository->find(4);

        $initImage =[
            'originalName' => '3632486652_b432f7b283_c.jpg',
            'mimeType' => 'image/jpeg',
            'public' => false,
            'checksum' => 'c009533aca4c9a309e85606dbc69f8ad',
            'sourceUrl' => null,
        ];
        $expectedLanguageStub = [
            'name' => 'en',
        ];
        $expectedSeasonStub = [
            'number' => 1,
            'title' => 'title stub Season 1',
            'subTitle' => null,
            'preserved' => false,
            'description' => '',
        ];
        $sharedGravatarImage = [
            'originalName' => 'gravatar',
            'mimeType' => 'image/jpeg',
            'public' => true,
            'checksum' => '3642c573c4329edfeee928fd28951b27',
            'sourceUrl' => 'https://podcast.com/gravatar?s=96&amp;d=identicon&amp;r=G',
        ];
        $expectedPodcast = [
            'title' => 'title stub',
            'subTitle' => 'itunes subtitle stub',
            'explicit' => true,
            'description' => 'description stub',
            'copyright' => 'copyright stub',
            'website' => 'https://podcast.com/',
            'language' => $expectedLanguageStub,
            'categories' => [
                [ 'label' => 'Society & Culture' ],
                [ 'label' => 'Society & Culture - Philosophy' ],
            ],
            'images' => [
                $initImage,
                [
                    'originalName' => 'image.jpg',
                    'mimeType' => 'image/jpeg',
                    'public' => true,
                    'checksum' => 'e0c06454648674ebc21315efba645060',
                    'sourceUrl' => 'https://podcast.com/image.jpg',
                ],
            ],
            'seasons' => [$expectedSeasonStub],
            'episodes' => [
                [
                    'guid' => 'https://podcast.com/?p=2',
                    'number' => 2,
                    'date' => '2022-10-13T14:43:00-07:00',
                    'runTime' => '01:10:09',
                    'title' => 'episode 2 title stub',
                    'subTitle' => 'episode 2 itunes subtitle stub',
                    'description' => 'episode 2 description stub',
                    'season' => $expectedSeasonStub,
                    'language' => $expectedLanguageStub,
                    'audios' => [
                        [
                            'originalName' => 'audio.mp3',
                            'mimeType' => 'audio/mpeg',
                            'public' => true,
                            'checksum' => '3867b514cbaeb2fa5503da76cb9d1328',
                            'sourceUrl' => 'https://podcast.com/2/audio.mp3',
                        ],
                    ],
                    'images' => [
                        $sharedGravatarImage,
                        [
                            'originalName' => 'image.jpg',
                            'mimeType' => 'image/jpeg',
                            'public' => true,
                            'checksum' => 'c009533aca4c9a309e85606dbc69f8ad',
                            'sourceUrl' => 'https://podcast.com/2/image.jpg?w=1024',
                        ],
                    ],
                    'pdfs' => [
                        [
                            'originalName' => 'transcript.pdf',
                            'mimeType' => 'application/pdf',
                            'public' => true,
                            'checksum' => '4cd66a41f4b0e3f44a9c0739b87f1943',
                            'sourceUrl' => 'https://podcast.com/2/transcript.pdf',
                        ],
                    ],
                ],
                [
                    'guid' => 'https://podcast.com/?p=1',
                    'number' => 1,
                    'date' => '2022-10-12T14:43:00-07:00',
                    'runTime' => '01:10:10',
                    'title' => 'episode 1 title stub',
                    'subTitle' => 'episode 1 itunes subtitle stub',
                    'description' => 'episode 1 description stub',
                    'season' => $expectedSeasonStub,
                    'language' => $expectedLanguageStub,
                    'audios' => [
                        [
                            'originalName' => 'audio.mp3',
                            'mimeType' => 'audio/mpeg',
                            'public' => true,
                            'checksum' => '3867b514cbaeb2fa5503da76cb9d1328',
                            'sourceUrl' => 'https://podcast.com/1/audio.mp3',
                        ],
                    ],
                    'images' => [
                        $sharedGravatarImage,
                        [
                            'originalName' => 'image.jpg',
                            'mimeType' => 'image/jpeg',
                            'public' => true,
                            'checksum' => '1d86f8f0a2d4f99c2eaaab1ce9fb3b08',
                            'sourceUrl' => 'https://podcast.com/1/image.jpg?w=1024',
                        ],
                    ],
                    'pdfs' => [],
                ],
            ],
        ];

        // init state
        $this->assertSame($this->podcastStub($podcast), [
            'title' => 'Title 3',
            'subTitle' => 'SubTitle 3',
            'explicit' => false,
            'description' => '<p>This is paragraph 3</p>',
            'copyright' => '<p>This is paragraph 3</p>',
            'website' => '<p>This is paragraph 3</p>',
            'language' => null,
            'categories' => [],
            'images' => [$initImage],
            'seasons' => [],
            'episodes' => [],
        ]);


        $callback = function ($method, $url, $options) {
            if ($method === 'GET' && $url === 'https://rss.com/3') {
                return new MockResponse($this->rssFeed);
            } elseif ($method === 'GET' && $url === 'https://podcast.com/image.jpg') {
                return new MockResponse(file_get_contents(dirname(__FILE__, 2) . '/data/image/28213926366_4430448ff7_c.jpg'));
            } elseif ($method === 'GET' && $url === 'https://podcast.com/gravatar?s=96&amp;d=identicon&amp;r=G') {
                return new MockResponse(file_get_contents(dirname(__FILE__, 2) . '/data/image/30191231240_4010f114ba_c.jpg'));
            } elseif ($method === 'GET' && $url === 'https://podcast.com/1/image.jpg?w=1024') {
                return new MockResponse(file_get_contents(dirname(__FILE__, 2) . '/data/image/33519978964_c025c0da71_c.jpg'));
            } elseif ($method === 'GET' && $url === 'https://podcast.com/1/audio.mp3') {
                return new MockResponse(file_get_contents(dirname(__FILE__, 2) . '/data/audio/94934__bletort__taegum-1.mp3'));
            } elseif ($method === 'GET' && $url === 'https://podcast.com/2/image.jpg?w=1024') {
                return new MockResponse(file_get_contents(dirname(__FILE__, 2) . '/data/image/3632486652_b432f7b283_c.jpg'));
            } elseif ($method === 'GET' && $url === 'https://podcast.com/2/audio.mp3') {
                return new MockResponse(file_get_contents(dirname(__FILE__, 2) . '/data/audio/94934__bletort__taegum-1.mp3'));
            } elseif ($method === 'GET' && $url === 'https://podcast.com/2/transcript.pdf') {
                return new MockResponse(file_get_contents(dirname(__FILE__, 2) . '/data/pdf/holmes_1.pdf'));
            }
            throw new Exception("test request not accounted {$method} {$url}");
        };
        $httpClient = new MockHttpClient($callback);
        static::$kernel->getContainer()->set(HttpClientInterface::class, new TraceableHttpClient($httpClient));

        $this->execute('app:import:rss', [
            'podcastId' => 4,
        ]);
        $podcast = $podcastRepository->find(4);
        $this->assertSame($this->podcastStub($podcast), $expectedPodcast);

        // running the import again will not add duplicate resources
        $this->execute('app:import:rss', [
            'podcastId' => 4,
        ]);
        $podcast = $podcastRepository->find(4);
        $this->assertSame($this->podcastStub($podcast), $expectedPodcast);
    }

    protected function setUp() : void {
        parent::setUp();
    }
}